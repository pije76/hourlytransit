<?php

namespace App\Http\Controllers\Vendor\RoomFeature;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

class PaystackController extends Controller
{
    private $api_key;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('paystack')->first();
        $paystackData = json_decode($data->information, true);

        $this->api_key = $paystackData['key'];
    }

    public function index(Request $request)
    {
        $charge = FeaturedRoomCharge::find($request->charge);

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'NGN' or not
        if ($currencyInfo->base_currency_text !== 'NGN') {
            return Response::json(['error' => 'Invalid currency for paystack payment.'], 422);
        }


        $notifyURL = route('vendor.room_management.room.purchase_feature.paystack.notify');

        $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

        if (isset($vendor_mail->to_mail)) {
            $vendorEmail = $vendor_mail->to_mail;
        } else {
            $vendorEmail = $vendor_mail->email;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.paystack.co/transaction/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode([
                'amount'       => intval($charge->price) * 100,
                'email'        => $vendorEmail,
                'callback_url' => $notifyURL
            ]),
            CURLOPT_HTTPHEADER     => [
                'authorization: Bearer ' . $this->api_key,
                'content-type: application/json',
                'cache-control: no-cache'
            ]
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $transaction = json_decode($response, true);

        // put some data in session before redirect to paystack url
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('roomId', $request->room_id);

        if ($transaction['status'] == true) {
            return response()->json(['redirectURL' => $transaction['data']['authorization_url']]);
        } else {
            return Response::json(['error' => $transaction['message']], 422);
        }
    }

    public function notify(Request $request)
    {
        // get the information from session
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');

        $urlInfo = $request->all();

        if ($urlInfo['trxref'] === $urlInfo['reference']) {


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
            $order->payment_method = "Paystack";
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
            return redirect()->back();
        }
    }
}
