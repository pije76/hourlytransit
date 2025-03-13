<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\Vendor;
use Cache;
use Illuminate\Http\Request;
use Session;

class IyzicoController extends Controller
{
  public function index(Request $request, $paymentFor)
  {
    if ($request->session()->has('price')) {
      $priceId = $request->session()->get('price');
    } else {
      Session::flash('error', 'Something went wrong!');

      return redirect()->back();
    }

    $bookingProcess = new BookingController();

    // do calculation
    $calculatedData = $bookingProcess->calculation($request, $priceId);

    $currencyInfo = $this->getCurrencyInfo();
    if ($currencyInfo->base_currency_text != 'TRY') {
      return redirect()->back()->with('error', 'Invalid currency for iyzico payment.')->withInput();
    }

    $arrData = $bookingProcess->timeCheck($request, 'Iyzico');


    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

    $name = $request['booking_name'];
    $phone = $request['booking_phone'];
    $email = $request['booking_email'];
    $address = $request['booking_address'];
    $city = $request['booking_address'];
    $country = $request['booking_address'];
    $price = $calculatedData['grandTotal'];
    //payment gateway code start
    $notifyURL = route('frontend.room.room_booking.iyzico.notify');
    $options = options();
    $conversion_id = uniqid(9999, 999999);
    $basket_id = 'B' . uniqid(999, 99999);
    $id_number = $request->identity_number;
    $zip_code = $request->zip_code;

    # create request class
    $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
    $request->setLocale(\Iyzipay\Model\Locale::EN);
    $request->setConversationId($conversion_id);
    $request->setPrice($price);
    $request->setPaidPrice($price);
    $request->setCurrency(\Iyzipay\Model\Currency::TL);
    $request->setBasketId($basket_id);
    $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
    $request->setCallbackUrl($notifyURL);
    $request->setEnabledInstallments(array(2, 3, 6, 9));

    $buyer = new \Iyzipay\Model\Buyer();
    $buyer->setId(uniqid());
    $buyer->setName("$name");
    $buyer->setSurname("$name");
    $buyer->setGsmNumber("$phone");
    $buyer->setEmail("$email");
    $buyer->setIdentityNumber($id_number);
    $buyer->setLastLoginDate("");
    $buyer->setRegistrationDate("");
    $buyer->setRegistrationAddress("$address");
    $buyer->setIp("");
    $buyer->setCity("$city");
    $buyer->setCountry("$country");
    $buyer->setZipCode($zip_code);
    $request->setBuyer($buyer);

    $shippingAddress = new \Iyzipay\Model\Address();
    $shippingAddress->setContactName("$name");
    $shippingAddress->setCity("$city");
    $shippingAddress->setCountry("$country");
    $shippingAddress->setAddress("$address");
    $shippingAddress->setZipCode("$zip_code");
    $request->setShippingAddress($shippingAddress);

    $billingAddress = new \Iyzipay\Model\Address();
    $billingAddress->setContactName("$name");
    $billingAddress->setCity("$city");
    $billingAddress->setCountry("$country");
    $billingAddress->setAddress("$address");
    $billingAddress->setZipCode("$zip_code");
    $request->setBillingAddress($billingAddress);

    $q_id = uniqid(999, 99999);
    $basketItems = array();
    $firstBasketItem = new \Iyzipay\Model\BasketItem();
    $firstBasketItem->setId($q_id);
    $firstBasketItem->setName("Purchase Id " . $q_id);
    $firstBasketItem->setCategory1("Purchase or Booking");
    $firstBasketItem->setCategory2("");
    $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
    $firstBasketItem->setPrice($price);
    $basketItems[0] = $firstBasketItem;
    $request->setBasketItems($basketItems);

    # make request
    $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($request, $options);

    $paymentResponse = (array)$payWithIyzicoInitialize;

    foreach ($paymentResponse as $key => $data) {
      $paymentInfo = json_decode($data, true);
      if ($paymentInfo['status'] == 'success') {
        if (!empty($paymentInfo['payWithIyzicoPageUrl'])) {
          Cache::forget('conversation_id');
          Session::put('iyzico_token', $paymentInfo['token']);
          Session::put('conversation_id', $conversion_id);
          Cache::put('conversation_id', $conversion_id, 60000);
          Session::put('paymentFor', $paymentFor);
          Session::put('arrData', $arrData);
          //return for payment
          return redirect($paymentInfo['payWithIyzicoPageUrl']);
        }
      }
      return redirect()->route('frontend.room_booking.cancel');
    }
  }

  public function notify(Request $request)
  {
    $arrData = $request->session()->get('arrData');
    $arrData['conversation_id'] = Session::get('conversation_id');

    $bookingProcess = new BookingController();

    $bookingProcess->storeData($arrData);

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
  }
}
