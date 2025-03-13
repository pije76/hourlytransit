<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Session;

class ToyyibpayController extends Controller
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
        if ($currencyInfo->base_currency_text != 'RM') {
            return redirect()->back()->with('error', 'Invalid currency for Toyyibpay payment.')->withInput();
        }

        $arrData = $bookingProcess->timeCheck($request,'Toyyibpay');

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $info = OnlineGateway::where('keyword', 'toyyibpay')->first();
        $information = json_decode($info->information, true);
        $ref = uniqid();
        session()->put('toyyibpay_ref_id', $ref);
        $bill_title = 'Product Purchase';
        $bill_description = 'Product Purchase via toyyibpay';

        $some_data = array(
            'userSecretKey' => $information['secret_key'],
            'categoryCode' => $information['category_code'],
            'billName' => $bill_title,
            'billDescription' => $bill_description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $calculatedData['grandTotal'] * 100,
            'billReturnUrl' => route('frontend.room.room_booking.toyyibpay.notify'),
            'billExternalReferenceNo' => $ref,
            'billTo' => $request['booking_name'],
            'billEmail' => $request['booking_email'],
            'billPhone' => $request['booking_phone'],
        );

        if ($information['sandbox_status'] == 1) {
            $host = 'https://dev.toyyibpay.com/'; // for development environment
        } else {
            $host = 'https://toyyibpay.com/'; // for production environment
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $host . 'index.php/api/createBill');  // sandbox will be dev.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $response = json_decode($result, true);
        if (!empty($response[0])) {
            // put some data in session before redirect to paytm url
            $request->session()->put('paymentFor', $paymentFor);
            $request->session()->put('arrData', $arrData);
            return redirect($host . $response[0]["BillCode"]);
        } else {
            return redirect()->route('frontend.room_booking.cancel');
        }

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }
    public function notify(Request $request)
    {
        $arrData = $request->session()->get('arrData');
        $ref = session()->get('toyyibpay_ref_id');
        if ($request['status_id'] == 1 && $request['order_id'] == $ref) {
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
