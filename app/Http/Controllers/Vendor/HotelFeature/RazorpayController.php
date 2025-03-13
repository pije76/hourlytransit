<?php

namespace App\Http\Controllers\Vendor\HotelFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\FeaturedHotelCharge;
use App\Models\HotelFeature;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

class RazorpayController extends Controller
{
    private $key, $secret, $api;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('razorpay')->first();
        $razorpayData = json_decode($data->information, true);

        $this->key = $razorpayData['key'];

        $this->secret = $razorpayData['secret'];

        $this->api = new Api($this->key, $this->secret);
    }

    public function index(Request $request, $paymentFor)
    {

        $charge = FeaturedHotelCharge::find($request->charge);

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'INR' or not
        if ($currencyInfo->base_currency_text !== 'INR') {
            return Response::json(['error' => 'Invalid currency for razorpay payment.'], 422);
        }

        $title = 'Activation Feature';
        $notifyURL = route('vendor.hotel_management.hotel.purchase_feature.razorpay.notify');

        // create order data
        $orderData = [
            'receipt'         => $title,
            'amount'          => intval($charge->price * 100),
            'currency'        => 'INR',
            'payment_capture' => 1
        ];

        $razorpayOrder = $this->api->order->create($orderData);

        $webInfo = Basic::select('website_title')->first();

        $customerName = $request['billing_name'] . ' ' . $request['billing_name'];
        $customerEmail = $request['billing_email'];
        $customerPhone = $request['billing_phone'];

        // create checkout data
        $checkoutData = [
            'key'               => $this->key,
            'amount'            => $orderData['amount'],
            'name'              => $webInfo->website_title,
            'description'       => $title . ' via Razorpay.',
            'prefill'           => [
                "name" => "azim",
                "email" => "azimahmed11041@gmail.com",
                "contact" => "+8801749494949",
            ],
            'order_id'          => $razorpayOrder->id
        ];

        $jsonData = json_encode($checkoutData);

        // put some data in session before redirect to razorpay url
        $request->session()->put('razorpayOrderId', $razorpayOrder->id);
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('hotelId', $request->hotel_id);

        return view('frontend.payment.razorpay', compact('jsonData', 'notifyURL'));
    }

    public function notify(Request $request)
    {
        // get the information from session

        $chargeId = $request->session()->get('chargeId');
        $hotelId = $request->session()->get('hotelId');


        $razorpayOrderId = $request->session()->get('razorpayOrderId');

        $urlInfo = $request->all();

        // assume that the transaction was successful
        $success = true;


        try {
            $attributes = [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $urlInfo['razorpayPaymentId'],
                'razorpay_signature' => $urlInfo['razorpaySignature']
            ];

            $this->api->utility->verifyPaymentSignature($attributes);
        } catch (SignatureVerificationError $e) {
            $success = false;
        }

        if ($success === true) {

            $bs = Basic::first();
            $hotelFeature = new HotelFeatureController();
            $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

            if (isset($vendor_mail->to_mail)) {
                $to_mail = $vendor_mail->to_mail;
            } else {
                $to_mail = $vendor_mail->email;
            }

            $charge = FeaturedHotelCharge::find($chargeId);

            $startDate = Carbon::now()->startOfDay();
            $endDate = $startDate->copy()->addDays($charge->days);

            $order =  HotelFeature::where('hotel_id', $hotelId)->first();
            if (empty($order)) {
                $order = new HotelFeature();
            }

            $order->hotel_id = $hotelId;
            $order->vendor_id = Auth::guard('vendor')->user()->id;
            $order->vendor_mail = $to_mail;
            $order->order_number = uniqid();
            $order->total = $charge->price;
            $order->payment_method = "Razorpay";
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
            $invoice = $hotelFeature->generateInvoice($order);

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
                'transcation_type' => 'hotel_feature',
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
            $hotelFeature->prepareMail($to_mail, $charge->price, $order->payment_method, $order->invoice);

            $request->session()->forget('chargeId');
            $request->session()->forget('hotelId');
            return redirect()->route('success.page');
        } else {
            $request->session()->forget('chargeId');
            $request->session()->forget('hotelId');
            return redirect()->back();
        }
    }
}
