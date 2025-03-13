<?php

namespace App\Http\Controllers\Vendor\HotelFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Omnipay\Omnipay;
use Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedHotelCharge;
use App\Models\HotelFeature;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

class AuthorizenetController extends Controller
{
    private $gateway;
    public function __construct()
    {
        $data = OnlineGateway::query()->whereKeyword('authorize.net')->first();
        $authorizeNetData = json_decode($data->information, true);
        $this->gateway = Omnipay::create('AuthorizeNetApi_Api');
        $this->gateway->setAuthName($authorizeNetData['login_id']);
        $this->gateway->setTransactionKey($authorizeNetData['transaction_key']);
        if ($authorizeNetData['sandbox_check'] == 1) {
            $this->gateway->setTestMode(true);
        }
    }
    public function index(Request $request, $paymentFor)
    {


        $currencyInfo = $this->getCurrencyInfo();

        // checking whether the currency is set to 'INR' or not
        $allowedCurrencies = array('USD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'NOK', 'PLN', 'SEK', 'AUD', 'NZD');
        $currencyInfo = $this->getCurrencyInfo();
        // checking whether the base currency is allowed or not
        if (!in_array($currencyInfo->base_currency_text, $allowedCurrencies)) {
            return Response::json(['error' => 'Invalid currency for authorize.net payment.'], 422);
        }

        // put some data in session before redirect to paytm url;
        $charge = FeaturedHotelCharge::find($request->charge);

        if ($request->filled('opaqueDataValue') && $request->filled('opaqueDataDescriptor')) {

            // generate a unique merchant site transaction ID
            $transactionId = rand(100000000, 999999999);
            $response = $this->gateway->authorize([
                'amount' => sprintf('%0.2f', $charge->price),
                'currency' => $currencyInfo->base_currency_text,
                'transactionId' => $transactionId,
                'opaqueDataDescriptor' => $request->opaqueDataDescriptor,
                'opaqueDataValue' => $request->opaqueDataValue
            ])->send();

            if ($response->isSuccessful()) {


                $bs = Basic::first();
                $hotelFeature = new HotelFeatureController();
                $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

                if (isset($vendor_mail->to_mail)) {
                    $to_mail = $vendor_mail->to_mail;
                } else {
                    $to_mail = $vendor_mail->email;
                }

                $charge = FeaturedHotelCharge::find($request->charge);

                $startDate = Carbon::now()->startOfDay();
                $endDate = $startDate->copy()->addDays($charge->days);

                $order =  HotelFeature::where('hotel_id', $request->hotel_id)->first();
                if (empty($order)) {
                    $order = new HotelFeature();
                }

                $order->hotel_id = $request->hotel_id;
                $order->vendor_id = Auth::guard('vendor')->user()->id;
                $order->vendor_mail = $to_mail;
                $order->order_number = uniqid();
                $order->total = $charge->price;
                $order->payment_method = "Authorized.net";
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
                Session::flash('success', __('Something Went Wrong') . '!');
                return redirect()->route('vendor.hotel_management.hotels');
            }
        } else {
            $request->session()->forget('chargeId');
            $request->session()->forget('hotelId');
            Session::flash('success', __('Something Went Wrong') . '!');
            return redirect()->route('vendor.hotel_management.hotels');
        }
        $request->session()->forget('chargeId');
        $request->session()->forget('hotelId');
        Session::flash('success', __('Something Went Wrong') . '!');
        return redirect()->route('vendor.hotel_management.hotels');
    }
}
