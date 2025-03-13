<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartalyst\Stripe\Exception\CardErrorException;
use Cartalyst\Stripe\Exception\UnauthorizedException;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\RoomFeature;
use App\Models\Vendor;

class StripeController extends Controller
{
    public function index(Request $request)
    {
        // card validation start
        $rules = [
            'stripeToken' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // card validation end

        $charge = FeaturedRoomCharge::find($request->charge);


        $currencyInfo = $this->getCurrencyInfo();

        // changing the currency before redirect to Stripe
        if ($currencyInfo->base_currency_text !== 'USD') {
            $rate = floatval($currencyInfo->base_currency_rate);
            $convertedTotal = round(($charge->price / $rate), 2);
        }

        $stripeTotal = $currencyInfo->base_currency_text === 'USD' ? $charge->price : $convertedTotal;

        try {
            // initialize stripe
            $stripe = new Stripe();
            $stripe = Stripe::make(Config::get('services.stripe.secret'));

            try {

                // generate charge
                $charge = $stripe->charges()->create([
                    'source' => $request->stripeToken,
                    'currency' => 'USD',
                    'amount'   => $stripeTotal
                ]);

                if ($charge['status'] == 'succeeded') {


                    $bs = Basic::first();
                    $roomFeature = new RoomFeatureController();
                    $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

                    if (isset($vendor_mail->to_mail)) {
                        $to_mail = $vendor_mail->to_mail;
                    } else {
                        $to_mail = $vendor_mail->email;
                    }

                    $charge = FeaturedRoomCharge::find($request->charge);

                    $startDate = Carbon::now()->startOfDay();
                    $endDate = $startDate->copy()->addDays($charge->days);

                    $order =  RoomFeature::where('room_id', $request->room_id)->first();
                    if (empty($order)) {
                        $order = new RoomFeature();
                    }

                    $order->room_id = $request->room_id;
                    $order->vendor_id = Auth::guard('vendor')->user()->id;
                    $order->vendor_mail = $to_mail;
                    $order->order_number = uniqid();
                    $order->total = $charge->price;
                    $order->payment_method = "Stripe";
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
            } catch (CardErrorException $e) {
                Session::flash('error', $e->getMessage());

                Session::flash('warning', __('Something Went Wrong') . '!');
                return redirect()->route('vendor.room_management.rooms');
            }
        } catch (UnauthorizedException $e) {
            Session::flash('error', $e->getMessage());

            Session::flash('warning', __('Something Went Wrong') . '!');
            return redirect()->route('vendor.room_management.rooms');
        }
    }
}
