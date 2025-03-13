<?php

namespace App\Http\Controllers\Vendor\HotelFeature;


use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedHotelCharge;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Support\Facades\Config;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\HotelFeature;
use App\Models\Vendor;

class PayPalController extends Controller
{
    private $api_context;

    public function __construct()
    {
        $data = OnlineGateway::whereKeyword('paypal')->first();
        $paypalData = json_decode($data->information, true);

        $paypal_conf = Config::get('paypal');
        $paypal_conf['client_id'] = $paypalData['client_id'];
        $paypal_conf['secret'] = $paypalData['client_secret'];
        $paypal_conf['settings']['mode'] = $paypalData['sandbox_status'] == 1 ? 'sandbox' : 'live';

        $this->api_context = new ApiContext(
            new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret']
            )
        );

        $this->api_context->setConfig($paypal_conf['settings']);
    }

    public function index(Request $request, $paymentFor)
    {
        $charge = FeaturedHotelCharge::find($request->charge);

        $currencyInfo = $this->getCurrencyInfo();

        // changing the currency before redirect to PayPal
        if ($currencyInfo->base_currency_text !== 'USD') {
            $rate = floatval($currencyInfo->base_currency_rate);
            $convertedTotal = $charge->price / $rate;
        }

        $paypalTotal = $currencyInfo->base_currency_text === 'USD' ? $charge->price : $convertedTotal;


        $title = 'Activation Feature';
        $notifyURL = route('vendor.hotel_management.hotel.purchase_feature.paypal.notify');
        $cancelURL = route('vendor.hotel_management.hotels');

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $item_1 = new Item();
        $item_1->setName($title)
            /** item name **/
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice($paypalTotal);
        /** unit price **/
        $item_list = new ItemList();
        $item_list->setItems(array($item_1));
        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($paypalTotal);
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription($title . ' via PayPal');
        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl($notifyURL)
            /** Specify return URL **/
            ->setCancelUrl($cancelURL);
        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {
            $payment->create($this->api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            return redirect($cancelURL)->with('error', $ex->getMessage());
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirectURL = $link->getHref();
                break;
            }
        }

        // put some data in session before redirect to paypal url
        $request->session()->put('paymentFor', $paymentFor);
        $request->session()->put('paymentId', $payment->getId());
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('hotelId', $request->hotel_id);

        if (isset($redirectURL)) {
            /** redirect to paypal **/
            return response()->json(['redirectURL' => $redirectURL]);
        }
    }

    public function notify(Request $request)
    {


        $paymentId = $request->session()->get('paymentId');
        $chargeId = $request->session()->get('chargeId');
        $hotelId = $request->session()->get('hotelId');
        // get the information from session

        $urlInfo = $request->all();


        /** Execute The Payment **/
        $payment = Payment::get($paymentId, $this->api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($urlInfo['PayerID']);
        $result = $payment->execute($execution, $this->api_context);
        if ($result->getState() == 'approved') {
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
            $order->payment_method = "Paypal";
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
