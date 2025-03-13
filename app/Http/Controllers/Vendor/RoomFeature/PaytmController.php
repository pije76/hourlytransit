<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;
use Illuminate\Support\Facades\Response;

class PaytmController extends Controller
{
    public function index(Request $request, $paymentFor)
    {
        $charge = FeaturedRoomCharge::find($request->charge);

        $currencyInfo = $this->getCurrencyInfo();

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'INR' or not
        if ($currencyInfo->base_currency_text !== 'INR') {
            return Response::json(['error' => 'Invalid currency for paytm payment.'], 422);
        }

        $notifyURL = route('vendor.room_management.room.purchase_feature.paytm.notify');

        $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

        if (isset($vendor_mail->to_mail)) {
            $to_mail = $vendor_mail->to_mail;
        } else {
            $to_mail = $vendor_mail->email;
        }

        $customerEmail = $to_mail;
        $customerPhone = $vendor_mail->phone;

        $payment = PaytmWallet::with('receive');

        $payment->prepare([
            'order' => time(),
            'user' => uniqid(),
            'mobile_number' => $customerPhone,
            'email' => $customerEmail,
            'amount' => round($charge->price, 2),
            'callback_url' => $notifyURL
        ]);

        // put some data in session before redirect to paypal url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('roomId', $request->room_id);

        return $payment->receive();
    }

    public function notify(Request $request)
    {
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');

        $transaction = PaytmWallet::with('receive');

        // this response is needed to check the transaction status
        $response = $transaction->response();

        if ($transaction->isSuccessful()) {


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
            $order->payment_method = "Paytm";
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
