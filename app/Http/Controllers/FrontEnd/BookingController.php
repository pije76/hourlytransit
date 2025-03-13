<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\HourlyRoomPrice;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Room;
use App\Models\RoomContent;
use App\Models\RoomCoupon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function checkCheckout(Request $request)
    {
        $rule = [
            'price' => 'required'
        ];

        $messages = [
            'price.required' => __('Please select a booking hour to proceed.')
        ];

        $validator = Validator::make($request->all(), $rule, $messages);

        if ($validator->fails()) {
            return response()->json(
                [
                    'alert_type' => 'error',
                    'message' => 'Validation error occurred.',
                    'errors' => $validator->errors()
                ],
                422
            );
        }

        $request->session()->put('checkInTime', checkInTimeFormate($request->checkInTime));
        $request->session()->put('checkInDate', $request->checkInDate);
        $request->session()->put('price', $request->price);
        $request->session()->put('adult', $request->adult);
        $request->session()->put('children', $request->children);
        Session::forget('serviceCharge');
        Session::forget('takeService');
        Session::forget('roomDiscount');

        return response()->json([
            'redirect_url' => route('frontend.room.checkout')
        ], 200);
    }
    public function checkout(Request $request)
    {
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $information['language'] = $language;

        $information['pageHeading'] = $misc->getPageHeading($language);
        $information['bgImg'] = $misc->getBreadcrumb();
        $information['authUser'] = Auth::guard('web')->check() == true ? Auth::guard('web')->user() : null;

        $information['currencyInfo'] = $this->getCurrencyInfo();

        $information['onlineGateways'] = OnlineGateway::where('status', 1)->get();

        $information['offline_gateways'] = OfflineGateway::where('status', 1)->orderBy('serial_number', 'asc')->get();

        $stripe = OnlineGateway::where('keyword', 'stripe')->first();
        $stripe_info = json_decode($stripe->information, true);
        $information['stripe_key'] = $stripe_info['key'];


        $authorizenet = OnlineGateway::query()->whereKeyword('authorize.net')->first();
        $anetInfo = json_decode($authorizenet->information);

        if ($anetInfo->sandbox_check == 1) {
            $information['anetSource'] = 'https://jstest.authorize.net/v1/Accept.js';
        } else {
            $information['anetSource'] = 'https://js.authorize.net/v1/Accept.js';
        }

        $information['anetClientKey'] = $anetInfo->public_key;
        $information['anetLoginId'] = $anetInfo->login_id;


        $information['checkInTime'] = $request->session()->get('checkInTime');
        $information['checkInDate']  = $request->session()->get('checkInDate');
        $price  = $request->session()->get('price');
        $information['price'] = $price;

        $information['adult']  = $request->session()->get('adult');
        $information['children']  = $request->session()->get('children');

        if ($information['checkInTime'] && $information['checkInDate'] && $information['price'] && $information['adult']) {

            $roomId = HourlyRoomPrice::find($price)->room_id;
            $additionalServices = json_decode(Room::find($roomId)->additional_service);

            $information['room'] = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
                ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
                ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
                ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
                ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
                ->where('hotel_contents.language_id', $language->id)
                ->where('room_categories.status', 1)
                ->where('hotel_categories.status', 1)
                ->where('room_contents.language_id', $language->id)
                ->where('rooms.status',  '=',    '1')
                ->where('hotels.status',  '=',    '1')

                ->where([
                    ['rooms.status', '=', '1']
                ])

                ->select(
                    'rooms.*',
                    'room_contents.title',
                    'room_contents.slug',
                    'room_contents.amenities',
                    'room_categories.name as room_category',
                    'room_categories.slug as room_category_slug',
                    'room_contents.meta_keyword',
                    'room_contents.meta_description',
                    'hotel_contents.address',
                    'room_contents.description',
                    'hotels.id as hotelId',
                    'hotels.latitude as latitude',
                    'hotels.longitude as longitude',
                )
                ->where('rooms.id', $roomId)
                ->firstorFail();

            if ($additionalServices) {
                $information['additionalServices'] = $additionalServices;
            } else {
                $information['additionalServices'] = null;
            }
            return view('frontend.room.checkout', $information);
        } else {
            return redirect(route('frontend.rooms'));
        }
    }

    public function applyCoupon(Request $request)
    {
        try {
            $coupon = RoomCoupon::where('code', $request->coupon)->firstOrFail();

            $startDate = Carbon::parse($coupon->start_date);
            $endDate = Carbon::parse($coupon->end_date);
            $todayDate = Carbon::now();

            if ($todayDate->between($startDate, $endDate) == false) {
                return response()->json(['error' => __('Sorry, coupon has been expired!')]);
            }

            $price_id = $request->session()->get('price');
            $serviceCharge = $request->session()->get('serviceCharge');
            $roomId = HourlyRoomPrice::findorfail($price_id)->room_id;
            $price = HourlyRoomPrice::findorfail($price_id)->price;

            $roomIds = empty($coupon->rooms) ? '' : json_decode($coupon->rooms);

            if (!empty($roomIds) && !in_array($roomId, $roomIds)) {
                return response()->json(['error' => __('You can not apply this coupon for this room!')]);
            }

            session()->put('couponCode', $request->coupon);

            if ($coupon->type == 'fixed') {

                $request->session()->put('roomDiscount', $coupon->value);
                return response()->json([
                    'success' => __('Coupon applied successfully.'),
                ]);
            } else {

                $couponAmount = ($price + $serviceCharge) * ($coupon->value / 100);
                $request->session()->put('roomDiscount', $couponAmount);

                return response()->json([
                    'success' => __('Coupon applied successfully.'),
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => __('Coupon is not valid!')]);
        }
    }
    public function additonalService(Request $request)
    {
        $haven = Session::get('takeService');
        $taken = $request->takeService;

        $havenArray = !empty($haven) ? explode(',', $haven) : [];
        $takenArray = !empty($taken) ? explode(',', $taken) : [];

        $havenCount = count($havenArray);
        $takenCount = count($takenArray);

        Session::put('serviceCharge', $request->serviceCharge);
        Session::put('takeService', $request->takeService);
        Session::forget('roomDiscount');
        $couponCode = Session::forget('couponCode');


        if ($havenCount > $takenCount) {
            return response()->json([
                'error' => __('Additional Service removed successfully.'),
            ]);
        } else {
            return response()->json([
                'success' => __('Additional Service added successfully.'),
            ]);
        }
    }
}
