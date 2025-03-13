<?php

namespace App\Http\Controllers\Vendor\HotelFeature;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedHotelCharge;
use App\Models\HotelFeature;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class PerfectMoneyController extends Controller
{
    /*
     * Perfect Money Gateway
     */
    public static function index(Request $request, $event_id)
    {
        $charge = FeaturedHotelCharge::find($request->charge);

        $currencyInfo = Basic::first();

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Payment Gateway Init Start ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        $randomNo = substr(uniqid(), 0, 8);
        $websiteInfo = Basic::select('website_title')->first();
        $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
        $info = json_decode($perfect_money->information, true);
        $val['PAYEE_ACCOUNT'] = $info['perfect_money_wallet_id'];;
        $val['PAYEE_NAME'] = $websiteInfo->website_title;
        $val['PAYMENT_ID'] = "$randomNo";
        $val['PAYMENT_AMOUNT'] = $charge->price;
        $val['PAYMENT_UNITS'] = "$currencyInfo->base_currency_text";

        $val['STATUS_URL'] = route('vendor.hotel_management.hotels.purchase_feature.perfect_money.notify');
        $val['PAYMENT_URL'] = route('vendor.hotel_management.hotels.purchase_feature.perfect_money.notify');
        $val['PAYMENT_URL_METHOD'] = 'GET';
        $val['NOPAYMENT_URL'] = route('frontend.room_booking.cancel');
        $val['NOPAYMENT_URL_METHOD'] = 'GET';
        $val['SUGGESTED_MEMO'] = $request['billing_name'];
        $val['BAGGAGE_FIELDS'] = 'IDENT';

        $data['val'] = $val;
        $data['method'] = 'post';
        $data['url'] = 'https://perfectmoney.com/api/step1.asp';
        $request->session()->put('payment_id', $randomNo);
        $request->session()->put('chargeId', $request->charge);
        $request->session()->put('hotelId', $request->hotel_id);

        return view('frontend.payment.perfect-money', compact('data'));
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~ Payment Gateway Init End ~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }
    public function notify(Request $request)
    {
        // get the information from session
        $chargeId = $request->session()->get('chargeId');
        $hotelId = $request->session()->get('hotelId');
        $charge = FeaturedHotelCharge::find($chargeId);

        $perfect_money = OnlineGateway::where('keyword', 'perfect_money')->first();
        $perfectMoneyInfo = json_decode($perfect_money->information, true);
        $currencyInfo = Basic::select('base_currency_text')->first();

        $amo = $request['PAYMENT_AMOUNT'];
        $unit = $request['PAYMENT_UNITS'];
        $track = $request['PAYMENT_ID'];
        $id = Session::get('payment_id');
        $final_amount = $charge->price;
        // $final_amount = 0.01; //testing  amount

        if ($request->PAYEE_ACCOUNT == $perfectMoneyInfo['perfect_money_wallet_id'] && $unit == $currencyInfo->base_currency_text && $track == $id && $amo == round($final_amount, 2)) {


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
            $order->payment_method = "Perfect Money";
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
            Session::flash('warning', __('Something Went Wrong') . '!');

            return redirect()->route('vendor.hotel_management.hotels');
        }
    }
}
