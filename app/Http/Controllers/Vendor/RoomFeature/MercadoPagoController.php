<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

class MercadoPagoController extends Controller
{
    private $token, $sandbox_status;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('mercadopago')->first();
        $mercadopagoData = json_decode($data->information, true);

        $this->token = $mercadopagoData['token'];
        $this->sandbox_status = $mercadopagoData['sandbox_status'];
    }

    public function index(Request $request, $paymentFor)
    {
        $charge = FeaturedRoomCharge::find($request->charge);


        $allowedCurrencies = array('ARS', 'BOB', 'BRL', 'CLF', 'CLP', 'COP', 'CRC', 'CUC', 'CUP', 'DOP', 'EUR', 'GTQ', 'HNL', 'MXN', 'NIO', 'PAB', 'PEN', 'PYG', 'USD', 'UYU', 'VEF', 'VES');

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return Response::json(['error' => 'Invalid currency for mercadopago payment.'], 422);
        }

        $title = 'Feature Activation ';
        $notifyURL = route('vendor.room_management.room.purchase_feature.mercadopago.notify');
        $cancelURL = route('vendor.room_management.rooms');

        $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

        if (isset($vendor_mail->to_mail)) {
            $vendorEmail = $vendor_mail->to_mail;
        } else {
            $vendorEmail = $vendor_mail->email;
        }


        $curl = curl_init();

        $preferenceData = [
            'items' => [
                [
                    'id' => uniqid(),
                    'title' => $title,
                    'description' => $title . ' via MercadoPago',
                    'quantity' => 1,
                    'currency' => $currencyInfo->base_currency_text,
                    'unit_price' => $charge->price
                ]
            ],
            'payer' => [
                'email' => $vendorEmail
            ],
            'back_urls' => [
                'success' => $notifyURL,
                'pending' => '',
                'failure' => $cancelURL
            ],
            'notification_url' => $notifyURL,
            'auto_return' => 'approved'
        ];

        $httpHeader = ['Content-Type: application/json'];

        $url = 'https://api.mercadopago.com/checkout/preferences?access_token=' . $this->token;

        $curlOPT = [
            CURLOPT_URL             => $url,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => json_encode($preferenceData, true),
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTPHEADER      => $httpHeader
        ];

        curl_setopt_array($curl, $curlOPT);

        $response = curl_exec($curl);
        $responseInfo = json_decode($response, true);

        curl_close($curl);

        // put some data in session before redirect to mercadopago url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('hotelId', $request->hotel_id);

        if ($this->sandbox_status == 1) {
            return response()->json(['redirectURL' => $responseInfo['sandbox_init_point']]);
        } else {
            return response()->json(['redirectURL' => $responseInfo['init_point']]);
        }
    }

    public function notify(Request $request)
    {
        // get the information from session
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');


        if ($request->status == 'approved') {


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
            $order->payment_method = "Mercadopago";
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

            $request->session()->forget('chargeId');
            $request->session()->forget('roomId');
            Session::flash('warning', __('Something Went Wrong') . '!');
            return redirect()->route('vendor.room_management.rooms');
        }
    }
}
