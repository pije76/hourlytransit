<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Session;
use Ixudra\Curl\Facades\Curl;

class PhonepeController extends Controller
{
    public function index(Request $request, $paymentFor)
    {
        if ($request->session()->has('price')) {
            $priceId = $request->session()->get('price');
        } else {
            Session::flash('error', 'Something went wrong!');

            return redirect()->back();
        }

        $bookingProcess = new BookingController();

        // do calculation
        $calculatedData = $bookingProcess->calculation($request, $priceId);

        $currencyInfo = $this->getCurrencyInfo();
        if ($currencyInfo->base_currency_text != 'INR') {
            return redirect()->back()->with('error', 'Invalid currency for Phonepe payment.')->withInput();
        }

        $arrData = $bookingProcess->timeCheck($request, 'Phonepe');

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        $info = OnlineGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        $randomNo = substr(uniqid(), 0, 3);
        $data = array(
            'merchantId' => $information['merchant_id'],
            'merchantTransactionId' => uniqid(),
            'merchantUserId' => 'MUID' . $randomNo,
            'amount' => $calculatedData['grandTotal'] * 100,
            'redirectUrl' => route('frontend.room.room_booking.phonepe.notify'),
            'redirectMode' => 'POST',
            'callbackUrl' => route('frontend.room.room_booking.phonepe.notify'),
            'mobileNumber' => $request['booking_phone'] ? $request['booking_phone'] : '9999999999',
            'paymentInstrument' =>
            array(
                'type' => 'PAY_PAGE',
            ),
        );

        $encode = base64_encode(json_encode($data));

        $saltKey = $information['salt_key']; // sandbox salt key
        $saltIndex = $information['salt_index'];

        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);

        $finalXHeader = $sha256 . '###' . $saltIndex;


        if ($information['sandbox_status'] == 1) {
            $url = "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay"; // sandbox payment URL
        } else {
            $url = "https://api.phonepe.com/apis/hermes/pg/v1/pay"; // prod payment URL
        }

        $response = Curl::to($url)
            ->withHeader('Content-Type:application/json')
            ->withHeader('X-VERIFY:' . $finalXHeader)
            ->withData(json_encode(['request' => $encode]))
            ->post();

        $rData = json_decode($response);

        if ($rData->success == true) {
            if (!empty($rData->data->instrumentResponse->redirectInfo->url)) {
                // put some data in session before redirect to paytm url
                $request->session()->put('paymentFor', $paymentFor);
                $request->session()->put('arrData', $arrData);
                return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
            } else {
                return redirect()->back()->with('error', 'Payment Canceled.')->withInput();
            }
        } else {
            return redirect()->back()->with('error', 'Payment Canceled.')->withInput();
        }
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $arrData = $request->session()->get('arrData');

        $info = OnlineGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        if ($request->code == 'PAYMENT_SUCCESS' && $information['merchant_id'] == $request->merchantId) {
            $bookingProcess = new BookingController();

            // store all data in the database
            $bookingInfo = $bookingProcess->storeData($arrData);


            // generate an invoice in pdf format 
            $invoice = $bookingProcess->generateInvoice($bookingInfo);

            // then, update the invoice field info in database 
            $bookingInfo->update(['invoice' => $invoice]);

            // send a mail to the customer with the invoice
            $bookingProcess->prepareMailForCustomer($bookingInfo);

            // send a mail to the vendor with the invoice
            $bookingProcess->prepareMailForvendor($bookingInfo);

            //tranction part
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

            // remove all session data
            $request->session()->forget('price');
            $request->session()->forget('checkInTime');
            $request->session()->forget('checkInDate');
            $request->session()->forget('adult');
            $request->session()->forget('children');
            $request->session()->forget('roomDiscount');
            $request->session()->forget('takeService');
            $request->session()->forget('serviceCharge');

            return redirect()->route('frontend.room_booking.complete', ['type' => 'online']);
            
        } else {
            Session::flash('success', 'Something Went Wrong');
            return redirect()->route('frontend.rooms');
        }
    }
}
