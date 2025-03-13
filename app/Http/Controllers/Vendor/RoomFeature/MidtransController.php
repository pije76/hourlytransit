<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use Carbon\Carbon;
use Midtrans\Snap;
use App\Models\Vendor;
use App\Models\Language;
use App\Models\VendorInfo;
use Illuminate\Http\Request;
use App\Models\BasicSettings\Basic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config as MidtransConfig;
use Illuminate\Support\Facades\Session;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;

class MidtransController extends Controller
{
    public $public_key;

    public function paymentProcess(Request $request, $userType)
    {
        $charge = FeaturedRoomCharge::find($request->charge);
        $currentLang = session()->has('lang') ?
            (Language::where('code', session()->get('lang'))->first())
            : (Language::where('is_default', 1)->first());

        $vendorinfo = VendorInfo::where('vendor_id', Auth::guard('vendor')->user()->id)->where('language_id', $currentLang->id)->with('vendor')->first();

        $data = OnlineGateway::whereKeyword('midtrans')->first();
        $data = json_decode($data->information, true);
        // will come from database
        MidtransConfig::$serverKey = $data['server_key'];
        if ($data['midtrans_mode'] == 1) {
            MidtransConfig::$isProduction = false;
        } elseif ($data['midtrans_mode'] == 0) {
            MidtransConfig::$isProduction = true;
        }
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;


        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $charge->price * 1000, // will be multiplied by 1000
            ],

            'vendor_details' => [
                'email' => $vendorinfo->vendor->email,
                'phone' => $vendorinfo->vendor->phone,
                'name' => $vendorinfo->name,
            ],

        ];

        $snapToken = Snap::getSnapToken($params);

        Session::put('paymentInfo', $params);
        Session::put('userType', $userType);
        Session::put('request', $request->all());
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('roomId', $request->room_id);

        return view('frontend.payment.room-feature-midtrans', compact('snapToken', 'data'));
    }

    public function creditCardNotify(Request $request)
    {
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');
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
        $order->payment_method = "Midtrans";
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
}
