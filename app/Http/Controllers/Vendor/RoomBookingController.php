<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\BookingPayment\BookingController;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Http\Helpers\BasicMailer;
use App\Http\Requests\Room\AdminRoomBookingRequest;
use App\Models\AdditionalService;
use App\Models\BasicSettings\Basic;
use App\Models\Booking;
use App\Models\BookingHour;
use App\Models\Holiday;
use App\Models\HourlyRoomPrice;
use App\Models\Language;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Membership;
use App\Models\Room;
use App\Models\RoomContent;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Purifier;

class RoomBookingController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $order_number = $title =  null;

        if ($request->filled('booking_no')) {
            $order_number = $request['booking_no'];
        }

        $roomIds = [];
        if ($request->input('title')) {
            $title = $request->title;
            $room_contents = RoomContent::where('title', 'like', '%' . $title . '%')->get();
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content->room_id, $roomIds)) {
                    array_push($roomIds, $room_content->room_id);
                }
            }
        }
        $vendor_id = Auth::guard('vendor')->user()->id;

        if (URL::current() == Route::is('vendor.room_bookings.all_bookings')) {
            $information['bookings'] = Booking::Where('vendor_id', $vendor_id)
                ->when($order_number, function ($query, $order_number) {
                    return $query->where('order_number', 'like', '%' . $order_number . '%');
                })->when($title, function ($query) use ($roomIds) {
                    return $query->whereIn('room_id', $roomIds);
                })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else if (URL::current() == Route::is('vendor.room_bookings.paid_bookings')) {
            $information['bookings'] = Booking::Where('vendor_id', $vendor_id)
                ->when($order_number, function ($query, $order_number) {
                    return $query->where('order_number', 'like', '%' . $order_number . '%');
                })
                ->when($title, function ($query) use ($roomIds) {
                    return $query->whereIn('room_id', $roomIds);
                })
                ->where('payment_status', 1)
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else if (URL::current() == Route::is('vendor.room_bookings.unpaid_bookings')) {
            $information['bookings'] = Booking::Where('vendor_id', $vendor_id)
                ->when($order_number, function ($query, $order_number) {
                    return $query->where('order_number', 'like', '%' . $order_number . '%');
                })
                ->when($title, function ($query) use ($roomIds) {
                    return $query->whereIn('room_id', $roomIds);
                })
                ->where('payment_status', 0)
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        $vendor_id = Auth::guard('vendor')->user()->id;

        $information['roomInfos'] = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
            ->Where('rooms.vendor_id', $vendor_id)
            ->Where('room_contents.language_id', $language->id)
            ->select('room_contents.room_id', 'room_contents.title')
            ->orderBy('room_contents.title', 'ASC')
            ->get();

        return view('vendors.room-booking.bookings', $information);
    }
    public function details(Request $request, $id)
    {
        $details = Booking::where([['id', $id], ['vendor_id', Auth::guard('vendor')->user()->id]])->firstOrFail();
        $information['details'] = $details;

        $roomInfo = $details->hotelRoom()->first();
        $information['additional_services']   = json_decode($details->service_details);

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();

        $roomInfo = $details->hotelRoom()->first();

        $information['additional_services']   = json_decode($details->service_details);
        if ($roomInfo) {
            $roomContentInfo = $roomInfo->room_content()->where('language_id', $language->id)->first();
            if ($roomContentInfo) {
                $information['roomContentInfo'] = $roomContentInfo;
            } else {
                $information['roomContentInfo'] = '--';
            }
        } else {
            $information['roomContentInfo'] = '--';
        }

        $hotelInfo = $details->hotel()->first();

        if ($hotelInfo) {
            $hotelContentInfo = $hotelInfo->hotel_contents()->where('language_id', $language->id)->first();
            if ($hotelContentInfo) {
                $information['hotelContentInfo'] = $hotelContentInfo;
            } else {
                $information['hotelContentInfo'] = '--';
            }
        } else {
            $information['hotelContentInfo'] = '--';
        }

        $information['onlineGateways'] = OnlineGateway::query()
            ->where('status', '=', 1)
            ->select('name')
            ->get();

        $information['offlineGateways'] = OfflineGateway::query()
            ->where('status', '=', 1)
            ->select('name')
            ->orderBy('serial_number', 'asc')
            ->get();

        return view('vendors.room-booking.details', $information);
    }

    public function sendMail(Request $request)
    {
        $rules = [
            'subject' => 'required',
            'message' => 'required',
        ];

        $messages = [
            'subject.required' => 'The email subject field is required.',
            'message.required' => 'The email message field is required.'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $mailData['subject'] = $request->subject;
        $mailBody = Purifier::clean($request->message, 'youtube');

        $mailData['body'] = $mailBody;
        $mailData['recipient'] = $request->customer_email;
        $mailData['sessionMessage'] = "Mail sent successfully!";

        BasicMailer::sendMail($mailData);

        /**
         * this 'success' is returning for ajax call.
         * if return == 'success' then ajax will reload the page.
         */
        return Response::json(['status' => 'success'], 200);
    }
    // room booking from admin panel
    public function bookedDates(Request $request)
    {
        $rule = [
            'room_id' => 'required'
        ];

        $message = [
            'room_id.required' => 'Please select a room.'
        ];

        $validator = Validator::make($request->all(), $rule, $message);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->getMessageBag()
            ]);
        }

        // get all the booked dates of the selected room
        $roomId = $request['room_id'];

        return response()->json([
            'success' => route('vendor.room_bookings.booking_form', ['room_id' => $roomId])
        ]);
    }
    public function bookingForm(Request $request)
    {
        $vendor_id = Auth::guard('vendor')->user()->id;
        $vendorTotalBooking = vendorTotalBooking($vendor_id);
        $vendorTotalBookingInPackage = vendorTotalBookingInPackage($vendor_id);

        if ($vendorTotalBooking >= $vendorTotalBookingInPackage) {
            Session::flash('warning', __('Your reach your booking limit') . '!');
            return redirect()->route('vendor.room_bookings.all_bookings', ['language' => Auth::guard('vendor')->user()->code]);
        }


        $permissions = addBookingPermission($vendor_id);
        if ($permissions) {

            if (session()->has('bookedDates')) {
                $information['dates'] = session()->get('bookedDates');
            } else {
                $information['dates'] = [];
            }

            $id = $request['room_id'];
            $room = Room::where('vendor_id', '=', Auth::guard('vendor')->user()->id)->findOrFail($id);
            $information['id'] = $id;

            $check_in_time = date('H:i:s', strtotime('00:00:00'));
            $check_in_date = date('Y-m-d');
            $check_in_date_time = $check_in_date . ' ' . $check_in_time;


            $totalRoom = $room->number_of_rooms_of_this_same_type;
            $preparation_time = $room->preparation_time;
            $maxhour = 99;
            $hours = BookingHour::orderBy('hour', 'desc')->get();
            $bookingStatus = false;

            $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

            $holidays  = array_map(
                function ($holiday) {
                    return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
                },
                $holiday->toArray()
            );
            $information['holidayDates']  = $holidays;

            foreach ($hours as $hour) {
                $check_out_time = date('H:i:s', strtotime($check_in_time . " +{$hour->hour} hour"));

                $next_booking_time = date('H:i:s', strtotime($check_out_time . " +$preparation_time min"));


                list($current_hour, $current_minute, $current_second) = explode(':', $check_in_time);
                $total_hours = (int)$current_hour + $hour->hour;
                $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

                $checkoutTimeLimit = '23:59:59';

                if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
                    $checkoutDate = date('Y-m-d', strtotime($check_in_date . ' +1 day'));
                } else {
                    $checkoutDate = date('Y-m-d', strtotime($check_in_date));
                }

                $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;

                $convertedHolidays = array_map(function ($holiday) {
                    return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
                }, $holidays);


                if (!in_array($checkoutDate, $convertedHolidays)) {
                    $totalBookingDone = Booking::where('room_id', $id)
                        ->where('payment_status', '!=', 2)
                        ->where(function ($query) use ($check_in_date_time, $check_out_date_time) {
                            $query->where(function ($q) use ($check_in_date_time, $check_out_date_time) {
                                $q->whereBetween('check_in_date_time', [$check_in_date_time, $check_out_date_time])
                                    ->orWhereBetween('check_out_date_time', [$check_in_date_time, $check_out_date_time]);
                            })
                                ->orWhere(function ($q) use ($check_in_date_time, $check_out_date_time) {
                                    $q->where('check_in_date_time', '<=', $check_in_date_time)
                                        ->where('check_out_date_time', '>=', $check_out_date_time);
                                });
                        })
                        ->count();
                } else {
                    $totalBookingDone = 999999;
                }

                if ($totalRoom > $totalBookingDone) {
                    $bookingStatus = true;
                    $maxhour = $hour->hour;
                    break;
                }
            }

            if ($bookingStatus) {
                $information['hourlyPrices'] = HourlyRoomPrice::where('room_id', $id)
                    ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                    ->where('hourly_room_prices.price', '!=', null)
                    ->orderBy('booking_hours.serial_number')
                    ->where('hourly_room_prices.hour', '<=', $maxhour)
                    ->select('hourly_room_prices.*', 'booking_hours.serial_number')
                    ->get();
            } else {
                $information['hourlyPrices'] = [];
            }


            $room = Room::where('id', $id)->firstOrFail();
            $information['rent'] = $room->rent;

            $information['currencyInfo'] = $this->getCurrencyInfo();

            $information['onlineGateways'] = OnlineGateway::query()
                ->where('status', '=', 1)
                ->select('name')
                ->get();

            $information['offlineGateways'] = OfflineGateway::query()
                ->where('status', '=', 1)
                ->select('name')
                ->orderBy('serial_number', 'asc')
                ->get();
            $additionalServices = json_decode(Room::find($id)->additional_service);
            $information['additionalServices'] = $additionalServices;
            $taxData = Basic::select('hotel_tax_amount')->first()->hotel_tax_amount;
            $information['tax'] = $taxData;

            return view('vendors.room-booking.booking-form', $information);
        } else {
            Session::flash('warning', __('Your Permission is not granted') . '!');
            return redirect()->route('vendor.room_bookings.all_bookings', ['language' => Auth::guard('vendor')->user()->code]);
        }
    }

    public function makeBooking(AdminRoomBookingRequest $request)
    {
        $currencyInfo =  $this->getCurrencyInfo();
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $service_details = [];

        $onlinePaymentGateway = ['PayPal', 'Stripe', 'Instamojo', 'Paystack', 'Flutterwave', 'Razorpay', 'MercadoPago', 'Mollie', 'Paytm'];

        $gatewayType = in_array($request->payment_method, $onlinePaymentGateway) ? 'online' : 'offline';

        $hourlyPrice  = HourlyRoomPrice::findorfail($request->price);

        $Time = checkInTimeFormate($request->checkInTime);
        $Date = $request->checkInDate;
        $adult = $request->adult;
        $children = $request->children;
        $roomPrice = floatval($hourlyPrice->price);
        $hour = $hourlyPrice->hour;
        $vendor_id = $hourlyPrice->vendor_id;
        $hotel_id = $hourlyPrice->hotel_id;
        $room_id = $hourlyPrice->room_id;

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
        $services = json_decode(Room::findorfail($room_id)->additional_service);
        $additional_services = $request->additional_service;

        if ($request->additional_service) {
            $additional_service = $request->additional_service;

            $room = Room::find($room_id);
            $additionalServices = json_decode($room->additional_service, true);

            foreach ($additional_service as $id) {
                if (isset($additionalServices[$id])) {
                    $price = $additionalServices[$id];

                    $service = AdditionalService::join('additional_service_contents', 'additional_services.id', '=', 'additional_service_contents.additional_service_id')
                        ->where('additional_services.id', $id)
                        ->where('additional_service_contents.language_id', $language->id)
                        ->select('additional_service_contents.title')
                        ->first();
                    if ($service) {
                        $service_details[] = [
                            'price' => $price,
                            'service_name' => $service->title,
                        ];
                    }
                }
            }
        } else {
            $additional_service = null;
        }

        $serviceCharge = 0;
        if ($additional_services) {
            foreach ($services as $key => $servicePrice) {
                if (in_array($key, $additional_services)) {
                    $serviceCharge += $servicePrice;
                }
            }
        }

        $discount = isset($request->discount) ? floatval($request->discount) : 0.00;
        $subTotal = $serviceCharge + $roomPrice - $discount;
        $taxData = Basic::select('hotel_tax_amount')->first();
        $taxAmount = floatval($taxData->hotel_tax_amount);
        $tax = $subTotal * ($taxAmount / 100);
        $grandTotal = $subTotal + $tax;

        if ($vendor_id != 0) {
            $currentPackage = Membership::query()->where([
                ['vendor_id', '=', $vendor_id],
                ['status', '=', 1],
                ['start_date', '<=', Carbon::now()->format('Y-m-d')],
                ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
            ])->first();
        }

        $service_details = json_encode($service_details);

        $bookingInfo = Booking::query()->create([
            'order_number' => uniqid(),
            'user_id' => null,
            'check_in_time' => $checkInTime,
            'check_in_date' =>  $checkInDate,
            'check_out_date' => $checkoutDate,
            'check_out_time' =>  $checkoutTime,
            'check_in_date_time' =>  $check_in_date_time,
            'check_out_date_time' =>  $check_out_date_time,
            'vendor_id' =>  $vendor_id,
            'membership_id' => $vendor_id != 0 ? ($currentPackage ? $currentPackage->id : null) : 0,
            'hotel_id' =>  $hotel_id,
            'room_id' =>  $room_id,
            'preparation_time' =>  $preparation_time,
            'next_booking_time' =>  $next_booking_time,
            'hour' =>  $hour,
            'adult' =>  $adult,
            'children' => $children,
            'booking_name' => $request->customer_name,
            'booking_email' => $request->customer_email,
            'booking_phone' => $request->customer_phone,
            'booking_address' => null,
            'additional_service' => json_encode($additional_services),
            'service_details' => $service_details,
            'roomPrice' => $roomPrice,
            'serviceCharge' => $serviceCharge,
            'discount' => $discount,
            'total' => $subTotal,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'currency_text' => $currencyInfo->base_currency_text,
            'currency_text_position' => $currencyInfo->base_currency_text_position,
            'currency_symbol' => $currencyInfo->base_currency_symbol,
            'currency_symbol_position' => $currencyInfo->base_currency_symbol_position,
            'payment_method' => $request->payment_method,
            'gateway_type' => $gatewayType,
            'payment_status' => $request->payment_status,
            'attachment' => null
        ]);

        if ($request->payment_status == 1) {

            $bookingProcess = new BookingController();

            // generate an invoice in pdf format 
            $invoice = $bookingProcess->generateInvoice($bookingInfo);

            // then, update the invoice field info in database 
            $bookingInfo->update(['invoice' => $invoice]);

            // send a mail to the customer with the invoice
            $bookingProcess->prepareMailForCustomer($bookingInfo);
        }


        Session::flash('success', __('Room has booked successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function getPrice(Request $request, $slug, $id)
    {

        $check_in_time = date('H:i:s', strtotime($request->checkInTime));
        $check_in_date = date('Y-m-d', strtotime($request->checkInDate));
        $check_in_date_time = $check_in_date . ' ' . $check_in_time;


        $room = Room::findOrFail($id);

        $totalRoom = $room->number_of_rooms_of_this_same_type;
        $preparation_time = $room->preparation_time;

        $maxhour = 99;
        $hours = BookingHour::orderBy('hour', 'desc')->get();
        $bookingStatus = false;

        $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

        $holidays  = array_map(
            function ($holiday) {
                return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
            },
            $holiday->toArray()
        );

        foreach ($hours as $hour) {
            $x = false;
            $check_out_time = date('H:i:s', strtotime($check_in_time . " +{$hour->hour} hour"));

            $next_booking_time = date('H:i:s', strtotime($check_out_time . " +$preparation_time min"));


            list($current_hour, $current_minute, $current_second) = explode(':', $check_in_time);
            $total_hours = (int)$current_hour + $hour->hour;
            $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

            $checkoutTimeLimit = '23:59:59';

            if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
                $checkoutDate = date('Y-m-d', strtotime($check_in_date . ' +1 day'));
            } else {
                $checkoutDate = date('Y-m-d', strtotime($check_in_date));
            }


            $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;

            $convertedHolidays = array_map(function ($holiday) {
                return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
            }, $holidays);


            if (!in_array($checkoutDate, $convertedHolidays)) {
                $totalBookingDone = Booking::where('room_id', $id)
                    ->where('payment_status', '!=', 2)
                    ->where(function ($query) use ($check_in_date_time, $check_out_date_time) {
                        $query->where(function ($q) use ($check_in_date_time, $check_out_date_time) {
                            $q->whereBetween('check_in_date_time', [$check_in_date_time, $check_out_date_time])
                                ->orWhereBetween('check_out_date_time', [$check_in_date_time, $check_out_date_time]);
                        })
                            ->orWhere(function ($q) use ($check_in_date_time, $check_out_date_time) {
                                $q->where('check_in_date_time', '<=', $check_in_date_time)
                                    ->where('check_out_date_time', '>=', $check_out_date_time);
                            });
                    })
                    ->count();
            } else {
                $totalBookingDone = 999999;
            }

            if ($totalRoom > $totalBookingDone) {
                $bookingStatus = true;
                $maxhour = $hour->hour;
                break;
            }
        }

        if ($bookingStatus) {
            $information['hourlyPrices'] = HourlyRoomPrice::where('room_id', $id)
                ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                ->where('hourly_room_prices.price', '!=', null)
                ->orderBy('booking_hours.serial_number')
                ->where('hourly_room_prices.hour', '<=', $maxhour)
                ->select('hourly_room_prices.*', 'booking_hours.serial_number')
                ->get();
        } else {
            $information['hourlyPrices'] = [];
        }
        return view('vendors.room-booking.room-price', $information)->render();
    }
    public function editBookingDetails(Request $request, $id)
    {
        $details = Booking::where([['id', $id], ['vendor_id', Auth::guard('vendor')->user()->id]])->firstOrFail();
        $information['details'] = $details;

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();

        $roomInfo = $details->hotelRoom()->first();


        if ($roomInfo) {
            $roomContentInfo = $roomInfo->room_content()->where('language_id', $language->id)->first();
            if ($roomContentInfo) {
                $information['roomTitle'] = $roomContentInfo->title;
            } else {
                $information['roomTitle'] = '--';
            }
        } else {
            $information['roomTitle'] = '--';
        }

        $roomId = $details->room_id;
        $room = Room::find($roomId);

        if (!$room) {
            Session::flash('warning', __('Room has been deleted') . '!');
            return redirect()->route('admin.room_bookings.all_bookings', ['language' => 'en']);
        }
        $information['room'] = $room;

        $check_in_time = date('H:i:s', strtotime($details->check_in_time));
        $check_in_date = Carbon::parse($details->check_in_date)->format('Y-m-d');
        $check_in_date_time = $check_in_date . ' ' . $check_in_time;

        $totalRoom = $room->number_of_rooms_of_this_same_type;
        $preparation_time = $room->preparation_time;

        $maxhour = 99;
        $hours = BookingHour::orderBy('hour', 'desc')->get();
        $bookingStatus = false;

        $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

        $holidays  = array_map(
            function ($holiday) {
                return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
            },
            $holiday->toArray()
        );
        $information['holidayDates']  = $holidays;

        foreach ($hours as $hour) {
            $check_out_time = date('H:i:s', strtotime($check_in_time . " +{$hour->hour} hour"));

            $next_booking_time = date('H:i:s', strtotime($check_out_time . " +$preparation_time min"));


            list($current_hour, $current_minute, $current_second) = explode(':', $check_in_time);
            $total_hours = (int)$current_hour + $hour->hour;
            $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

            $checkoutTimeLimit = '23:59:59';

            if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
                $checkoutDate = date('Y-m-d', strtotime($check_in_date . ' +1 day'));
            } else {
                $checkoutDate = date('Y-m-d', strtotime($check_in_date));
            }

            $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;

            $convertedHolidays = array_map(function ($holiday) {
                return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
            }, $holidays);

            if (!in_array($checkoutDate, $convertedHolidays) && !in_array($check_in_date, $convertedHolidays)) {
                $totalBookingDone = Booking::where('room_id', $roomId)
                    ->where('payment_status', '!=', 2)
                    ->whereNot('bookings.id', '=', $id)
                    ->where(function ($query) use ($check_in_date_time, $check_out_date_time) {
                        $query->where(function ($q) use ($check_in_date_time, $check_out_date_time) {
                            $q->whereBetween('check_in_date_time', [$check_in_date_time, $check_out_date_time])
                                ->orWhereBetween('check_out_date_time', [$check_in_date_time, $check_out_date_time]);
                        })
                            ->orWhere(function ($q) use ($check_in_date_time, $check_out_date_time) {
                                $q->where('check_in_date_time', '<=', $check_in_date_time)
                                    ->where('check_out_date_time', '>=', $check_out_date_time);
                            });
                    })
                    ->count();
            } else {
                $totalBookingDone = 999999;
            }

            if ($totalRoom > $totalBookingDone) {
                $bookingStatus = true;
                $maxhour = $hour->hour;
                break;
            }
        }

        if ($bookingStatus) {
            $information['hourlyPrices'] = HourlyRoomPrice::where('room_id', $room->id)
                ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                ->where('hourly_room_prices.price', '!=', null)
                ->orderBy('booking_hours.serial_number')
                ->where('hourly_room_prices.hour', '<=', $maxhour)
                ->select('hourly_room_prices.*', 'booking_hours.serial_number')
                ->get();
        } else {
            $information['hourlyPrices'] = [];
        }

        $room = Room::where('id', $roomId)->firstOrFail();
        $information['rent'] = $room->rent;

        $information['currencyInfo'] = $this->getCurrencyInfo();

        $additionalServices = json_decode(Room::find($roomId)->additional_service);
        $information['additionalServices'] = $additionalServices;

        $information['onlineGateways'] = OnlineGateway::query()
            ->where('status', '=', 1)
            ->select('name')
            ->get();

        $information['offlineGateways'] = OfflineGateway::query()
            ->where('status', '=', 1)
            ->select('name')
            ->orderBy('serial_number', 'asc')
            ->get();

        $taxData = Basic::select('hotel_tax_amount')->first()->hotel_tax_amount;
        $information['tax'] = $taxData;

        return view('vendors.room-booking.edit-booking', $information);
    }

    public function updateBooking(AdminRoomBookingRequest $request)
    {
        $currencyInfo =  $this->getCurrencyInfo();
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $service_details = [];

        $onlinePaymentGateway = ['PayPal', 'Stripe', 'Instamojo', 'Paystack', 'Flutterwave', 'Razorpay', 'MercadoPago', 'Mollie', 'Paytm'];

        $gatewayType = in_array($request->payment_method, $onlinePaymentGateway) ? 'online' : 'offline';

        $hourlyPrice  = HourlyRoomPrice::findorfail($request->price);

        $Time = checkInTimeFormate($request->checkInTime);
        $Date = $request->checkInDate;
        $hour_id = $hourlyPrice->hour_id;
        $roomPrice = floatval($hourlyPrice->price);
        $hour = BookingHour::findorfail($hour_id)->hour;
        $vendor_id = $hourlyPrice->vendor_id;
        $hotel_id = $hourlyPrice->hotel_id;
        $room_id = $hourlyPrice->room_id;

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

        $services = json_decode(Room::findorfail($room_id)->additional_service);
        $additional_services = $request->additional_service;

        if ($request->additional_service) {
            $additional_service = $request->additional_service;

            $room = Room::find($room_id);
            $additionalServices = json_decode($room->additional_service, true);

            foreach ($additional_service as $id) {
                if (isset($additionalServices[$id])) {
                    $price = $additionalServices[$id];

                    $service = AdditionalService::join('additional_service_contents', 'additional_services.id', '=', 'additional_service_contents.additional_service_id')
                        ->where('additional_services.id', $id)
                        ->where('additional_service_contents.language_id', $language->id)
                        ->select('additional_service_contents.title')
                        ->first();
                    if ($service) {
                        $service_details[] = [
                            'price' => $price,
                            'service_name' => $service->title,
                        ];
                    }
                }
            }
        } else {
            $additional_service = null;
        }


        $serviceCharge = 0;
        if ($additional_services) {
            foreach ($services as $key => $servicePrice) {
                if (in_array($key, $additional_services)) {
                    $serviceCharge += $servicePrice;
                }
            }
        }

        $discount = isset($request->discount) ? floatval($request->discount) : 0.00;
        $subTotal = $serviceCharge + $roomPrice - $discount;

        $taxData = Basic::select('hotel_tax_amount')->first();

        $taxAmount = floatval($taxData->hotel_tax_amount);
        $tax = $subTotal * ($taxAmount / 100);

        $grandTotal = $subTotal + $tax;


        $booking = Booking::where('id', $request->booking_id)->firstorFail();

        $service_details = json_encode($service_details);

        $booking->update([
            'user_id' => null,
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
            'adult' =>  $request->adult,
            'children' => $request->children,
            'booking_name' => $request->customer_name,
            'booking_email' => $request->customer_email,
            'booking_phone' => $request->customer_phone,
            'booking_address' => null,
            'additional_service' => json_encode($additional_services),
            'service_details' => $service_details,
            'roomPrice' => $roomPrice,
            'serviceCharge' => $serviceCharge,
            'discount' => $discount,
            'total' => $subTotal,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'currency_text' => $currencyInfo->base_currency_text,
            'currency_text_position' => $currencyInfo->base_currency_text_position,
            'currency_symbol' => $currencyInfo->base_currency_symbol,
            'currency_symbol_position' => $currencyInfo->base_currency_symbol_position,
            'payment_method' => $request->payment_method,
            'gateway_type' => $gatewayType,
            'payment_status' => $request->payment_status,
            'attachment' => null
        ]);

        Session::flash('success', __('Booking information has been updated') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function getPriceForEdit(Request $request, $slug, $id)
    {

        $check_in_time = date('H:i:s', strtotime($request->checkInTimes));
        $check_in_date = date('Y-m-d', strtotime($request->checkInDates));
        $check_in_date_time = $check_in_date . ' ' . $check_in_time;

        $room = Room::findOrFail($id);

        $totalRoom = $room->number_of_rooms_of_this_same_type;
        $preparation_time = $room->preparation_time;
        $maxhour = 99;
        $hours = BookingHour::orderBy('hour', 'desc')->get();
        $bookingStatus = false;

        $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

        $holidays  = array_map(
            function ($holiday) {
                return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
            },
            $holiday->toArray()
        );

        foreach ($hours as $hour) {
            $x = false;
            $check_out_time = date('H:i:s', strtotime($check_in_time . " +{$hour->hour} hour"));

            $next_booking_time = date('H:i:s', strtotime($check_out_time . " +$preparation_time min"));


            list($current_hour, $current_minute, $current_second) = explode(':', $check_in_time);
            $total_hours = (int)$current_hour + $hour->hour;
            $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

            $checkoutTimeLimit = '23:59:59';

            if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
                $checkoutDate = date('Y-m-d', strtotime($check_in_date . ' +1 day'));
            } else {
                $checkoutDate = date('Y-m-d', strtotime($check_in_date));
            }

            $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;

            $convertedHolidays = array_map(function ($holiday) {
                return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
            }, $holidays);


            if (!in_array($checkoutDate, $convertedHolidays)) {
                $totalBookingDone = Booking::where('room_id', $id)
                    ->where('payment_status', '!=', 2)
                    ->whereNot('bookings.id', '=', $request->pricing_booking_id)
                    ->where(function ($query) use ($check_in_date_time, $check_out_date_time) {
                        $query->where(function ($q) use ($check_in_date_time, $check_out_date_time) {
                            $q->whereBetween('check_in_date_time', [$check_in_date_time, $check_out_date_time])
                                ->orWhereBetween('check_out_date_time', [$check_in_date_time, $check_out_date_time]);
                        })
                            ->orWhere(function ($q) use ($check_in_date_time, $check_out_date_time) {
                                $q->where('check_in_date_time', '<=', $check_in_date_time)
                                    ->where('check_out_date_time', '>=', $check_out_date_time);
                            });
                    })
                    ->count();
            } else {
                $totalBookingDone = 999999;
            }

            if ($totalRoom > $totalBookingDone) {
                $bookingStatus = true;
                $maxhour = $hour->hour;
                break;
            }
        }

        if ($bookingStatus) {
            $information['hourlyPrices'] = HourlyRoomPrice::where('room_id', $id)
                ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                ->where('hourly_room_prices.price', '!=', null)
                ->orderBy('booking_hours.serial_number')
                ->where('hourly_room_prices.hour', '<=', $maxhour)
                ->select('hourly_room_prices.*', 'booking_hours.serial_number')
                ->get();
        } else {
            $information['hourlyPrices'] = [];
        }
        return view('admin.room-booking.room-price', $information)->render();
    }
}
