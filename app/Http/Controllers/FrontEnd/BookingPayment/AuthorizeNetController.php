<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Omnipay\Omnipay;
use Session;
use App\Models\BookingHour;
use App\Models\Hotel;
use App\Models\HourlyRoomPrice;
use App\Models\Vendor;

class AuthorizeNetController extends Controller
{
    private $gateway;
    public function __construct()
    {
        $data = OnlineGateway::query()->whereKeyword('authorize.net')->first();
        $authorizeNetData = json_decode($data->information, true);
        $this->gateway = Omnipay::create('AuthorizeNetApi_Api');
        $this->gateway->setAuthName($authorizeNetData['login_id']);
        $this->gateway->setTransactionKey($authorizeNetData['transaction_key']);
        if ($authorizeNetData['sandbox_check'] == 1) {
            $this->gateway->setTestMode(true);
        }
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

        $arrData = $bookingProcess->timeCheck($request,'Authorized.Net');

        // do calculation
        $calculatedData = $bookingProcess->calculation($request, $priceId);



        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'INR' or not
        $allowedCurrencies = array('USD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'NOK', 'PLN', 'SEK', 'AUD', 'NZD');
        $currencyInfo = $this->getCurrencyInfo();
        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return redirect()->back()->with('error', 'Invalid currency for authorize.net payment.')->withInput();
        }


        if ($request->filled('opaqueDataValue') && $request->filled('opaqueDataDescriptor')) {

            // generate a unique merchant site transaction ID
            $transactionId = rand(100000000, 999999999);
            $response = $this->gateway->authorize([
                'amount' => sprintf('%0.2f', $calculatedData['grandTotal']),
                'currency' => $currencyInfo->base_currency_text,
                'transactionId' => $transactionId,
                'opaqueDataDescriptor' => $request->opaqueDataDescriptor,
                'opaqueDataValue' => $request->opaqueDataValue
            ])->send();

            if ($response->isSuccessful()) {

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
                // remove all session data
                $request->session()->forget('price');
                $request->session()->forget('checkInTime');
                $request->session()->forget('checkInDate');
                $request->session()->forget('adult');
                $request->session()->forget('children');
                $request->session()->forget('roomDiscount');
                $request->session()->forget('takeService');
                $request->session()->forget('serviceCharge');

                // remove session data
                $request->session()->forget('productCart');
                $request->session()->forget('discount');

                Session::flash('success', 'Something Went Wrong');
                return redirect()->route('frontend.rooms');
            }
        } else {
            Session::flash('success', 'Something Went Wrong');

            return redirect()->route('frontend.rooms');
        }

        Session::flash('success', 'Something Went Wrong');

        return redirect()->route('frontend.rooms');
    }
}
