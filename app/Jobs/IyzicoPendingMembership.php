<?php

namespace App\Jobs;

use App\Http\Helpers\MegaMailer;
use App\Models\BasicSettings\Basic;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IyzicoPendingMembership implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $memberhip_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($memberhip_id)
    {
        $this->memberhip_id = $memberhip_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $memberhip = Membership::where('id', $this->memberhip_id)->first();

        $conversion_id = $memberhip->conversation_id;

        $options = options();
        $request = new \Iyzipay\Request\ReportingPaymentDetailRequest();
        $request->setPaymentConversationId($conversion_id);


        $paymentResponse = \Iyzipay\Model\ReportingPaymentDetail::create($request, $options);

        $result = (array) $paymentResponse;
        foreach ($result as $key => $data) {
            $data = json_decode($data, true);
            if ($data['status'] == 'success' && !is_null($data['payments'])) {
                if (is_array($data['payments']) && !empty($data['payments'])) {
                    if ($data['payments'][0]['paymentStatus'] == 1) {
                        //success 
                        $memberhip->status = 1;
                        $memberhip->save();

                        $vendor = Vendor::findorFail($memberhip->vendor_id);
                        $package = Package::findOrFail($memberhip->package_id);
                        $settings = json_decode($memberhip->settings, true);
                        $activation = Carbon::parse($package->start_date);
                        $expire = Carbon::parse($package->expire_date);

                        $mailer = new MegaMailer();
                        $data = [
                            'toMail' => $vendor->email,
                            'toName' => $vendor->fname,
                            'username' => $vendor->username,
                            'package_title' => $package->title,
                            'package_price' => ($settings['base_currency_symbol_position'] == 'left' ? $settings['base_currency_symbol'] . ' ' : '') . $package->price . ($settings['base_currency_symbol_position'] == 'right' ? ' ' . $settings['base_currency_symbol'] : ''),
                            'activation_date' => $activation->toFormattedDateString(),
                            'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
                            'website_title' => $settings['website_title'],
                            'templateType' => 'subscription_package_purchase'
                        ];
                        $mailer->mailFromAdmin($data);


                        //Transactions part
                        $earning = Basic::first();

                        $earning->total_earning = $earning->total_earning + $memberhip->price;

                        $earning->save();

                        $after_balance = NULL;
                        $pre_balance = NULL;

                        $data = [
                            'transcation_id' => time(),
                            'booking_id' => $memberhip->id,
                            'transcation_type' => 'membership_buy',
                            'user_id' => null,
                            'vendor_id' => null,
                            'payment_status' => 1,
                            'payment_method' => $memberhip->payment_method,
                            'grand_total' => $memberhip->price,
                            'commission' => $memberhip->price,
                            'pre_balance' => $pre_balance,
                            'after_balance' => $after_balance,
                            'gateway_type' => "Online",
                            'currency_symbol' => $memberhip->base_currency_symbol,
                            'currency_symbol_position' => $memberhip->base_currency_symbol_position,
                        ];
                        store_transaction($data);
                    } else {
                        $memberhip->status = 2;
                        $memberhip->save();
                    }
                } else {
                    $memberhip->status = 2;
                    $memberhip->save();
                }
            } else {
                $memberhip->status = 2;
                $memberhip->save();
            }
        }
    }
}
