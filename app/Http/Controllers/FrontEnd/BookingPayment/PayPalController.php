<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
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
use App\Http\Controllers\FrontEnd\BookingPayment\BookingController;
use App\Models\BasicSettings\Basic;
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

    if ($request->session()->has('price')) {
      $priceId = $request->session()->get('price');
    } else {
      Session::flash('error', 'Something went wrong!');

      return redirect()->back();
    }


    $bookingProcess = new BookingController();

    $arrData = $bookingProcess->timeCheck($request,'Paypal');


    // do calculation
    $calculatedData = $bookingProcess->calculation($request, $priceId);


    $currencyInfo = $this->getCurrencyInfo();

    // changing the currency before redirect to PayPal
    if ($currencyInfo->base_currency_text !== 'USD') {
      $rate = floatval($currencyInfo->base_currency_rate);
      $convertedTotal = $calculatedData['grandTotal'] / $rate;
    }

    $paypalTotal = $currencyInfo->base_currency_text === 'USD' ? $calculatedData['grandTotal'] : $convertedTotal;


    $title = 'Room Booking';
    $notifyURL = route('frontend.room.room_booking.paypal.notify');
    $cancelURL = route('frontend.rooms');


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


    $request->session()->put('paymentId', $payment->getId());
    $request->session()->put('arrData', $arrData);

    if (isset($redirectURL)) {
      /** redirect to paypal **/
      return Redirect::away($redirectURL);
    }
  }

  public function notify(Request $request)
  {
    $paymentId = $request->session()->get('paymentId');
    $arrData = $request->session()->get('arrData');

    $urlInfo = $request->all();


    /** Execute The Payment **/
    $payment = Payment::get($paymentId, $this->api_context);
    $execution = new PaymentExecution();
    $execution->setPayerId($urlInfo['PayerID']);
    $result = $payment->execute($execution, $this->api_context);
    if ($result->getState() == 'approved') {

      $bookingProcess = new BookingController();

      // store all data in the database
      $bookingInfo = $bookingProcess->storeData($arrData);


      // generate an invoice in pdf format 
      $invoice = $bookingProcess->generateInvoice($bookingInfo);

      // then, update the invoice field info in database 
      $bookingInfo->update(['invoice' => $invoice]);

      // send a mail to the customer with the invoice
      $bookingProcess->prepareMailForCustomer($bookingInfo);

      // send a mail to the vendor with the invoice
      $bookingProcess->prepareMailForvendor($bookingInfo);

      //tranction part
      $vendor_id = $bookingInfo->vendor_id;


      //calculate commission
      if ($vendor_id == 0) {
        $commission = $bookingInfo->grand_total;
      } else {
        $commission = 0;
      }

      //get vendor
      $vendor = Vendor::where('id', $vendor_id)->first();

      //add blance to admin revinue
      $earning = Basic::first();

      if ($vendor_id == 0) {
        $earning->total_earning = $earning->total_earning + $bookingInfo->grand_total;
      } else {
        $earning->total_earning = $earning->total_earning + $commission;
      }
      $earning->save();

      //store Balance  to vendor
      if ($vendor) {
        $pre_balance = $vendor->amount;
        $vendor->amount = $vendor->amount + ($bookingInfo->grand_total - ($commission));
        $vendor->save();
        $after_balance = $vendor->amount;
      } else {

        $after_balance = NULL;
        $pre_balance = NULL;
      }
      //calculate commission end

      $data = [
        'transcation_id' => time(),
        'booking_id' => $bookingInfo->id,
        'transcation_type' => 'room_booking',
        'user_id' => null,
        'vendor_id' => $vendor_id,
        'payment_status' => 1,
        'payment_method' => $bookingInfo->payment_method,
        'grand_total' => $bookingInfo->grand_total,
        'commission' => $commission,
        'pre_balance' => $pre_balance,
        'after_balance' => $after_balance,
        'gateway_type' => $bookingInfo->gateway_type,
        'currency_symbol' => $bookingInfo->currency_symbol,
        'currency_symbol_position' => $bookingInfo->currency_symbol_position,
      ];
      store_transaction($data);

      // remove all session data
      $request->session()->forget('price');
      $request->session()->forget('checkInTime');
      $request->session()->forget('checkInDate');
      $request->session()->forget('adult');
      $request->session()->forget('children');
      $request->session()->forget('roomDiscount');
      $request->session()->forget('takeService');
      $request->session()->forget('serviceCharge');

      return redirect()->route('frontend.room_booking.complete', ['type' => 'online']);
    } else {
      Session::flash('success', 'Something Went Wrong');
      return redirect()->route('frontend.rooms');
    }
  }
}
