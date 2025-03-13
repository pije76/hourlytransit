<?php

namespace App\Http\Controllers\Vendor\HotelFeature;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Language;
use App\Models\VendorInfo;
use Illuminate\Http\Request;
use App\Models\BasicSettings\Basic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\FeaturedHotelCharge;
use App\Models\HotelFeature;
use Cache;

class IyzicoController extends Controller
{
    public function paymentProcess(Request $request, $amount, $title, $cancel_url)
    {
        $cancel_url = $cancel_url;
        $notifyURL = route('vendor.hotel_management.hotel.purchase_feature.iyzico.notify');
        Session::put('request', $request->all());

        $currentLang = session()->has('lang') ?
            (Language::where('code', session()->get('lang'))->first())
            : (Language::where('is_default', 1)->first());
        $vendorinfo = VendorInfo::where('vendor_id', Auth::guard('vendor')->user()->id)->where('language_id', $currentLang->id)->with('vendor')->first();
        $vendor = Auth::guard('vendor')->user();
        $charge = FeaturedHotelCharge::find($request->charge);
        $chargeId =$request->charge;
        $hotelId =$request->hotel_id;
        $price  = round($charge->price, 2);

        try {
            $options = options();
            $conversion_id = uniqid(9999, 999999);
            $id_number = $request->identity_number;
            $basket_id = 'B' . uniqid(999, 99999);
            # create request class
            $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
            $request->setLocale(\Iyzipay\Model\Locale::EN);
            $request->setConversationId($conversion_id);
            $request->setPrice("$price");
            $request->setPaidPrice("$price");
            $request->setCurrency(\Iyzipay\Model\Currency::TL);
            $request->setBasketId("$basket_id");
            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
            $request->setCallbackUrl($notifyURL);
            $request->setEnabledInstallments(array(2, 3, 6, 9));

            $buyer = new \Iyzipay\Model\Buyer();
            $buyer->setId(uniqid());
            $buyer->setName(!empty($vendorinfo->name) ? $vendorinfo->name : $vendor->username);
            $buyer->setSurname(!empty($vendorinfo->name) ? $vendorinfo->name : $vendor->username);
            $buyer->setGsmNumber($vendor->phone);
            $buyer->setEmail($vendor->email);
            $buyer->setIdentityNumber("$id_number");
            $buyer->setLastLoginDate("");
            $buyer->setRegistrationDate("");
            $buyer->setRegistrationAddress($vendorinfo->address);
            $buyer->setIp("");
            $buyer->setCity($vendorinfo->city);
            $buyer->setCountry($vendorinfo->country);
            $buyer->setZipCode($vendorinfo->zip_code);
            $request->setBuyer($buyer);

            $shippingAddress = new \Iyzipay\Model\Address();
            $shippingAddress->setContactName(!empty($vendorinfo->name) ? $vendorinfo->name : $vendor->username);
            $shippingAddress->setCity($vendorinfo->city);
            $shippingAddress->setCountry($vendorinfo->country);
            $shippingAddress->setAddress($vendorinfo->address);
            $shippingAddress->setZipCode("$vendorinfo->zip_code");
            $request->setShippingAddress($shippingAddress);

            $billingAddress = new \Iyzipay\Model\Address();
            $billingAddress->setContactName(!empty($vendorinfo->name) ? $vendorinfo->name : $vendor->username);
            $billingAddress->setCity($vendorinfo->city);
            $billingAddress->setCountry($vendorinfo->country);
            $billingAddress->setAddress($vendorinfo->address);
            $billingAddress->setZipCode("$vendorinfo->zip_code");
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
        } catch (\Exception $th) {
           
        }
       
        $paymentResponse = (array)$payWithIyzicoInitialize;

        foreach ($paymentResponse as $key => $data) {
            $paymentInfo = json_decode($data, true);
            if ($paymentInfo['status'] == 'success') {
                if (!empty($paymentInfo['payWithIyzicoPageUrl'])) {
                    Cache::forget('conversation_id');
                    Session::put('iyzico_token', $paymentInfo['token']);
                    Session::put('conversation_id', $conversion_id);
                    Cache::put('conversation_id', $conversion_id, 60000);
                    Session::put('chargeId', $chargeId);
                    Session::put('hotelId', $hotelId);

                    //return for payment
                    return response()->json(['redirectURL' => $paymentInfo['payWithIyzicoPageUrl']]);
                }
            }
            return redirect($cancel_url);
        }
    }

    public function notify(Request $request)
    {
        $requestData = Session::get('request');
        $requestData['status'] = 0;
        $requestData['conversation_id'] = Session::get('conversation_id');
        $chargeId = $request->session()->get('chargeId');
        $hotelId = $request->session()->get('hotelId');
        $bs = Basic::first();
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
        $order->payment_method = "Iyzico";
        $order->gateway_type = "online";
        $order->payment_status = "pending";
        $order->order_status = 'pending';
        $order->days = $charge->days;
        $order->start_date = $startDate;
        $order->end_date = $endDate;
        $order->currency_symbol = $bs->base_currency_symbol;
        $order->currency_symbol_position = $bs->base_currency_symbol_position;
        $order->conversation_id = $requestData['conversation_id'];
        $order->save();

        $request->session()->forget('chargeId');
        $request->session()->forget('hotelId');
        return redirect()->route('success.page');
        
    }

    public function iyzicoCancle()
    {
        $requestData = Session::get('request');
        $paymentFor = Session::get('paymentFor');
        session()->flash('warning', __('cancel payment'));
        if ($paymentFor == "membership") {
            return redirect()->route('front.register.view', ['status' => $requestData['package_type'], 'id' => $requestData['package_id']])->withInput($requestData);
        } else {
            return redirect()->route('vendor.plan.extend.checkout', ['package_id' => $requestData['package_id']])->withInput($requestData);
        }
    }
}
