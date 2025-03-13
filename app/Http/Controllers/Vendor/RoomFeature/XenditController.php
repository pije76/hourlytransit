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
use Session;
use Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class XenditController extends Controller
{
    public function index(Request $request, $paymentFor)
    {
        $charge = FeaturedRoomCharge::find($request->charge);


        $currencyInfo = $this->getCurrencyInfo();
        $allowed_currency = array('IDR', 'PHP', 'USD', 'SGD', 'MYR');

        if (!in_array($currencyInfo->base_currency_text, $allowed_currency)) {
            return Response::json(['error' => 'Invalid currency for Xendit payment.'], 422);
        }

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $external_id = Str::random(10);
        $secret_key = 'Basic ' . config('xendit.key_auth');

        $data_request = Http::withHeaders([
            'Authorization' => $secret_key
        ])->post('https://api.xendit.co/v2/invoices', [
            'external_id' => $external_id,
            'amount' => $charge->price,
            'currency' => $currencyInfo->base_currency_text,
            'success_redirect_url' => route('vendor.room_management.room.purchase_feature.xendit.notify')
        ]);
        $response = $data_request->object();
        $response = json_decode(json_encode($response), true);
        if (!empty($response['success_redirect_url'])) {
            // put some data in session before redirect to paytm url
            $request->session()->put('paymentFor', $paymentFor);
            $request->session()->put('chargeId', $request->charge);
            $request->session()->put('roomId', $request->room_id);
            $request->session()->put('xendit_id', $response['id']);
            $request->session()->put('secret_key', config('xendit.key_auth'));
            $request->session()->put('xendit_payment_type', 'shop');

            return response()->json(['redirectURL' => $response['invoice_url']]);
        } else {
            return Response::json(['error' => 'Something went Wrong.'], 422);
        }
    }


    // return to success page
    public function notify(Request $request)
    {
        // get the information from session
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');
        $xendit_id = Session::get('xendit_id');
        $secret_key = Session::get('secret_key');
        if (!is_null($xendit_id) && $secret_key == config('xendit.key_auth')) {
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
            $order->payment_method = "Xendit";
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
