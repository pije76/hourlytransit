<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MercadoPagoController extends Controller
{
    private $token, $sandbox_status;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('mercadopago')->first();
        $mercadopagoData = json_decode($data->information, true);

        $this->token = $mercadopagoData['token'];
        $this->sandbox_status = $mercadopagoData['sandbox_status'];
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


        $allowedCurrencies = array('ARS', 'BOB', 'BRL', 'CLF', 'CLP', 'COP', 'CRC', 'CUC', 'CUP', 'DOP', 'EUR', 'GTQ', 'HNL', 'MXN', 'NIO', 'PAB', 'PEN', 'PYG', 'USD', 'UYU', 'VEF', 'VES');

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return redirect()->back()->with('error', 'Invalid currency for mercadopago payment.');
        }

        $arrData = $bookingProcess->timeCheck($request, 'Mercadopago');

        $title = 'Room Booking';
        $notifyURL = route('frontend.room.room_booking.mercadopago.notify');
        $cancelURL = route('frontend.room_booking.cancel');

        $customerEmail = $request['booking_email'];

        $curl = curl_init();

        $preferenceData = [
            'items' => [
                [
                    'id' => uniqid(),
                    'title' => $title,
                    'description' => $title . ' via MercadoPago',
                    'quantity' => 1,
                    'currency' => $currencyInfo->base_currency_text,
                    'unit_price' => $calculatedData['grandTotal']
                ]
            ],
            'payer' => [
                'email' => $customerEmail
            ],
            'back_urls' => [
                'success' => $notifyURL,
                'pending' => '',
                'failure' => $cancelURL
            ],
            'notification_url' => $notifyURL,
            'auto_return' => 'approved'
        ];

        $httpHeader = ['Content-Type: application/json'];

        $url = 'https://api.mercadopago.com/checkout/preferences?access_token=' . $this->token;

        $curlOPT = [
            CURLOPT_URL             => $url,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => json_encode($preferenceData, true),
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTPHEADER      => $httpHeader
        ];

        curl_setopt_array($curl, $curlOPT);

        $response = curl_exec($curl);
        $responseInfo = json_decode($response, true);

        curl_close($curl);

        // put some data in session before redirect to mercadopago url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('arrData', $arrData);

        if ($this->sandbox_status == 1) {
            return redirect($responseInfo['sandbox_init_point']);
        } else {
            return redirect($responseInfo['init_point']);
        }
    }

    public function notify(Request $request)
    {

        $arrData = $request->session()->get('arrData');

        if ($request->status == 'approved') {


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

    public function curlCalls($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $curlData = curl_exec($curl);

        curl_close($curl);

        return $curlData;
    }
}
