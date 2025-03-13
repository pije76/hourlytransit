<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Mollie\Laravel\Facades\Mollie;
use App\Models\Vendor;

class MollieController extends Controller
{
    public function index(Request $request)
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


        $allowedCurrencies = array('AED', 'AUD', 'BGN', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'ILS', 'ISK', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TWD', 'USD', 'ZAR');

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return redirect()->back()->with('error', 'Invalid currency for mollie payment.')->withInput();
        }

        $arrData = $bookingProcess->timeCheck($request, 'Mollie');


        $title = 'Room Booking';
        $notifyURL = route('frontend.room.room_booking.mollie.notify');
 
        $payment = Mollie::api()->payments->create([
            'amount' => [
                'currency' => $currencyInfo->base_currency_text,
                'value' => sprintf('%0.2f', $calculatedData['grandTotal'])
            ],
            'description' => $title . ' via Mollie',
            'redirectUrl' => $notifyURL
        ]);

        // put some data in session before redirect to mollie url
        $request->session()->put('payment', $payment);
        $request->session()->put('arrData', $arrData);

        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function notify(Request $request)
    {


        // get the information from session
        $payment = $request->session()->get('payment');
        $arrData = $request->session()->get('arrData');

        $paymentInfo = Mollie::api()->payments->get($payment->id);

        if ($paymentInfo->isPaid() == true) {


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
