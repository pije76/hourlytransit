<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Instamojo;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InstamojoController extends Controller
{
    private $api;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('instamojo')->first();
        $instamojoData = json_decode($data->information, true);

        if ($instamojoData['sandbox_status'] == 1) {
            $this->api = new Instamojo($instamojoData['key'], $instamojoData['token'], 'https://test.instamojo.com/api/1.1/');
        } else {
            $this->api = new Instamojo($instamojoData['key'], $instamojoData['token']);
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

        // do calculation
        $calculatedData = $bookingProcess->calculation($request, $priceId);

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'INR' or not
        if ($currencyInfo->base_currency_text !== 'INR') {
            return redirect()->back()->with('error', 'Invalid currency for instamojo payment.')->withInput();
        }

        $arrData = $bookingProcess->timeCheck($request, 'Instamojo');

        $title = 'Room Booking';
        $notifyURL = route('frontend.room.room_booking.instamojo.notify');

        $customerName = $request['booking_name'];
        $customerEmail = $request['booking_email'];
        $customerPhone = $request['booking_phone'];

        try {
            $response = $this->api->paymentRequestCreate(array(
                'purpose' => $title,
                'amount' => round($calculatedData['grandTotal'], 2),
                'buyer_name' => $customerName,
                'email' => $customerEmail,
                'send_email' => false,
                'phone' => $customerPhone,
                'send_sms' => false,
                'redirect_url' => $notifyURL
            ));

            // put some data in session before redirect to instamojo url
            $request->session()->put('paymentFor', $paymentFor);
            $request->session()->put('arrData', $arrData);
            $request->session()->put('paymentId', $response['id']);

            return redirect($response['longurl']);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Sorry, transaction failed!')->withInput();
        }
    }

    public function notify(Request $request)
    {
        // get the information from session
        $payment = $request->session()->get('payment');
        $arrData = $request->session()->get('arrData');
        $paymentId = $request->session()->get('paymentId');

        $urlInfo = $request->all();

        if ($urlInfo['payment_request_id'] == $paymentId) {
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


            return redirect()->route('frontend.room_booking.cancel');
        }
    }
}
