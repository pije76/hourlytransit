<?php

namespace App\Http\Controllers\Vendor\RoomFeature;


use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\RoomFeature;
use App\Models\Vendor;
use Auth;
use Basel\MyFatoorah\MyFatoorah;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Session;

class MyfatoorahController extends Controller
{
    public $myfatoorah;

    public function __construct()
    {
        $this->myfatoorah = MyFatoorah::getInstance(true);
    }

    public function  index(Request $request, $_amount, $_title, $_cancel_url)
    {
        try {
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
            ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $info = OnlineGateway::where('keyword', 'myfatoorah')->first();
            $information = json_decode(
                $info->information,
                true
            );
            $random_1 = rand(999, 9999);
            $random_2 = rand(9999, 99999);
            $result = $this->myfatoorah->sendPayment(
                Auth::guard('vendor')->user()->username,
                $price,
                [
                    'CustomerMobile' => $information['sandbox_status'] == 1 ? '56562123544' : $request->phone,
                    'CustomerReference' => "$random_1",  //orderID
                    'UserDefinedField' => "$random_2", //clientID
                    "InvoiceItems" => [
                        [
                            "ItemName" => "Hotel Feature",
                            "Quantity" => 1,
                            "UnitPrice" => $price
                        ]
                    ]
                ]
            );
            if ($result && $result['IsSuccess'] == true) {
                $request->session()->put('myfatoorah_payment_type', 'room_feature');
                $request->session()->put('chargeId', $request->charge);
                $request->session()->put('roomId', $request->room_id);
                return response()->json(['redirectURL' => $result['Data']['InvoiceURL']]);
            }
        } catch (Exception $e) {
            return redirect($cancel_url)->with('error', 'Payment Canceled');
        }
    }

    public function successCallback(Request $request)
    {
        if (!empty($request->paymentId)) {
            $result = $this->myfatoorah->getPaymentStatus('paymentId', $request->paymentId);
            if ($result && $result['IsSuccess'] == true && $result['Data']['InvoiceStatus'] == "Paid") {
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
                $order->payment_method = "Myfatoorah";
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
                return [
                    'status' => 'success'
                ];
            } else {
                return [
                    'status' => 'fail'
                ];
            }
        } else {
            return [
                'status' => 'fail'
            ];
        }
    }

    public function failCallback(Request $request)
    {
        $cancel_url = Session::get('cancel_url');
        return redirect($cancel_url);
    }
}
