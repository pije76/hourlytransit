<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Log;
use Session;

class PaytabsController extends Controller
{
    public function index(Request $request, $_amount, $_title, $_cancel_url)
    {

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Package Info ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $charge = FeaturedRoomCharge::find($request->charge);
        $title = $_title;
        $price = $charge->price;
        $price = round($price, 2);
        $cancel_url = $_cancel_url;

        Session::put('request', $request->all());

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Init Payment Gateway ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $description = 'Package Purchase via paytabs';
        $currencyInfo = $this->getCurrencyInfo();
        $paytabInfo = paytabInfo();

        // changing the currency before redirect to Stripe
        if ($currencyInfo->base_currency_text != $paytabInfo['currency']) {
            return Response::json(['error' => 'Invalid currency for paytabs payment.'], 422);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $paytabInfo['server_key'], // Server Key
                'Content-Type' => 'application/json',
            ])->post($paytabInfo['url'], [
                'profile_id' => $paytabInfo['profile_id'], // Profile ID
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => uniqid(),
                'cart_description' => $description,
                'cart_currency' => $paytabInfo['currency'], // set currency by region
                'cart_amount' => $price,
                'return' => route('vendor.room_management.room.purchase_feature.paytabs.notify'),
            ]);

            $responseData = $response->json();
            // put some data in session before redirect to paytm url
            Session::put('request', $request->all());
            $request->session()->put('chargeId', $request->charge);
            $request->session()->put('roomId', $request->room_id);
            return response()->json(['redirectURL' => $responseData['redirect_url']]);
        } catch (\Exception $e) {

            return redirect($cancel_url);
        }
    }
    public function notify(Request $request)
    {

        $requestData = Session::get('request');
        $bs = Basic::first();
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');

        $resp = $request->all();
        if ($resp['respStatus'] == "A" && $resp['respMessage'] == 'Authorised') {

            $bs = Basic::first();
            $roomFeature = new RoomFeatureController();
            $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

            if (isset($vendor_mail->to_mail)) {
                $to_mail = $vendor_mail->to_mail;
            } else {
                $to_mail = $vendor_mail->email;
            }

            $charge = FeaturedRoomCharge::find($chargeId);

            $startDate = Carbon::now()->startOfDay();
            $endDate = $startDate->copy()->addDays($charge->days);

            $order =  RoomFeature::where('room_id', $roomId)->first();
            if (empty($order)) {
                $order = new RoomFeature();
            }

            $order->room_id = $roomId;
            $order->vendor_id = Auth::guard('vendor')->user()->id;
            $order->vendor_mail = $to_mail;
            $order->order_number = uniqid();
            $order->total = $charge->price;
            $order->payment_method = "Paytabs";
            $order->gateway_type = "online";
            $order->payment_status = "completed";
            $order->order_status = 'pending';
            $order->days = $charge->days;
            $order->start_date = $startDate;
            $order->end_date = $endDate;
            $order->currency_symbol = $bs->base_currency_symbol;
            $order->currency_symbol_position = $bs->base_currency_symbol_position;

            $order->save();

            // generate an invoice in pdf format 
            $invoice = $roomFeature->generateInvoice($order);

            // then, update the invoice field info in database 
            $order->update(['invoice' => $invoice]);

            //Transactions part
            $earning = Basic::first();

            $earning->total_earning = $earning->total_earning + $order->total;

            $earning->save();

            $after_balance = NULL;
            $pre_balance = NULL;

            $data = [
                'transcation_id' => time(),
                'booking_id' => $order->id,
                'transcation_type' => 'room_feature',
                'user_id' => null,
                'vendor_id' => null,
                'payment_status' => 1,
                'payment_method' => $order->payment_method,
                'grand_total' => $order->total,
                'commission' => $order->total,
                'pre_balance' => $pre_balance,
                'after_balance' => $after_balance,
                'gateway_type' => $order->gateway_type,
                'currency_symbol' => $order->currency_symbol,
                'currency_symbol_position' => $order->currency_symbol_position,
            ];
            store_transaction($data);

            // send a mail to the vendor 
            $roomFeature->prepareMail($to_mail, $charge->price, $order->payment_method, $order->invoice);

            $request->session()->forget('chargeId');
            $request->session()->forget('roomId');
            return redirect()->route('success.page');
            
        } else {
            Session::flash('warning', __('Something Went Wrong') . '!');

            return redirect()->route('vendor.room_management.rooms');
        }
    }

}
