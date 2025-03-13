<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Instamojo;
use App\Models\PaymentGateway\OnlineGateway;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

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

        $charge = FeaturedRoomCharge::find($request->charge);
        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'INR' or not
        if ($currencyInfo->base_currency_text !== 'INR') {
            return Response::json(['error' => 'Invalid currency for instamojo payment.'], 422);
        }

        $title = 'Activation Feature';
        $notifyURL = route('vendor.room_management.room.purchase_feature.instamojo.notify');

        $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

        if (isset($vendor_mail->to_mail)) {
            $to_mail = $vendor_mail->to_mail;
        } else {
            $to_mail = $vendor_mail->email;
        }

        try {
            $response = $this->api->paymentRequestCreate(array(
                'purpose' => $title,
                'amount' => round($charge->price, 2),
                'buyer_name' => "Vendor",
                'email' => $to_mail,
                'send_email' => false,
                'phone' => $vendor_mail->phone,
                'send_sms' => false,
                'redirect_url' => $notifyURL
            ));

            // put some data in session before redirect to instamojo url
            $request->session()->put('paymentFor', $paymentFor);
            $request->session()->put('paymentId', $response['id']);
            $request->session()->put('chargeId', $request->charge);
            $request->session()->put('roomId', $request->room_id);

            return Response::json(['redirectURL' => $response['longurl']]);
        } catch (Exception $e) {
            Session::flash('warning', __('Something Went Wrong') . '!');
            if ($charge->price < 10) {
                return Response::json(['error' => 'Amount cannot be less than INR 9.00.'], 422);
            } else {
                return Response::json(['error' => 'Something went wrong'], 422);
            }
        }
    }

    public function notify(Request $request)
    {

        $paymentId = $request->session()->get('paymentId');
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');

        $urlInfo = $request->all();

        if ($urlInfo['payment_request_id'] == $paymentId) {


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
            $order->payment_method = "Instamojo";
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
