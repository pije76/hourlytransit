<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Session;

class PerfectMoneyController extends Controller
{
    /*
     * Perfect Money Gateway
     */
    public static function index(Request $request, $event_id)
    {
        try {
            if ($request->session()->has('price')) {
                $priceId = $request->session()->get('price');
            } else {
                Session::flash('error', 'Something went wrong!');

                return redirect()->back();
            }

            $bookingProcess = new BookingController();

            // do calculation
            $calculatedData = $bookingProcess->calculation($request, $priceId);

            $currencyInfo = Basic::first();

            $arrData = $bookingProcess->timeCheck($request, 'Perfect Money');


            /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

            /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Payment Gateway Init Start ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

            $randomNo = substr(uniqid(), 0, 8);
            $websiteInfo = Basic::select('website_title')->first();
            $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
            $info = json_decode($perfect_money->information, true);
            $val['PAYEE_ACCOUNT'] = $info['perfect_money_wallet_id'];;
            $val['PAYEE_NAME'] = $websiteInfo->website_title;
            $val['PAYMENT_ID'] = "$randomNo"; //random id
            $val['PAYMENT_AMOUNT'] = $calculatedData['grandTotal'];
            $val['PAYMENT_UNITS'] = "$currencyInfo->base_currency_text";

            $val['STATUS_URL'] = route('frontend.room.room_booking.perfect_money.notify');
            $val['PAYMENT_URL'] = route('frontend.room.room_booking.perfect_money.notify');
            $val['PAYMENT_URL_METHOD'] = 'GET';
            $val['NOPAYMENT_URL'] = route('frontend.room_booking.cancel');
            $val['NOPAYMENT_URL_METHOD'] = 'GET';
            $val['SUGGESTED_MEMO'] = $request['billing_name'];
            $val['BAGGAGE_FIELDS'] = 'IDENT';

            $data['val'] = $val;
            $data['method'] = 'post';
            $data['url'] = 'https://perfectmoney.com/api/step1.asp';
            $request->session()->put('payment_id', $randomNo);
            $request->session()->put('arrData', $arrData);

            return view('frontend.payment.perfect-money', compact('data'));
            /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Payment Gateway Init End ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        } catch (\Exception $e) {

            Session::flash('success', 'Something Went Wrong');
            return redirect()->route('frontend.rooms');
        }
    }
    public function notify(Request $request)
    {
        // get the information from session
        $arrData = $request->session()->get('arrData');

        $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
        $perfectMoneyInfo = json_decode($perfect_money->information, true);
        $currencyInfo = Basic::select('base_currency_text')->first();

        $amo = $request['PAYMENT_AMOUNT'];
        $unit = $request['PAYMENT_UNITS'];
        $track = $request['PAYMENT_ID'];
        $id = Session::get('payment_id');
        $final_amount = $arrData['grandTotal'];
        // $final_amount = 0.01; //testing  amount

        if ($request->PAYEE_ACCOUNT == $perfectMoneyInfo['perfect_money_wallet_id'] && $unit == $currencyInfo->base_currency_text && $track == $id && $amo == round($final_amount, 2)) {
            //success
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
