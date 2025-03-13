<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Session;
use Ixudra\Curl\Facades\Curl;

class PhonepeController extends Controller
{
    public function index(Request $request, $paymentFor)
    {
        $charge = FeaturedRoomCharge::find($request->charge);

        $currencyInfo = $this->getCurrencyInfo();

        if ($currencyInfo->base_currency_text !== 'INR') {
            return Response::json(['error' => 'Invalid currency for Phonepe payment.'], 422);
        }


        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        $info = OnlineGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        $randomNo = substr(uniqid(), 0, 3);
        $data = array(
            'merchantId' => $information['merchant_id'],
            'merchantTransactionId' => uniqid(),
            'merchantUserId' => 'MUID' . $randomNo,
            'amount' => $charge->price * 100,
            'redirectUrl' => route('vendor.room_management.room.phonepe.notify'),
            'redirectMode' => 'POST',
            'callbackUrl' => route('vendor.room_management.room.phonepe.notify'),
            'mobileNumber' => $request['booking_phone'] ? $request['booking_phone'] : '9999999999',
            'paymentInstrument' =>
            array(
                'type' => 'PAY_PAGE',
            ),
        );

        $encode = base64_encode(json_encode($data));

        $saltKey = $information['salt_key']; // sandbox salt key
        $saltIndex = $information['salt_index'];

        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);

        $finalXHeader = $sha256 . '###' . $saltIndex;


        if ($information['sandbox_status'] == 1) {
            $url = "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay"; // sandbox payment URL
        } else {
            $url = "https://api.phonepe.com/apis/hermes/pg/v1/pay"; // prod payment URL
        }

        $response = Curl::to($url)
            ->withHeader('Content-Type:application/json')
            ->withHeader('X-VERIFY:' . $finalXHeader)
            ->withData(json_encode(['request' => $encode]))
            ->post();

        $rData = json_decode($response);

        if ($rData->success == true) {
            if (!empty($rData->data->instrumentResponse->redirectInfo->url)) {
                // put some data in session before redirect to paytm url
                $request->session()->put('paymentFor', $paymentFor);
                $request->session()->put('chargeId', $request->charge);
                $request->session()->put('roomId', $request->room_id);

                return response()->json(['redirectURL' => $rData->data->instrumentResponse->redirectInfo->url]);
            } else {
                return redirect()->back()->with('error',  __('Payment Canceled') . '!')->withInput();
            }
        } else {
            return Response::json(['error' => 'Payment Canceled.'], 422);
        }
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $chargeId = $request->session()->get('chargeId');
        $roomId = $request->session()->get('roomId');

        $info = OnlineGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        if ($request->code == 'PAYMENT_SUCCESS' && $information['merchant_id'] == $request->merchantId) {
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
            $order->payment_method = "Phonepe";
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
