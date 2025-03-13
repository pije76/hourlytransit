<?php

namespace App\Jobs;

use App\Http\Controllers\Vendor\RoomFeature\RoomFeatureController;
use App\Models\BasicSettings\Basic;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class iyzicoPendingRoomFeature implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $feature_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($feature_id)
    {
        $this->feature_id = $feature_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = RoomFeature::where('id', $this->feature_id)->first();
        $conversion_id = $order->conversation_id;
        $roomFeature = new RoomFeatureController();

        $vendor_mail = Vendor::Find($order->vendor_id);

        if (isset($vendor_mail->to_mail)) {
            $to_mail = $vendor_mail->to_mail;
        } else {
            $to_mail = $vendor_mail->email;
        }

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
                        $order->payment_status = 'completed';
                        $order->save();

                        // generate an invoice in pdf format 
                        $invoice = $roomFeature->generateInvoice($order);

                        // then, update the invoice field info in database 
                        $order->update(['invoice' => $invoice]);

                        //Transactions part
                        $earning = Basic::first();

                        $earning->total_earning = $earning->total_earning + $order->total;

                        $earning->save();

                        $after_balance = NULL;
                        $pre_balance = NULL;

                        $data = [
                            'transcation_id' => time(),
                            'booking_id' => $order->id,
                            'transcation_type' => 'room_feature',
                            'user_id' => null,
                            'vendor_id' => null,
                            'payment_status' => 1,
                            'payment_method' => $order->payment_method,
                            'grand_total' => $order->total,
                            'commission' => $order->total,
                            'pre_balance' => $pre_balance,
                            'after_balance' => $after_balance,
                            'gateway_type' => $order->gateway_type,
                            'currency_symbol' => $order->currency_symbol,
                            'currency_symbol_position' => $order->currency_symbol_position,
                        ];
                        store_transaction($data);

                        // send a mail to the vendor 
                        $roomFeature->prepareMail($to_mail, $order->total, $order->payment_method, $order->invoice);
                    } else {
                        $order->payment_status = 'rejected';
                        $order->order_status = 'rejected';
                        $order->save();
                    }
                } else {
                    $order->payment_status = 'rejected';
                    $order->order_status = 'rejected';
                    $order->save();
                }
            } else {
                $order->payment_status = 'rejected';
                $order->order_status = 'rejected';
                $order->save();
            }
        }
    }
}
