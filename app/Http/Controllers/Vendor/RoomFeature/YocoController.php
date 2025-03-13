<?php

namespace App\Http\Controllers\Vendor\RoomFeature;


use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Facades\Http;

class YocoController extends Controller
{
    public function   index(Request $request, $_amount, $_title, $_cancel_url)
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
        ~~~~~~~~~~~~~~~~~ Send Request for payment start~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $info = OnlineGateway::where('keyword', 'yoco')->first();
        $information = json_decode($info->information, true);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $information['secret_key'],
        ])->post('https://payments.yoco.com/api/checkouts', [
            'amount' => $price * 100,
            'currency' => 'ZAR',
            'successUrl' => route('vendor.room_management.room.purchase_feature.yoco.notify')
        ]);

        $responseData = $response->json();
        if (array_key_exists('redirectUrl', $responseData)) {
            // put some data in session before redirect to paytm url 
            $request->session()->put('yoco_id', $responseData['id']);
            $request->session()->put('s_key', $information['secret_key']);
            $request->session()->put('chargeId', $request->charge);
            $request->session()->put('roomId', $request->room_id);
            return response()->json(['redirectURL' => $responseData["redirectUrl"]]);
        } else {

            return redirect($cancel_url)->with('error', 'Payment Canceled');
        }
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Send Request for payment start~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $id = Session::get('yoco_id');
        $s_key = Session::get('s_key');
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');
        $info = OnlineGateway::where('keyword', 'yoco')->first();
        $information = json_decode($info->information, true);

        $requestData = Session::get('request');
        $cancel_url = Session::get('cancel_url');
        if ($id && $information['secret_key'] == $s_key) {
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
            $order->payment_method = "Yoco";
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
        }
        Session::flash('warning', __('Something Went Wrong') . '!');
        return redirect()->route('vendor.room_management.rooms');
    }
}
