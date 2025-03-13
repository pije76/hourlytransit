<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\BookingPayment\BookingController;
use App\Http\Helpers\UploadFile;
use App\Models\BookingHour;
use App\Models\Hotel;
use App\Models\HourlyRoomPrice;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\Room;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OfflineController extends Controller
{
    public function index(Request $request)
    {
        $gatewayId = $request->gateway;
        $offlineGateway = OfflineGateway::query()->findOrFail($gatewayId);

        // validation start
        if ($offlineGateway->has_attachment == 1) {
            $rules = [
                'attachment' => [
                    'required',
                    new ImageMimeTypeRule()
                ]
            ];

            $message = [
                'attachment.required' => 'Please attach your payment receipt.'
            ];

            $validator = Validator::make($request->only('attachment'), $rules, $message);

            Session::flash('gatewayId', $offlineGateway->id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
        }
        // validation end

        $price_id = $request->session()->get('price');

        $Time = $request->session()->get('checkInTime');
        $Date = $request->session()->get('checkInDate');
        $adult = $request->session()->get('adult');
        $children = $request->session()->get('children');

        $hour_id = HourlyRoomPrice::findorfail($price_id)->hour_id;
        $hour = BookingHour::findorfail($hour_id)->hour;
        $vendor_id = HourlyRoomPrice::findorfail($price_id)->vendor_id;
        $hotel_id = HourlyRoomPrice::findorfail($price_id)->hotel_id;
        $room_id = HourlyRoomPrice::findorfail($price_id)->room_id;
        $preparation_time = Room::findorfail($room_id)->preparation_time;
        $checkInTime = date('H:i:s', strtotime($Time));
        $checkInDate = date('Y-m-d', strtotime($Date));
        $check_in_date_time = $checkInDate . ' ' . $checkInTime;

        $checkoutTime = date('H:i:s', strtotime($Time . " +$hour hour"));
        $next_booking_time = date('H:i:s', strtotime($checkoutTime . " +$preparation_time min"));

        list($current_hour, $current_minute, $current_second) = explode(':', $checkInTime);
        $total_hours = (int)$current_hour + $hour;
        $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

        $checkoutTimeLimit = '23:59:59';

        if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
            $checkoutDate = date('Y-m-d', strtotime($checkInDate . ' +1 day'));
        } else {
            $checkoutDate = date('Y-m-d', strtotime($checkInDate));
        }
        

        $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;



        if ($request->session()->has('price')) {
            $priceId = $request->session()->get('price');
        } else {
            Session::flash('error', 'Something went wrong!');

            return redirect()->back();
        }

        $bookingProcess = new BookingController();

        // do calculation
        $calculatedData = $bookingProcess->calculation($request, $priceId);

        $directory = public_path('assets/file/attachments/room-booking/');

        // store attachment in local storage
        if ($request->hasFile('attachment')) {
            $attachmentName = UploadFile::store($directory, $request->file('attachment'));
        } else {
            $attachmentName = null;
        }
        $currencyInfo = $this->getCurrencyInfo();

        $arrData = array(
            'check_in_time' => $checkInTime,
            'check_in_date' =>  $checkInDate,
            'check_out_date' => $checkoutDate,
            'check_out_time' =>  $checkoutTime,
            'check_in_date_time' =>  $check_in_date_time,
            'check_out_date_time' =>  $check_out_date_time,
            'vendor_id' =>  $vendor_id,
            'hotel_id' =>  $hotel_id,
            'room_id' =>  $room_id,
            'preparation_time' =>  $preparation_time,
            'next_booking_time' =>  $next_booking_time,
            'hour' =>  $hour,
            'adult' =>  $adult,
            'children' => $children,
            'booking_name' => $request['booking_name'],
            'booking_email' => $request['booking_email'],
            'booking_phone' => $request['booking_phone'],
            'booking_address' => $request['booking_address'],
            'additional_service' => $calculatedData['additional_service'],
            'service_details' => $calculatedData['service_details'],
            'roomPrice' => $calculatedData['roomPrice'],
            'serviceCharge' => $calculatedData['serviceCharge'],
            'total' => $calculatedData['total'],
            'discount' => $calculatedData['discount'],
            'tax' => $calculatedData['tax'],
            'grandTotal' => $calculatedData['grandTotal'],
            'currencyText' => $currencyInfo->base_currency_text,
            'currencyTextPosition' => $currencyInfo->base_currency_text_position,
            'currencySymbol' => $currencyInfo->base_currency_symbol,
            'currencySymbolPosition' => $currencyInfo->base_currency_symbol_position,
            'paymentMethod' => $offlineGateway->name,
            'gatewayType' => 'offline',
            'payment_status' => 0,
            'attachment' => $attachmentName
        );

        // store product order information in database
        $bookingInfo = $bookingProcess->storeData($arrData);


        // generate an invoice in pdf format 
        $invoice = $bookingProcess->generateInvoice($bookingInfo);

        // then, update the invoice field info in database 
        $bookingInfo->update(['invoice' => $invoice]);


        // send a mail to the vendor with the invoice
        $bookingProcess->prepareMailForvendor($bookingInfo);

        // then subtract each product quantity from respective product stock


        // remove all session data
        $request->session()->forget('price');
        $request->session()->forget('checkInTime');
        $request->session()->forget('checkInDate');
        $request->session()->forget('adult');
        $request->session()->forget('children');
        $request->session()->forget('roomDiscount');
        $request->session()->forget('takeService');
        $request->session()->forget('serviceCharge');


        return redirect()->route('frontend.room_booking.complete', ['type' => 'offline_booking']);
    }
}
