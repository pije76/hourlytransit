<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Amenitie;
use App\Models\BasicSettings\Basic;
use App\Models\Booking;
use App\Models\BookingHour;
use App\Models\Holiday;
use App\Models\Hotel;
use App\Models\HourlyRoomPrice;
use App\Models\Room;
use App\Models\RoomContent;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\HotelContent;
use App\Models\Vendor;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use App\Models\RoomCategory;
use App\Models\RoomReview;
use App\Models\Visitor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $view = Basic::query()->pluck('room_view')->first();
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();

        $information['bgImg'] = $misc->getBreadcrumb();

        $information['pageHeading'] = $misc->getPageHeading($language);

        $information['language'] = $language;
        $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_rooms', 'meta_description_rooms')->first();

        $information['currencyInfo'] = $this->getCurrencyInfo();

        $title = $address = $category = $ratings = $checkInDates = $amenitie = $hotelId = $country = $state = $city =  $location = null;

        if ($request->filled('checkInDates')) {
            $checkInDates = $request->checkInDates;
        }

        if ($request->filled('checkInTimes')) {
            $checkInTimes = $request->checkInTimes;
            try {
                $checkInTimes = Carbon::parse($checkInTimes)->format('H:i:s');
            } catch (\Exception $e) {
                $checkInTimes = '00:00:00';
            }
        } else {
            $checkInTimes = null;
        }

        if ($request->filled('hour')) {
            $hour = $request->hour;
        } else {
            $hour  = BookingHour::max('hour');
        }

        $rooms = Room::get();

        $roomtimeIds = [];
        if ($checkInDates) {
            foreach ($rooms as $room) {

                $id = $room->id;

                $check_in_time = date('H:i:s', strtotime($checkInTimes));
                $check_in_date = date('Y-m-d', strtotime($checkInDates));
                $check_in_date_time = $check_in_date . ' ' . $check_in_time;

                $totalRoom = Room::findOrFail($id)->number_of_rooms_of_this_same_type;
                $preparation_time = $room->preparation_time;
                $bookingStatus = false;

                $check_out_time = date('H:i:s', strtotime($check_in_time . " +{$hour} hour"));

                $next_booking_time = date('H:i:s', strtotime($check_out_time . " +$preparation_time min"));

                list($current_hour, $current_minute, $current_second) = explode(':', $check_in_time);
                $total_hours = (int)$current_hour + $hour;
                $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

                $checkoutTimeLimit = '23:59:59';

                if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
                    $checkoutDate = date('Y-m-d', strtotime($check_in_date . ' +1 day'));
                } else {
                    $checkoutDate = date('Y-m-d', strtotime($check_in_date));
                }

                $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;


                $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

                $holidays  = array_map(
                    function ($holiday) {
                        return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
                    },
                    $holiday->toArray()
                );

                $convertedHolidays = array_map(
                    function ($holiday) {
                        return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
                    },
                    $holidays
                );

                if (!in_array($checkoutDate, $convertedHolidays) && !in_array($check_in_date, $convertedHolidays)) {

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
                }

                if (!$bookingStatus) {
                    if (!in_array($id, $roomtimeIds)) {
                        array_push($roomtimeIds, $id);
                    }
                }
            }
        }

        $roomIds = [];
        if ($request->filled('title')) {
            $title = $request->title;

            $room_contents = RoomContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('room_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $roomIds)) {
                    array_push($roomIds, $room_content);
                }
            }
        }

        $countryIds = [];
        if ($request->filled('country')) {
            $country = $request->country;
            $hotel_contents = HotelContent::where('language_id', $language->id)
                ->where('country_id', $country)
                ->get()
                ->pluck('hotel_id');
            foreach ($hotel_contents as $hotel_content) {
                if (!in_array($hotel_content, $countryIds)) {
                    array_push($countryIds, $hotel_content);
                }
            }
        }
        $stateIds = [];
        if ($request->filled('state')) {
            $state = $request->state;
            $hotel_contents = HotelContent::where('language_id', $language->id)
                ->where('state_id', $state)
                ->get()
                ->pluck('hotel_id');
            foreach ($hotel_contents as $hotel_content) {
                if (!in_array($hotel_content, $stateIds)) {
                    array_push($stateIds, $hotel_content);
                }
            }
        }

        $cityIds = [];
        if ($request->filled('city')) {
            $city = $request->city;
            $hotel_contents = HotelContent::where('language_id', $language->id)
                ->where('city_id', $city)
                ->get()
                ->pluck('hotel_id');
            foreach ($hotel_contents as $hotel_content) {
                if (!in_array($hotel_content, $cityIds)) {
                    array_push($cityIds, $hotel_content);
                }
            }
        }

        if ($request->filled('hotelId')) {
            $hotelId = $request->hotelId;
        }

        $category_roomIds = [];
        if ($request->filled('category')) {
            $category = $request->category;

            $category_content = RoomCategory::where([['language_id', $language->id], ['slug', $category]])->first();

            if (!empty($category_content)) {
                $category_id = $category_content->id;
                $contents = RoomContent::where('language_id', $language->id)
                    ->where('room_category', $category_id)
                    ->get()
                    ->pluck('room_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $category_roomIds)) {
                        array_push($category_roomIds, $content);
                    }
                }
            }
        }


        $ratingIds = [];
        if ($request->filled('ratings')) {
            $ratings = $request->ratings;
            $contents = Room::where('average_rating', '>=', $ratings)
                ->get()
                ->pluck('id');
            foreach ($contents as $content) {
                if (!in_array($content, $ratingIds)) {
                    array_push($ratingIds, $content);
                }
            }
        }

        $amenitieIds = [];
        if ($request->filled('amenitie')) {
            $amenitie = $request->amenitie;
            $array = explode(',', $amenitie);

            $contents = RoomContent::where('language_id', $language->id)
                ->get(['room_id', 'amenities']);

            foreach ($contents as $content) {
                $amenities = (json_decode($content->amenities));
                $roomId = $content->room_id;
                $diff1 = array_diff($array, $amenities);
                $diff2 = array_diff($array, $amenities);

                if (empty($diff1) && empty($diff2)) {

                    array_push($amenitieIds, $roomId);
                }
            }
        }

        //search by location
        $locationIds = [];
        $addressIds = [];
        $bs = Basic::select('google_map_api_key_status', 'radius')->first();
        $radius = $bs->google_map_api_key_status == 1 ? $bs->radius : 5000;

        if ($request->filled('location')) {

            if ($bs->google_map_api_key_status == 1) {
                $location = $request->location;
                $hotelIds = HotelContent::where('language_id', $language->id)
                    ->where('address', 'like', '%' . $location . '%')
                    ->distinct()
                    ->pluck('hotel_id')
                    ->toArray();

                $serviceLog = Hotel::whereIn('id', $hotelIds)->select('latitude', 'longitude')->first();
                $locationIds = $serviceLog;
            } else {
                $address = $request->location;
                $contents = HotelContent::Where('language_id', $language->id)
                    ->where('address', 'like', '%' . $address . '%')
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $addressIds)) {
                        array_push($addressIds, $content);
                    }
                }
            }
        }

        if ($request->filled('sort')) {
            if ($request['sort'] == 'new') {
                $order_by_column = 'rooms.id';
                $order = 'desc';
            } elseif ($request['sort'] == 'old') {
                $order_by_column = 'rooms.id';
                $order = 'asc';
            } else {
                $order_by_column = 'rooms.id';
                $order = 'desc';
            }
        } else {
            $order_by_column = 'rooms.id';
            $order = 'desc';
        }


        $featured_contents = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
            ->Join('room_features', 'rooms.id', '=', 'room_features.room_id')
            ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
            ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
            ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
            ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
            ->where('hotel_contents.language_id', $language->id)
            ->where('room_categories.status', 1)
            ->where('hotel_categories.status', 1)
            ->where('room_contents.language_id', $language->id)
            ->where('room_features.order_status', '=', 'apporved')
            ->where('rooms.status',  '=',    '1')
            ->where('hotels.status',  '=',    '1')
            ->whereDate('room_features.end_date', '>=', Carbon::now()->format('Y-m-d'))
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
                    ->where(function ($query) {
                        $query->where([
                            ['memberships.status', '=', 1],
                            ['memberships.start_date', '<=', now()->format('Y-m-d')],
                            ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
                    ->where(function ($query) {
                        $query->where([
                            ['vendors.status', '=', 1],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when($category, function ($query) use ($category_roomIds) {
                return $query->whereIn('rooms.id', $category_roomIds);
            })

            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('rooms.id', $roomIds);
            })
            ->when($hotelId, function ($query) use ($hotelId) {
                return $query->where('rooms.hotel_id', $hotelId);
            })

            ->when($ratings, function ($query) use ($ratingIds) {
                return $query->whereIn('rooms.id', $ratingIds);
            })
            ->when($amenitie, function ($query) use ($amenitieIds) {
                return $query->whereIn('rooms.id', $amenitieIds);
            })
            ->when($checkInDates, function ($query) use ($roomtimeIds) {
                return $query->whereNotIn('rooms.id', $roomtimeIds);
            })
            ->when($country, function ($query) use ($countryIds) {
                return $query->whereIn('rooms.hotel_id', $countryIds);
            })
            ->when($state, function ($query) use ($stateIds) {
                return $query->whereIn('rooms.hotel_id', $stateIds);
            })
            ->when($city, function ($query) use ($cityIds) {
                return $query->whereIn('rooms.hotel_id', $cityIds);
            })

            ->when($address, function ($query) use ($addressIds) {
                return $query->whereIn('rooms.hotel_id', $addressIds);
            })

            ->when($location, function ($query) use ($locationIds, $radius) {
                if (is_null($locationIds)) {
                    return $query->whereRaw('1=0');
                }
                return $query->whereRaw("
            (6371000 * acos(
            cos(radians(?)) *
            cos(radians(hotels.latitude)) *
            cos(radians(hotels.longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(hotels.latitude))
            )) < ?
            ", [$locationIds->latitude, $locationIds->longitude, $locationIds->latitude, $radius]);
            })
            ->select(
                'rooms.*',
                'room_contents.title',
                'room_contents.slug',
                'room_contents.amenities',
                'hotels.id as hotelId',
                'hotels.stars as stars',
                'hotels.latitude as latitude',
                'hotels.longitude as longitude',
                'hotels.logo as hotelImage',
                'hotel_contents.title as hotelName',
                'hotel_contents.slug as hotelSlug',
                'hotel_contents.city_id',
                'hotel_contents.state_id',
                'hotel_contents.country_id'
            )
            ->orderBy($order_by_column, $order)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $totalFeatured_content = Count($featured_contents);

        $featured_contentsIds = [];
        if ($featured_contents) {

            foreach ($featured_contents as $content) {
                if (!in_array($content->id, $featured_contentsIds)) {
                    array_push($featured_contentsIds, $content->id);
                }
            }
        }

        $room_contents = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
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
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
                    ->where(function ($query) {
                        $query->where([
                            ['memberships.status', '=', 1],
                            ['memberships.start_date', '<=', now()->format('Y-m-d')],
                            ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
                    ->where(function ($query) {
                        $query->where([
                            ['vendors.status', '=', 1],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('rooms.id', $roomIds);
            })

            ->when($hotelId, function ($query) use ($hotelId) {
                return $query->where('rooms.hotel_id', $hotelId);
            })
            ->when($category, function ($query) use ($category_roomIds) {
                return $query->whereIn('rooms.id', $category_roomIds);
            })
            ->when($ratings, function ($query) use ($ratingIds) {
                return $query->whereIn('rooms.id', $ratingIds);
            })
            ->when($amenitie, function ($query) use ($amenitieIds) {
                return $query->whereIn('rooms.id', $amenitieIds);
            })
            ->when($checkInDates, function ($query) use ($roomtimeIds) {
                return $query->whereNotIn('rooms.id', $roomtimeIds);
            })
            ->when($country, function ($query) use ($countryIds) {
                return $query->whereIn('rooms.hotel_id', $countryIds);
            })
            ->when($state, function ($query) use ($stateIds) {
                return $query->whereIn('rooms.hotel_id', $stateIds);
            })
            ->when($city, function ($query) use ($cityIds) {
                return $query->whereIn('rooms.hotel_id', $cityIds);
            })
            ->when($featured_contents, function ($query) use ($featured_contentsIds) {
                return $query->whereNotIn('rooms.id', $featured_contentsIds);
            })
            ->when($address, function ($query) use ($addressIds) {
                return $query->whereIn('rooms.hotel_id', $addressIds);
            })

            ->when($location, function ($query) use ($locationIds, $radius) {
                if (is_null($locationIds)) {
                    return $query->whereRaw('1=0');
                }
                return $query->whereRaw("
            (6371000 * acos(
            cos(radians(?)) *
            cos(radians(hotels.latitude)) *
            cos(radians(hotels.longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(hotels.latitude))
            )) < ?
            ", [$locationIds->latitude, $locationIds->longitude, $locationIds->latitude, $radius]);
            })
            ->select(
                'rooms.*',
                'room_contents.title',
                'room_contents.slug',
                'room_contents.amenities',
                'hotels.id as hotelId',
                'hotels.stars as stars',
                'hotels.latitude as latitude',
                'hotels.longitude as longitude',
                'hotels.logo as hotelImage',
                'hotel_contents.title as hotelName',
                'hotel_contents.slug as hotelSlug',
                'hotel_contents.city_id',
                'hotel_contents.state_id',
                'hotel_contents.country_id'
            )
            ->orderBy($order_by_column, $order)
            ->get();


        if ($totalFeatured_content == 3) {
            $perPage = 9;
        } elseif ($totalFeatured_content == 2) {
            $perPage = 10;
        } elseif ($totalFeatured_content == 1) {
            $perPage = 11;
        } else {
            $perPage = 12;
        }

        $page = 1;

        $offset = ($page - 1) * $perPage;

        $currentPageData = $room_contents->slice($offset, $perPage);

        $information['categories'] = RoomCategory::where('language_id', $language->id)->where('status', 1)
            ->orderBy('serial_number', 'asc')->get();

        $information['vendors'] = Vendor::join('memberships', 'vendors.id', '=', 'memberships.vendor_id')
            ->where([
                ['memberships.status', '=', 1],
                ['memberships.start_date', '<=', Carbon::now()->format('Y-m-d')],
                ['memberships.expire_date', '>=', Carbon::now()->format('Y-m-d')]
            ])
            ->get();

        $information['countries'] = Country::where('language_id', $language->id)
            ->orderBy('id', 'asc')->get();

        $information['states'] = State::where('language_id', $language->id)
            ->orderBy('id', 'asc')->get();

        $information['cities'] = City::where('language_id', $language->id)
            ->orderBy('id', 'asc')->get();

        $information['room_contents'] = $room_contents;
        $information['featured_contents'] = $featured_contents;
        $information['currentPageData'] = $currentPageData;
        $information['perPage'] = $perPage;
        $information['bookingHours'] =  BookingHour::orderBy('hour', 'asc')->get();
        $information['amenities'] =  Amenitie::Where('language_id', $language->id)->get();
        $information['hotels'] =  Hotel::Join('hotel_contents', 'hotels.id', '=', 'hotel_contents.hotel_id')
            ->where('hotel_contents.language_id', $language->id)
            ->select(
                'hotels.id',
                'hotel_contents.title',
            )->orderBy('hotels.id', 'desc')
            ->get();

        $information['adultNumber'] = Room::where('status', 1)->max('adult');
        $information['childrenNumber'] = Room::where('status', 1)->max('children');

        if ($view == 0) {
            return view('frontend.room.room-map', $information);
        } else {
            return view('frontend.room.room-gird', $information);
        }
    }

    public function getAddress(Request $request)
    {
        if ($request->country_id) {
            $country = Country::Where('id', $request->country_id)->first()
                ->name;
        }
        if ($request->state_id) {
            $state = State::Where('id', $request->state_id)->first()
                ->name;
        }
        if ($request->city_id) {
            $city = City::Where('id', $request->city_id)->first()
                ->name;
        }
        $address = '';
        if ($request->city_id) {
            if ($city) {
                $address .= $city;
            }
        }
        if ($request->state_id) {
            if ($state) {
                $address .= ($address ? ', ' : '') . $state;
            }
        }
        if ($request->country_id) {
            if ($country) {
                $address .= ($address ? ', ' : '') . $country;
            }
        }

        return $address;
    }

    public function search_room(Request $request)
    {
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $information['language'] = $language;

        $information['currencyInfo'] = $this->getCurrencyInfo();
        $title = $address = $category = $stars = $checkInDates = $adult = $children = $ratings = $amenitie = $hotelId = $country = $state = $city =  $location = null;

        if ($request->filled('checkInDates')) {
            $checkInDates = $request->checkInDates;
        }

        if ($request->filled('checkInTimes')) {
            $checkInTimes = $request->checkInTimes;
            try {
                $checkInTimes = Carbon::parse($checkInTimes)->format('H:i:s');
            } catch (\Exception $e) {
                $checkInTimes = '00:00:00';
            }
        } else {
            $checkInTimes = '00:00:00';
        }

        $hourIds = [];
        if ($request->filled('hour')) {
            $hour = $request->hour;

            $hourhave = 'yes';

            $prices = HourlyRoomPrice::where([
                ['hour', $hour]
            ])
                ->whereNotNull('price')
                ->get()
                ->pluck('room_id');

            foreach ($prices as $price) {
                if (!in_array($price, $hourIds)) {
                    array_push($hourIds, $price);
                }
            }
        } else {
            $hour  = BookingHour::max('hour');
            $hourhave = null;
        }

        $roomtimeIds = [];
        if ($checkInDates) {
            $rooms = Room::get();
            foreach ($rooms as $room) {
                $id = $room->id;

                $check_in_time = date('H:i:s', strtotime($checkInTimes));
                $check_in_date = date('Y-m-d', strtotime($checkInDates));
                $check_in_date_time = $check_in_date . ' ' . $check_in_time;

                $totalRoom = Room::findOrFail($id)->number_of_rooms_of_this_same_type;
                $preparation_time = $room->preparation_time;
                $bookingStatus = false;

                $check_out_time = date('H:i:s', strtotime($check_in_time . " +{$hour} hour"));

                $next_booking_time = date('H:i:s', strtotime($check_out_time . " +$preparation_time min"));

                list($current_hour, $current_minute, $current_second) = explode(':', $check_in_time);
                $total_hours = (int)$current_hour + $hour;
                $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

                $checkoutTimeLimit = '23:59:59';

                if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
                    $checkoutDate = date('Y-m-d', strtotime($check_in_date . ' +1 day'));
                } else {
                    $checkoutDate = date('Y-m-d', strtotime($check_in_date));
                }

                $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

                $holidays  = array_map(
                    function ($holiday) {
                        return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
                    },
                    $holiday->toArray()
                );
                $convertedHolidays = array_map(function ($holiday) {
                    return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
                }, $holidays);

                $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;
                if (!in_array($checkoutDate, $convertedHolidays) && !in_array($check_in_date, $convertedHolidays)) {

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
                }

                if (!$bookingStatus) {
                    if (!in_array($id, $roomtimeIds)) {
                        array_push($roomtimeIds, $id);
                    }
                }
            }
        }

        $roomIds = [];
        if ($request->filled('title')) {
            $title = $request->title;

            $room_contents = RoomContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('room_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $roomIds)) {
                    array_push($roomIds, $room_content);
                }
            }
        }

        $countryIds = [];
        if ($request->filled('country')) {
            $country = $request->country;
            $hotel_contents = HotelContent::where('language_id', $language->id)
                ->where('country_id', $country)
                ->get()
                ->pluck('hotel_id');
            foreach ($hotel_contents as $hotel_content) {
                if (!in_array($hotel_content, $countryIds)) {
                    array_push($countryIds, $hotel_content);
                }
            }
        }
        $stateIds = [];
        if ($request->filled('state')) {
            $state = $request->state;
            $hotel_contents = HotelContent::where('language_id', $language->id)
                ->where('state_id', $state)
                ->get()
                ->pluck('hotel_id');
            foreach ($hotel_contents as $hotel_content) {
                if (!in_array($hotel_content, $stateIds)) {
                    array_push($stateIds, $hotel_content);
                }
            }
        }

        $cityIds = [];
        if ($request->filled('city')) {
            $city = $request->city;
            $hotel_contents = HotelContent::where('language_id', $language->id)
                ->where('city_id', $city)
                ->get()
                ->pluck('hotel_id');
            foreach ($hotel_contents as $hotel_content) {
                if (!in_array($hotel_content, $cityIds)) {
                    array_push($cityIds, $hotel_content);
                }
            }
        }

        if ($request->filled('hotelId')) {
            $hotelId = $request->hotelId;
        }

        $category_roomIds = [];
        if ($request->filled('category')) {
            $category = $request->category;

            $category_content = RoomCategory::where([['language_id', $language->id], ['slug', $category]])->first();

            if (!empty($category_content)) {
                $category_id = $category_content->id;
                $contents = RoomContent::where('language_id', $language->id)
                    ->where('room_category', $category_id)
                    ->get()
                    ->pluck('room_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $category_roomIds)) {
                        array_push($category_roomIds, $content);
                    }
                }
            }
        }

        $ratingIds = [];
        if ($request->filled('ratings')) {
            $ratings = $request->ratings;
            $contents = Room::where('average_rating', '>=', $ratings)
                ->get()
                ->pluck('id');
            foreach ($contents as $content) {
                if (!in_array($content, $ratingIds)) {
                    array_push($ratingIds, $content);
                }
            }
        }

        $starsIds = [];
        if ($request->filled('stars')) {
            $stars = $request->stars;
            $contents = Hotel::where('stars', $stars)
                ->get()
                ->pluck('id');
            foreach ($contents as $content) {
                if (!in_array($content, $starsIds)) {
                    array_push($starsIds, $content);
                }
            }
        }

        $adultIds = [];
        if ($request->filled('adult')) {
            $adult = $request->adult;
            $contents = Room::where('adult', '>=', $adult)
                ->get()
                ->pluck('id');

            foreach ($contents as $content) {
                if (!in_array($content, $adultIds)) {
                    array_push($adultIds, $content);
                }
            }
        }
        $childrenIds = [];
        if ($request->filled('children')) {
            $children = $request->children;
            $contents = Room::where('children', '>=',  $children)
                ->get()
                ->pluck('id');
            foreach ($contents as $content) {
                if (!in_array($content, $childrenIds)) {
                    array_push($childrenIds, $content);
                }
            }
        }

        $amenitieIds = [];
        if ($request->filled('amenitie')) {
            $amenitie = $request->amenitie;
            $array = explode(',', $amenitie);

            $contents = RoomContent::where('language_id', $language->id)
                ->get(['room_id', 'amenities']);

            foreach ($contents as $content) {
                $amenities = (json_decode($content->amenities));
                $roomId = $content->room_id;
                $diff1 = array_diff($array, $amenities);
                $diff2 = array_diff($array, $amenities);

                if (empty($diff1) && empty($diff2)) {

                    array_push($amenitieIds, $roomId);
                }
            }
        }

        //search by location
        $locationIds = [];
        $addressIds = [];
        $bs = Basic::select('google_map_api_key_status', 'radius')->first();
        $radius = $bs->google_map_api_key_status == 1 ? $bs->radius : 5000;

        if ($request->filled('location_val')) {

            if ($bs->google_map_api_key_status == 1) {
                $location = $request->location_val;
                $hotelIds = HotelContent::where('language_id', $language->id)
                    ->where('address', 'like', '%' . $location . '%')
                    ->distinct()
                    ->pluck('hotel_id')
                    ->toArray();

                $serviceLog = Hotel::whereIn('id', $hotelIds)->select('latitude', 'longitude')->first();
                $locationIds = $serviceLog;
            } else {
                $address = $request->location_val;

                $contents = HotelContent::Where('language_id', $language->id)
                    ->where('address', 'like', '%' . $address . '%')
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $addressIds)) {
                        array_push($addressIds, $content);
                    }
                }
            }
        }

        if ($request->filled('sort')) {
            if ($request['sort'] == 'new') {
                $order_by_column = 'rooms.id';
                $order = 'desc';
            } elseif ($request['sort'] == 'old') {
                $order_by_column = 'rooms.id';
                $order = 'asc';
            } elseif ($request['sort'] == 'starhigh') {
                $order_by_column = 'hotels.stars';
                $order = 'desc';
            } elseif ($request['sort'] == 'starlow') {
                $order_by_column = 'hotels.stars';
                $order = 'asc';
            } elseif ($request['sort'] == 'reviewshigh') {
                $order_by_column = 'rooms.average_rating';
                $order = 'desc';
            } elseif ($request['sort'] == 'reviewslow') {
                $order_by_column = 'rooms.average_rating';
                $order = 'asc';
            } else {
                $order_by_column = 'rooms.id';
                $order = 'desc';
            }
        } else {
            $order_by_column = 'rooms.id';
            $order = 'desc';
        }

        $featured_contents = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
            ->Join('room_features', 'rooms.id', '=', 'room_features.room_id')
            ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
            ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
            ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
            ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
            ->where('hotel_contents.language_id', $language->id)
            ->where('room_categories.status', 1)
            ->where('hotel_categories.status', 1)
            ->where('room_contents.language_id', $language->id)
            ->where('room_features.order_status', '=', 'apporved')
            ->where('rooms.status',  '=', '1')
            ->where('hotels.status',  '=', '1')
            ->whereDate('room_features.end_date', '>=', Carbon::now()->format('Y-m-d'))
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
                    ->where(function ($query) {
                        $query->where([
                            ['memberships.status', '=', 1],
                            ['memberships.start_date', '<=', now()->format('Y-m-d')],
                            ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
                    ->where(function ($query) {
                        $query->where([
                            ['vendors.status', '=', 1],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when($category, function ($query) use ($category_roomIds) {
                return $query->whereIn('rooms.id', $category_roomIds);
            })

            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('rooms.id', $roomIds);
            })
            ->when($hourhave, function ($query) use ($hourIds) {
                return $query->whereIn('rooms.id', $hourIds);
            })
            ->when($hotelId, function ($query) use ($hotelId) {
                return $query->where('rooms.hotel_id', $hotelId);
            })
            ->when($ratings, function ($query) use ($ratingIds) {
                return $query->whereIn('rooms.id', $ratingIds);
            })
            ->when($adult, function ($query) use ($adultIds) {
                return $query->whereIn('rooms.id', $adultIds);
            })
            ->when($children, function ($query) use ($childrenIds) {
                return $query->whereIn('rooms.id', $childrenIds);
            })
            ->when($stars, function ($query) use ($starsIds) {
                return $query->whereIn('rooms.hotel_id', $starsIds);
            })
            ->when($amenitie, function ($query) use ($amenitieIds) {
                return $query->whereIn('rooms.id', $amenitieIds);
            })
            ->when($checkInDates, function ($query) use ($roomtimeIds) {
                return $query->whereNotIn('rooms.id', $roomtimeIds);
            })
            ->when($country, function ($query) use ($countryIds) {
                return $query->whereIn('rooms.hotel_id', $countryIds);
            })
            ->when($state, function ($query) use ($stateIds) {
                return $query->whereIn('rooms.hotel_id', $stateIds);
            })
            ->when($city, function ($query) use ($cityIds) {
                return $query->whereIn('rooms.hotel_id', $cityIds);
            })
            ->when($address, function ($query) use ($addressIds) {
                return $query->whereIn('rooms.hotel_id', $addressIds);
            })
            ->when($location, function ($query) use ($locationIds, $radius) {
                if (is_null($locationIds)) {
                    return $query->whereRaw('1=0');
                }
                return $query->whereRaw("
            (6371000 * acos(
            cos(radians(?)) *
            cos(radians(hotels.latitude)) *
            cos(radians(hotels.longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(hotels.latitude))
            )) < ?
            ", [$locationIds->latitude, $locationIds->longitude, $locationIds->latitude, $radius]);
            })
            ->select(
                'rooms.*',
                'room_contents.title',
                'room_contents.slug',
                'room_contents.amenities',
                'hotels.id as hotelId',
                'hotels.stars as stars',
                'hotels.latitude as latitude',
                'hotels.longitude as longitude',
                'hotels.logo as hotelImage',
                'hotel_contents.title as hotelName',
                'hotel_contents.slug as hotelSlug',
                'hotel_contents.city_id',
                'hotel_contents.state_id',
                'hotel_contents.country_id'
            )

            ->orderBy($order_by_column, $order)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $totalFeatured_content = Count($featured_contents);

        $featured_contentsIds = [];
        if ($featured_contents) {

            foreach ($featured_contents as $content) {
                if (!in_array($content->id, $featured_contentsIds)) {
                    array_push($featured_contentsIds, $content->id);
                }
            }
        }

        $room_contents = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
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
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
                    ->where(function ($query) {
                        $query->where([
                            ['memberships.status', '=', 1],
                            ['memberships.start_date', '<=', now()->format('Y-m-d')],
                            ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when('rooms.vendor_id' != "0", function ($query) {
                return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
                    ->where(function ($query) {
                        $query->where([
                            ['vendors.status', '=', 1],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('rooms.id', $roomIds);
            })
            ->when($hourhave, function ($query) use ($hourIds) {
                return $query->whereIn('rooms.id', $hourIds);
            })

            ->when($hotelId, function ($query) use ($hotelId) {
                return $query->where('rooms.hotel_id', $hotelId);
            })
            ->when($category, function ($query) use ($category_roomIds) {
                return $query->whereIn('rooms.id', $category_roomIds);
            })

            ->when($ratings, function ($query) use ($ratingIds) {
                return $query->whereIn('rooms.id', $ratingIds);
            })
            ->when($adult, function ($query) use ($adultIds) {
                return $query->whereIn('rooms.id', $adultIds);
            })
            ->when($children, function ($query) use ($childrenIds) {
                return $query->whereIn('rooms.id', $childrenIds);
            })
            ->when($stars, function ($query) use ($starsIds) {
                return $query->whereIn('rooms.hotel_id', $starsIds);
            })
            ->when($amenitie, function ($query) use ($amenitieIds) {
                return $query->whereIn('rooms.id', $amenitieIds);
            })
            ->when($checkInDates, function ($query) use ($roomtimeIds) {
                return $query->whereNotIn('rooms.id', $roomtimeIds);
            })
            ->when($country, function ($query) use ($countryIds) {
                return $query->whereIn('rooms.hotel_id', $countryIds);
            })
            ->when($state, function ($query) use ($stateIds) {
                return $query->whereIn('rooms.hotel_id', $stateIds);
            })
            ->when($city, function ($query) use ($cityIds) {
                return $query->whereIn('rooms.hotel_id', $cityIds);
            })
            ->when($featured_contents, function ($query) use ($featured_contentsIds) {
                return $query->whereNotIn('rooms.id', $featured_contentsIds);
            })
            ->when($address, function ($query) use ($addressIds) {
                return $query->whereIn('rooms.hotel_id', $addressIds);
            })
            ->when($location, function ($query) use ($locationIds, $radius) {
                if (is_null($locationIds)) {
                    return $query->whereRaw('1=0');
                }
                return $query->whereRaw("
            (6371000 * acos(
            cos(radians(?)) *
            cos(radians(hotels.latitude)) *
            cos(radians(hotels.longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(hotels.latitude))
            )) < ?
            ", [$locationIds->latitude, $locationIds->longitude, $locationIds->latitude, $radius]);
            })
            ->select(
                'rooms.*',
                'room_contents.title',
                'room_contents.slug',
                'room_contents.amenities',
                'hotels.id as hotelId',
                'hotels.stars as stars',
                'hotels.latitude as latitude',
                'hotels.longitude as longitude',
                'hotels.logo as hotelImage',
                'hotel_contents.title as hotelName',
                'hotel_contents.slug as hotelSlug',
                'hotel_contents.city_id',
                'hotel_contents.state_id',
                'hotel_contents.country_id'
            )
            ->orderBy($order_by_column, $order)
            ->get();


        if ($totalFeatured_content == 3) {
            $perPage = 9;
        } elseif ($totalFeatured_content == 2) {
            $perPage = 10;
        } elseif ($totalFeatured_content == 1) {
            $perPage = 11;
        } else {
            $perPage = 12;
        }

        $page = $request->query('page');

        $offset = ($page - 1) * $perPage;

        // Get the subset of data for the current page
        $currentPageData = $room_contents->slice($offset, $perPage);

        $information['room_contents'] = $room_contents;
        $information['featured_contents'] = $featured_contents;
        $information['currentPageData'] = $currentPageData;

        $information['perPage'] = $perPage;
        $information['adultNumber'] = Room::where('status', 1)->max('adult');
        $information['childrenNumber'] = Room::where('status', 1)->max('children');

        return view('frontend.room.search-room', $information)->render();
    }

    public function details($slug, $id)
    {
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $information['bgImg'] = $misc->getBreadcrumb();
        $information['pageHeading'] = $misc->getPageHeading($language);

        $vendorId = Room::where('id', $id)->pluck('vendor_id')->first();

        $language = $misc->getLanguage();

        $roomContent = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
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

            ->when($vendorId && $vendorId != 0, function ($query) {
                $query->join('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
                    ->where([
                        ['memberships.status', '=', 1],
                        ['memberships.start_date', '<=', now()->format('Y-m-d')],
                        ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                    ]);
            })
            ->when($vendorId && $vendorId != 0, function ($query) {
                return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
                    ->where(function ($query) {
                        $query->where([
                            ['vendors.status', '=', 1],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->where([
                ['rooms.status', '=', '1']
            ])

            ->select(
                'rooms.*',
                'room_contents.title',
                'room_contents.slug',
                'room_contents.amenities',
                'room_contents.room_category',
                'room_contents.meta_keyword',
                'room_contents.meta_description',
                'hotel_contents.address as address',
                'hotel_contents.title as hoteltitle',
                'hotel_contents.slug as hotelSlug',
                'room_contents.description',
                'hotels.id as hotelId',
                'hotels.logo as hotellogo',
                'hotels.stars as stars',
                'hotels.latitude as latitude',
                'hotels.longitude as longitude',
                'room_categories.name as categoryName',
                'room_categories.slug as categorySlug',
            )
            ->where('rooms.id', $id)
            ->firstOrFail();

        if ($vendorId == 0) {
            $information['vendor'] = Admin::first();
            $information['userName'] = 'admin';
        } else {
            $information['vendor'] = Vendor::Where('id', $vendorId)->first();
            $information['userName'] = $information['vendor']->username;
        }

        $information['bgImg'] = $misc->getBreadcrumb();
        $information['roomContent'] = $roomContent;
        $information['roomImages'] = RoomImage::Where('room_id', $id)->get();

        $room_content = RoomContent::where('language_id', $language->id)->where('room_id', $id)->first();
        if (is_null($room_content)) {
            Session::flash('error', 'No Room information found for ' . $language->name . ' language');
            return redirect()->route('index');
        }
        $information['language'] = $language;

        $holiday = Holiday::Where('hotel_id', $roomContent->hotelId)->get();

        $holidays  = array_map(
            function ($holiday) {
                return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
            },
            $holiday->toArray()
        );

        $information['holidayDates']  = $holidays;

        $convertedHolidays = array_map(
            function ($holiday) {
                return \DateTime::createFromFormat('m/d/Y', $holiday)->format('Y-m-d');
            },
            $holidays
        );

        $latestCheckoutDate = Booking::where('room_id', $id)->max('check_out_date');

        if ($latestCheckoutDate) {
            $checkinDate = Carbon::parse($latestCheckoutDate)->addDay()->format('Y-m-d');
        } else {
            $checkinDate = date('Y-m-d');
        }

        while (in_array($checkinDate, $convertedHolidays)) {
            $checkinDate = Carbon::parse($checkinDate)->addDay()->format('Y-m-d');
        }

        $information['checkinDate']  = $checkinDate;

        $information['hourlyPrices'] = HourlyRoomPrice::where('room_id', $id)
            ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
            ->where('hourly_room_prices.price', '!=', null)
            ->orderBy('booking_hours.serial_number')
            ->select('hourly_room_prices.*', 'booking_hours.serial_number')
            ->get();

        $reviews = RoomReview::query()->where('room_id', '=', $id)->orderByDesc('id')->get();

        $reviews->map(function ($review) {
            $review['user'] = $review->userInfo()->first();
        });

        $information['reviews'] = $reviews;
        $numOfReview = count($reviews);
        $information['numOfReview'] = $numOfReview;

        $rooms = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
            ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
            ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
            ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
            ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
            ->where('hotel_contents.language_id', $language->id)
            ->where('room_categories.status', 1)
            ->where('hotel_categories.status', 1)
            ->where('room_contents.language_id', $language->id)
            ->where('rooms.status', '=', '1')
            ->where('hotels.status', '=', '1')
            ->whereNot('rooms.id', '=', $id)

            ->when($vendorId && $vendorId != 0, function ($query) {
                $query->join('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
                    ->where([
                        ['memberships.status', '=', 1],
                        ['memberships.start_date', '<=', now()->format('Y-m-d')],
                        ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                    ]);
            })
            ->when($vendorId && $vendorId != 0, function ($query) {
                return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
                    ->where(function ($query) {
                        $query->where([
                            ['vendors.status', '=', 1],
                        ])->orWhere('rooms.vendor_id', '=', 0);
                    });
            })
            ->where([
                ['rooms.status', '=', '1'],
                ['room_contents.room_category', '=', $roomContent->room_category]
            ])

            ->select(
                'rooms.*',
                'room_contents.title',
                'room_contents.slug',
                'room_contents.amenities',
                'room_contents.meta_keyword',
                'room_contents.meta_description',
                'hotel_contents.address',
                'room_contents.description',
                'hotel_contents.title as hotelName',
                'hotel_contents.slug as hotelSlug',
                'hotels.id as hotelId',
                'hotels.stars as stars',
                'hotels.logo as hotelImage',
                'hotels.latitude as latitude',
                'hotels.longitude as longitude',
                'hotel_contents.city_id',
                'hotel_contents.state_id',
                'hotel_contents.country_id',
            )
            ->limit(4)
            ->get();
        $information['rooms'] = $rooms;
        return view('frontend.room.room-details', $information);
    }

    public function getPrice(Request $request, $slug, $id)
    {

        $check_in_time = date('H:i:s', strtotime($request->checkInTime));
        $check_in_date = date('Y-m-d', strtotime($request->checkInDates));
        $check_in_date_time = $check_in_date . ' ' . $check_in_time;

        $room = Room::findOrFail($id);
        $totalRoom = $room->number_of_rooms_of_this_same_type;

        $holiday = Holiday::Where('hotel_id', $room->hotel_id)->get();

        $holidays  = array_map(
            function ($holiday) {
                return \Carbon\Carbon::parse($holiday['date'])->format('m/d/Y');
            },
            $holiday->toArray()
        );

        $preparation_time = $room->preparation_time;

        $maxhour = 99;
        $hours = BookingHour::orderBy('hour', 'desc')->get();
        $bookingStatus = false;

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

        return view('frontend.room.room-price', $information)->render();
    }
    public function storeReview(Request $request, $id)
    {

        $rule = ['rating' => 'required'];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'The rating field is required for product review.')
                ->withInput();
        }

        $user = Auth::guard('web')->user();

        if ($user) {

            $booking = Booking::Where([['user_id', $user->id], ['room_id', $id]])->get();

            if ($booking != '[]') {
                $room = Room::find($id);
                RoomReview::updateOrCreate(
                    ['user_id' => $user->id, 'room_id' => $id],
                    [
                        'review' => $request->review,
                        'rating' => $request->rating,
                        'hotel_id' => $room->hotel_id
                    ]
                );

                $roomreviews = RoomReview::where('room_id', $id)->get();
                $hotelreviews = RoomReview::where('hotel_id', $room->hotel_id)->get();

                $totalRating = 0;
                $totalhotelRating = 0;

                foreach ($roomreviews as $review) {
                    $totalRating += $review->rating;
                }

                $numOfReview = count($roomreviews);

                $averageRating = $totalRating / $numOfReview;


                foreach ($hotelreviews as $review) {
                    $totalhotelRating += $review->rating;
                }

                $numOfHotelReview = count($hotelreviews);

                $hotelaverageRating = $totalhotelRating / $numOfHotelReview;

                // finally, store the average rating of this hotel
                $room->update(['average_rating' => $averageRating]);
                Hotel::find($room->hotel_id)->update(['average_rating' => $hotelaverageRating]);

                Session::flash('success', 'Your review submitted successfully.');
            } else {
                Session::flash('error', 'You have to Booked First!');
            }
        } else {
        }
        return redirect()->back();
    }

    public function store_visitor(Request $request)
    {
        $request->validate([
            'room_id'
        ]);
        $ipAddress = \Request::ip();
        $check = Visitor::where([['room_id', $request->room_id], ['ip_address', $ipAddress], ['date', Carbon::now()->format('y-m-d')]])->first();
        $room = Room::where('id', $request->room_id)->first();
        if ($room) {
            if (!$check) {
                $visitor = new Visitor();
                $visitor->room_id = $request->room_id;
                $visitor->ip_address = $ipAddress;
                $visitor->vendor_id = $room->vendor_id;
                $visitor->date = Carbon::now()->format('y-m-d');
                $visitor->save();
            }
        }
    }
}
