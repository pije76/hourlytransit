<?php

namespace App\Http\Controllers\Vendor\HotelFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedHotelCharge;
use App\Models\HotelFeature;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

class FlutterwaveController extends Controller
{
    private $public_key, $secret_key;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('flutterwave')->first();
        $flutterwaveData = json_decode($data->information, true);

        $this->public_key = $flutterwaveData['public_key'];
        $this->secret_key = $flutterwaveData['secret_key'];
    }

    public function index(Request $request, $paymentFor)
    {


        $charge = FeaturedHotelCharge::find($request->charge);


        $allowedCurrencies = array('BIF', 'CAD', 'CDF', 'CVE', 'EUR', 'GBP', 'GHS', 'GMD', 'GNF', 'KES', 'LRD', 'MWK', 'MZN', 'NGN', 'RWF', 'SLL', 'STD', 'TZS', 'UGX', 'USD', 'XAF', 'XOF', 'ZMK', 'ZMW', 'ZWD');

        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return Response::json(['error' => 'Invalid currency for flutterwave payment.'], 422);
        }

        $title = 'Activation Feature';
        $notifyURL = route('vendor.hotel_management.hotel.purchase_feature.flutterwave.notify');


        $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);
        $customerName = $request['billing_name'];
        if (isset($vendor_mail->to_mail)) {
            $vendorEmail = $vendor_mail->to_mail;
        } else {
            $vendorEmail = $vendor_mail->email;
        }
        $customerPhone = $request['billing_phone'];


        // send payment to flutterwave for processing
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.flutterwave.com/v3/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'tx_ref' => 'FLW | ' . time(),
                'amount' => $charge->price,
                'currency' => $currencyInfo->base_currency_text,
                'redirect_url' => $notifyURL,
                'payment_options' => 'card,banktransfer',
                'customer' => [
                    'email' => $vendorEmail,
                    'phone_number' => $customerPhone,
                    'name' => $customerName
                ],
                'customizations' => [
                    'title' => $title,
                    'description' => $title . ' via Flutterwave.'
                ]
            ]),
            CURLOPT_HTTPHEADER => array(
                'authorization: Bearer ' . $this->secret_key,
                'content-type: application/json'
            )
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseData = json_decode($response, true);

        //curl end

        // put some data in session before redirect to flutterwave url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('hotelId', $request->hotel_id);

        // redirect to payment
        if ($responseData['status'] === 'success') {
            return Response::json(['redirectURL' => $responseData['data']['link']]);
        } else {
            return redirect()->back()->with('error', 'Error: ' . $responseData['message'])->withInput();
        }
    }

    public function notify(Request $request)
    {
        // get the information from session
        $chargeId = $request->session()->get('chargeId');
        $hotelId = $request->session()->get('hotelId');

        $urlInfo = $request->all();

        if ($urlInfo['status'] == 'successful') {
            $txId = $urlInfo['transaction_id'];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$txId}/verify",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'authorization: Bearer ' . $this->secret_key,
                    'content-type: application/json'
                )
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $responseData = json_decode($response, true);
            if ($responseData['status'] === 'success') {


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
                $order->payment_method = "FlutterWave";
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
            }
        } else {
            $request->session()->forget('chargeId');
            $request->session()->forget('hotelId');

            Session::flash('success', __('Something Went Wrong') . '!');
            return redirect()->route('vendor.hotel_management.hotels');
        }
    }
}
