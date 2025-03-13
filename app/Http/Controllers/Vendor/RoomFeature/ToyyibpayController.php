<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\Language;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\RoomFeature;
use App\Models\Vendor;
use App\Models\VendorInfo;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;


class ToyyibpayController extends Controller
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
        $currentLang = session()->has('lang') ?
            (Language::where('code', session()->get('lang'))->first())
            : (Language::where('is_default', 1)->first());
        $vendorinfo = VendorInfo::where('vendor_id', Auth::guard('vendor')->user()->id)->where('language_id', $currentLang->id)->with('vendor')->first();
        $vendor = Auth::guard('vendor')->user();

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Init Payment Gateway ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $info = OnlineGateway::where('keyword', 'toyyibpay')->first();
        $information = json_decode($info->information, true);
        $ref = uniqid();
        session()->put('toyyibpay_ref_id', $ref);
        $bill_title = 'Hotel Feature';
        $bill_description = 'Hotel Feature via Toyyibpay';

        $some_data = array(
            'userSecretKey' => $information['secret_key'],
            'categoryCode' => $information['category_code'],
            'billName' => $bill_title,
            'billDescription' => $bill_description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $price * 100,
            'billReturnUrl' => route('vendor.room_management.room.purchase_feature.toyyibpay.notify'),
            'billExternalReferenceNo' => $ref,
            'billTo' => $vendorinfo->name,
            'billEmail' => $vendor->email,
            'billPhone' => $vendor->phone,
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
            Session::put('request', $request->all());
            $request->session()->put('chargeId', $request->charge);
            $request->session()->put('roomId', $request->room_id);
            return response()->json(['redirectURL' => $host . $response[0]["BillCode"]]);
        } else {
            return redirect($cancel_url)->with('error', 'Payment Canceled');
        }

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $ref = session()->get('toyyibpay_ref_id');
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');

        if ($request['status_id'] == 1 && $request['order_id'] == $ref) {
            $requestData = Session::get('request');
            $bs = Basic::first();
            $cancel_url = Session::get('cancel_url');
            /** Get the payment ID before session clear **/
            $payment_id = Session::get('payment_id');

            if ($request['payment_request_id'] == $payment_id) {

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
                $order->payment_method = "Toyyibpay";
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
        Session::flash('warning', __('Something Went Wrong') . '!');

        return redirect()->route('vendor.room_management.rooms');
    }

    public function cancel()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        session()->flash('warning', __('cancel payment'));
        if ($paymentFor == "membership") {
            return redirect()->route('front.register.view', ['status' => $requestData['package_type'], 'id' => $requestData['package_id']])->withInput($requestData);
        } else {
            return redirect()->route('vendor.plan.extend.checkout', ['package_id' => $requestData['package_id']])->withInput($requestData);
        }
    }
}
