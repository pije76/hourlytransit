<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FlutterwaveController extends Controller
{
    private $public_key, $secret_key;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('flutterwave')->first();
        $flutterwaveData = json_decode($data->information, true);

        $this->public_key = $flutterwaveData['public_key'];
        $this->secret_key = $flutterwaveData['secret_key'];
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

        $arrData = $bookingProcess->timeCheck($request, 'Flutterwave');

        // do calculation
        $calculatedData = $bookingProcess->calculation($request, $priceId);

        $allowedCurrencies = array('BIF', 'CAD', 'CDF', 'CVE', 'EUR', 'GBP', 'GHS', 'GMD', 'GNF', 'KES', 'LRD', 'MWK', 'MZN', 'NGN', 'RWF', 'SLL', 'STD', 'TZS', 'UGX', 'USD', 'XAF', 'XOF', 'ZMK', 'ZMW', 'ZWD');

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return redirect()->back()->with('error', 'Invalid currency for flutterwave payment.')->withInput();
        }


        $title = 'Room Booking';
        $notifyURL = route('frontend.room.room_booking.flutterwave.notify');

        $customerName = $request['booking_name'];
        $customerEmail = $request['booking_email'];
        $customerPhone = $request['booking_phone'];


        // send payment to flutterwave for processing
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.flutterwave.com/v3/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'tx_ref' => 'FLW | ' . time(),
                'amount' => $calculatedData['grandTotal'],
                'currency' => $currencyInfo->base_currency_text,
                'redirect_url' => $notifyURL,
                'payment_options' => 'card,banktransfer',
                'customer' => [
                    'email' => $customerEmail,
                    'phone_number' => $customerPhone,
                    'name' => $customerName
                ],
                'customizations' => [
                    'title' => $title,
                    'description' => $title . ' via Flutterwave.'
                ]
            ]),
            CURLOPT_HTTPHEADER => array(
                'authorization: Bearer ' . $this->secret_key,
                'content-type: application/json'
            )
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseData = json_decode($response, true);

        //curl end

        // put some data in session before redirect to flutterwave url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('arrData', $arrData);

        // redirect to payment
        if ($responseData['status'] === 'success') {
            return redirect($responseData['data']['link']);
        } else {
            return redirect()->back()->with('error', 'Error: ' . $responseData['message'])->withInput();
        }
    }

    public function notify(Request $request)
    {
        $arrData = $request->session()->get('arrData');

        $urlInfo = $request->all();

        if ($urlInfo['status'] == 'successful') {
            $txId = $urlInfo['transaction_id'];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$txId}/verify",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'authorization: Bearer ' . $this->secret_key,
                    'content-type: application/json'
                )
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $responseData = json_decode($response, true);
            if ($responseData['status'] === 'success') {
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
        } else {
            Session::flash('success', 'Something Went Wrong');
            return redirect()->route('frontend.rooms');
        }
    }
}
