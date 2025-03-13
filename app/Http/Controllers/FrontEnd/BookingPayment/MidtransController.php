<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Midtrans\Config as MidtransConfig;
use Illuminate\Support\Facades\Session;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\BasicSettings\Basic;
use App\Models\Vendor;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function index(Request $request, $paymentFor, $userType)
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

        if ($currencyInfo->base_currency_text !== 'IDR') {
            return redirect()->back()->with('error', 'Invalid currency for midtrans payment.')->withInput();
        }

        $data = OnlineGateway::whereKeyword('midtrans')->first();
        $data = json_decode($data->information, true);

        MidtransConfig::$serverKey = $data['server_key'];
        if ($data['midtrans_mode'] == 1) {
            MidtransConfig::$isProduction = false;
        } elseif ($data['midtrans_mode'] == 0) {
            MidtransConfig::$isProduction = true;
        }
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        $arrData = $bookingProcess->timeCheck($request, 'Midtrans');

        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => intval($calculatedData['grandTotal']) * 1000, 
            ],

            'customer_details' => [
                'email' =>  $request['booking_email'],
                'phone' => $request['booking_phone'],
                'name' =>  $request['booking_name'],
            ],

        ];

        $snapToken = Snap::getSnapToken($params);

        $title = 'Room Booking';


        session()->put('paymentFor', $paymentFor);
        session()->put('arrData', $arrData);
        session()->put('title', $title);
        session()->put('userType', $userType);

        return view('frontend.payment.booking-midtrans', compact('snapToken', 'data'));
    }


    public function creditCardNotify(Request $request)
    {
        $arrData = $request->session()->get('arrData');
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
    }
}
