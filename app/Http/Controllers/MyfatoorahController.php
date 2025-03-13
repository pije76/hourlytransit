<?php

namespace App\Http\Controllers;

use App\Http\Controllers\FrontEnd\BookingPayment\MyfatoorahController as BookingPaymentMyfatoorahController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Payment\MyfatoorahController as BuyPlanMyfatoorahController;
use App\Http\Controllers\Vendor\HotelFeature\MyfatoorahController as HotelFeatureMyfatoorahController;
use App\Http\Controllers\Vendor\RoomFeature\MyfatoorahController as RoomFeatureMyfatoorahController;

class MyfatoorahController extends Controller
{

    public function myfatoorah_callback(Request $request)
    {
        $type = Session::get('myfatoorah_payment_type');
        if ($type == 'buy_plan') {
            $data = new BuyPlanMyfatoorahController();
            $data = $data->successCallback($request);
            Session::forget('myfatoorah_payment_type');
            if ($data['status'] == 'success') {
                return redirect()->route('success.page');
            } else {
                $cancel_url = Session::get('cancel_url');
                return redirect($cancel_url);
            }
        } elseif ($type == 'booking') {
            $data = new BookingPaymentMyfatoorahController();
            $data = $data->successCallback($request);
            Session::forget('myfatoorah_payment_type');
            if ($data['status'] == 'success') {
                return redirect()->route('frontend.room_booking.complete', ['type' => 'online']);
            } else {
                Session::flash('warning', 'Payment Cancel.');
                return redirect()->route('frontend.rooms');
            }
        } elseif ($type == 'room_feature') {
            $data = new RoomFeatureMyfatoorahController();
            $data = $data->successCallback($request);
            Session::forget('myfatoorah_payment_type');
            if ($data['status'] == 'success') {
                return redirect()->route('success.page');
            } else {
                $cancel_url = Session::get('cancel_url');
                return redirect($cancel_url);
            }
        } elseif ($type == 'hotel_feature') {
            $data = new HotelFeatureMyfatoorahController();
            $data = $data->successCallback($request);
            Session::forget('myfatoorah_payment_type');
            if ($data['status'] == 'success') {
                return redirect()->route('success.page');
            } else {
                $cancel_url = Session::get('cancel_url');
                return redirect($cancel_url);
            }
        }
    }

    public function myfatoorah_cancel(Request $request)
    {
        $type = Session::get('myfatoorah_payment_type');
        if ($type == 'buy_plan') {
            return redirect()->route('membership.cancel');
        } elseif ($type == 'booking') {
            Session::flash('warning', 'Payment Cancel.');
            return redirect()->route('frontend.rooms');
        } elseif ($type == 'room_feature') {
            return redirect()->route('membership.cancel');
        } elseif ($type == 'hotel_feature') {
            return redirect()->route('membership.cancel');
        }
    }
}
