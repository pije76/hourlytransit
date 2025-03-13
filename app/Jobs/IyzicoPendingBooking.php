<?php

namespace App\Jobs;

use App\Http\Controllers\FrontEnd\BookingPayment\BookingController;
use App\Models\BasicSettings\Basic;
use App\Models\Booking;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IyzicoPendingBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $booking_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($booking_id)
    {
        $this->booking_id = $booking_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $bookingInfo = Booking::where('id', $this->booking_id)->first();
        $conversion_id = $bookingInfo->conversation_id;

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

                        $bookingInfo->payment_status = 1;
                        $bookingInfo->save();

                        $bookingProcess = new BookingController();

                        // generate an invoice in pdf format 
                        $invoice = $bookingProcess->generateInvoice($bookingInfo);

                        // then, update the invoice field info in database 
                        $bookingInfo->update(['invoice' => $invoice]);

                        // send a mail to the customer with the invoice
                        $bookingProcess->prepareMailForCustomer($bookingInfo);

                        // send a mail to the vendor with the invoice
                        $bookingProcess->prepareMailForvendor($bookingInfo);

                        //Transactions part
                        $vendor_id = $bookingInfo->vendor_id;

                        //calculate commission
                        if ($vendor_id == 0) {
                            $commission = $bookingInfo->grand_total;
                        } else {
                            $commission = 0;
                        }

                        //get vendor
                        $vendor = Vendor::where('id', $vendor_id)->first();

                        //add blance to admin revinue
                        $earning = Basic::first();

                        if ($vendor_id == 0) {
                            $earning->total_earning = $earning->total_earning + $bookingInfo->grand_total;
                        } else {
                            $earning->total_earning = $earning->total_earning + $commission;
                        }
                        $earning->save();

                        //store Balance  to vendor
                        if ($vendor) {
                            $pre_balance = $vendor->amount;
                            $vendor->amount = $vendor->amount + ($bookingInfo->grand_total - ($commission));
                            $vendor->save();
                            $after_balance = $vendor->amount;
                        } else {

                            $after_balance = NULL;
                            $pre_balance = NULL;
                        }
                        //calculate commission end

                        $data = [
                            'transcation_id' => time(),
                            'booking_id' => $bookingInfo->id,
                            'transcation_type' => 'room_booking',
                            'user_id' => null,
                            'vendor_id' => $vendor_id,
                            'payment_status' => 1,
                            'payment_method' => $bookingInfo->payment_method,
                            'grand_total' => $bookingInfo->grand_total,
                            'commission' => $commission,
                            'pre_balance' => $pre_balance,
                            'after_balance' => $after_balance,
                            'gateway_type' => $bookingInfo->gateway_type,
                            'currency_symbol' => $bookingInfo->currency_symbol,
                            'currency_symbol_position' => $bookingInfo->currency_symbol_position,
                        ];
                        store_transaction($data);
                    } else {
                        $bookingInfo->payment_status = 2;
                        $bookingInfo->save();
                    }
                } else {
                    $bookingInfo->payment_status = 2;
                    $bookingInfo->save();
                }
            } else {
                $bookingInfo->payment_status = 2;
                $bookingInfo->save();
            }
        }
    }
}
