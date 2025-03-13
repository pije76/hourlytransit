<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaystackController extends Controller
{
    private $api_key;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('paystack')->first();
        $paystackData = json_decode($data->information, true);

        $this->api_key = $paystackData['key'];
    }

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

        // checking whether the currency is set to 'NGN' or not
        if ($currencyInfo->base_currency_text !== 'NGN') {
            return redirect()->back()->with('error', 'Invalid currency for paystack payment.')->withInput();
        }


        $arrData = $bookingProcess->timeCheck($request, 'Paystack');

        $notifyURL = route('frontend.room.room_booking.paystack.notify');

        $customerEmail = $request['booking_email'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.paystack.co/transaction/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode([
                'amount'       => intval($calculatedData['grandTotal']) * 100,
                'email'        => $customerEmail,
                'callback_url' => $notifyURL
            ]),
            CURLOPT_HTTPHEADER     => [
                'authorization: Bearer ' . $this->api_key,
                'content-type: application/json',
                'cache-control: no-cache'
            ]
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $transaction = json_decode($response, true);

        // put some data in session before redirect to paystack url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('arrData', $arrData);

        if ($transaction['status'] == true) {
            return redirect($transaction['data']['authorization_url']);
        } else {
            return redirect()->back()->with('error', 'Error: ' . $transaction['message'])->withInput();
        }
    }

    public function notify(Request $request)
    {
        $arrData = $request->session()->get('arrData');

        $urlInfo = $request->all();

        if ($urlInfo['trxref'] === $urlInfo['reference']) {
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
