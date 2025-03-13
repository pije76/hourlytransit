<?php

namespace App\Http\Controllers\Admin\RoomManagement;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Language;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Purifier;
use App\Models\Location\City;
use App\Models\Location\State;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\PaymentGateway\OfflineGateway;
use App\Http\Requests\Room\RoomStoreRequest;
use App\Http\Requests\Room\RoomUpdateRequest;
use App\Models\AdditionalService;
use App\Models\AdditionalServiceContent;
use App\Models\BasicSettings\Basic;
use App\Models\BookingHour;
use App\Models\FeaturedRoomCharge;
use App\Models\Hotel;
use App\Models\HotelCounter;
use App\Models\HourlyRoomPrice;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\RoomContent;
use App\Models\RoomFeature;
use App\Models\RoomImage;
use App\Models\RoomReview;
use App\Models\Visitor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function settings()
    {
        $info = DB::table('basic_settings')->select('room_view')->first();
        return view('admin.room-management.settings', ['info' => $info]);
    }

    public function updateSettings(Request $request)
    {
        $rules = [
            'room_view' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        DB::table('basic_settings')->updateOrInsert(
            ['uniqid' => 12345],
            [
                'room_view' => $request->room_view,
            ]
        );

        Session::flash('success', __('Settings updated successfully') . '!');

        return redirect()->back();
    }
    public function index(Request $request)
    {
        $information['currencyInfo'] = $this->getCurrencyInfo();
        $information['langs'] = Language::all();

        $language =  Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        $language_id = $language->id;
        $status = $title = $roomCategories = $vendor_id  = $featured =  null;

        if (request()->filled('status')) {
            $status = $request->status;
        }

        $type_roomIds = [];
        if (request()->filled('roomCategories') && request()->input('roomCategories') !== "All") {
            $roomCategories = $request->roomCategories;
            $type_content = RoomCategory::where([['language_id', $language->id], ['name', $roomCategories]])->first();

            if (!empty($type_content)) {
                $category = $type_content->id;
                $contents = RoomContent::where('language_id', $language->id)
                    ->where('room_category', $category)
                    ->get()
                    ->pluck('room_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $type_roomIds)) {
                        array_push($type_roomIds, $content);
                    }
                }
            }
        }

        $featured_roomIds = [];
        if ($request->filled('featured') && $request->input('featured') !== "All") {
            $featured = $request->input('featured');

            if ($featured == 'active') {
                $contents = RoomFeature::where('order_status', '=', 'apporved')
                    ->where('payment_status', '=', 'completed')
                    ->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))
                    ->get()
                    ->pluck('room_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $featured_roomIds)) {
                        array_push($featured_roomIds, $content);
                    }
                }
            }
            if ($featured == 'pending') {
                $contents = RoomFeature::where('order_status', '=', 'pending')
                    ->get()
                    ->pluck('room_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $featured_roomIds)) {
                        array_push($featured_roomIds, $content);
                    }
                }
            }
            if ($featured == 'unfeatured') {
                $contents = RoomFeature::where('order_status', '=', 'pending')
                    ->get()
                    ->pluck('room_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $featured_roomIds)) {
                        array_push($featured_roomIds, $content);
                    }
                }
                $contentss = RoomFeature::where('order_status', '=', 'apporved')
                    ->where('payment_status', '=', 'completed')
                    ->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))
                    ->get()
                    ->pluck('room_id');
                foreach ($contentss as $conten) {
                    if (!in_array($conten, $featured_roomIds)) {
                        array_push($featured_roomIds, $conten);
                    }
                }
            }
        }

        if (request()->filled('vendor_id') && request()->input('vendor_id') !== "All") {
            $vendor_id = request()->input('vendor_id');
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

        $information['rooms'] = Room::with([
            'room_content' => function ($q) use ($language_id) {
                $q->where('language_id', $language_id);
            },
            'vendor'
        ])
            ->when($roomCategories, function ($query) use ($type_roomIds) {
                return $query->whereIn('rooms.id', $type_roomIds);
            })

            ->when($featured, function ($query) use ($featured_roomIds, $featured) {
                if ($featured !== 'unfeatured') {
                    return $query->whereIn('rooms.id', $featured_roomIds);
                } else {
                    return $query->whereNotIn('rooms.id', $featured_roomIds);
                }
            })

            ->when($status, function ($query) use ($status) {

                if ($status === 'approved') {
                    return $query->where('status', 1);
                } elseif ($status === 'pending') {
                    return $query->where('status', 0);
                } else {
                    return $query->where('status', 2);
                }
            })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('rooms.id', $roomIds);
            })
            ->when($vendor_id, function ($query) use ($vendor_id) {
                if ($vendor_id === 'admin') {
                    return $query->where('vendor_id', '0');
                } else {
                    return $query->where('vendor_id', $vendor_id);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
        $information['vendors'] = Vendor::where('id', '!=', 0)->get();
        $information['roomCategories'] = RoomCategory::Where('language_id', $language_id)->get();

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


        $charges = FeaturedRoomCharge::orderBy('days')->get();
        $information['charges'] = $charges;
        return view('admin.room-management.index', $information);
    }

    public function updateStatus(Request $request)
    {

        $room = Room::findOrFail($request->roomId);

        if ($request->status == 1) {
            $room->update(['status' => 1]);

            Session::flash('success', __('Room Active successfully') . '!');
        }
        if ($request->status == 0) {
            $room->update(['status' => 0]);

            Session::flash('success', __('Room Deactive successfully') . '!');
        }

        return redirect()->back();
    }

    public function updateFeatured(Request $request)
    {
        $rules = [
            'charge' => 'required',
        ];

        $message = [
            'charge.required' => 'The charge field is required.'
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        if (!$request->exists('charge')) {

            $errorMessageKey = "select_days_" . $request->room_id;
            Session::flash($errorMessageKey, 'Please select promotion list.');
            Session::flash($errorMessageKey, __('Please select promotion list') . '.');
            return redirect()->back()->withInput();
        }
        $gatewayId = $request->gateway;
        $offlineGateway = OfflineGateway::query()->find($gatewayId);
        $chargeID = $request->charge;
        $charge = FeaturedRoomCharge::findorfail($chargeID);
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays($charge->days);

        $vendor_id = Hotel::where('id', $request->room_id)->pluck('vendor_id')->first();

        $be = Basic::select('to_mail')->firstOrFail();
        if ($vendor_id != 0) {
            $vendor = Vendor::where('id', $vendor_id)->select('to_mail', 'username', 'email')->first();

            if (isset($vendor->to_mail)) {
                $to_mail = $vendor->to_mail;
            } else {
                $to_mail = $vendor->email;
            }
        } else {
            $to_mail = $be->to_mail;
        }

        $order =  RoomFeature::where('room_id', $request->room_id)->first();
        if (empty($order)) {
            $order = new RoomFeature();
        }



        $order->room_id = $request->room_id;
        $order->vendor_id = $vendor_id;
        $order->vendor_mail = $to_mail;
        $order->order_number = uniqid();
        $order->total = $charge->price;
        $order->payment_method = $offlineGateway ? $offlineGateway->name : $gatewayId;
        $order->gateway_type =  $offlineGateway ? "offline" : "online";
        $order->payment_status = "completed";
        $order->order_status = 'apporved';
        $order->days = $charge->days;
        $order->start_date = $startDate;
        $order->end_date = $endDate;
        $order->currency_symbol = $be->base_currency_symbol;
        $order->currency_symbol_position = $be->base_currency_symbol_position;

        $order->save();

        Session::flash('success', __('Room Featured successfully') . '!');
        return  redirect()->back();
    }

    public function unfeature($id)
    {
        $order = RoomFeature::find($id);

        // delete the attachment
        @unlink(public_path('assets/file/attachments/room-feature/') . $order->attachment);

        $order->delete();

        return redirect()->back()->with('success',  __('Unfeatured successfully') . '!');
    }

    public function selectVendor()
    {
        $information = [];
        $languages = Language::get();
        $information['languages'] = $languages;
        $information['vendors'] = Vendor::join('memberships', 'vendors.id', '=', 'memberships.vendor_id')
            ->where([
                ['memberships.status', '=', 1],
                ['memberships.start_date', '<=', Carbon::now()->format('Y-m-d')],
                ['memberships.expire_date', '>=', Carbon::now()->format('Y-m-d')]
            ])
            ->select('vendors.id', 'vendors.username')
            ->get();
        return view('admin.room-management.select-vendor', $information);
    }
    public function findVendor(Request $request)
    {
        return redirect()->route('admin.room_management.create_room', ['vendor_id' => $request->vendor_id ?? 0]);
    }

    public function create($id)
    {
        $information = [];
        $information['vendor_id'] = $id;
        $languages = Language::get();
        $information['languages'] = $languages;
        $information['bookingHours']  = BookingHour::orderBy('serial_number', 'asc')->get();
        $language = Language::where('is_default', 1)->first();
        $language_id = $language->id;

        $information['hotels'] = Hotel::with([
            'hotel_contents' => function ($q) use ($language_id) {
                $q->where('language_id', $language_id);
            },
            'vendor'
        ])
            ->where('vendor_id', $id)
            ->orderBy('id', 'desc')
            ->select('id')
            ->get();

        if ($id != 0) {
            $package = VendorPermissionHelper::packagePermission($id);
            if ($package != '[]') {
                return view('admin.room-management.create', $information);
            } else {

                Session::flash('success', __('This vendor has\'t membership') . '!');
                return redirect()->route('admin.room_management.select_vendor');
            }
        } else {
            return view('admin.room-management.create', $information);
        }
    }
    public function imagesstore(Request $request)
    {
        $img = $request->file('file');
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
        $rules = [
            'file' => [
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    $ext = $img->getClientOriginalExtension();
                    if (!in_array($ext, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg images are allowed");
                    }
                },
            ]
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }
        $filename = uniqid() . '.jpg';

        $directory = public_path('assets/img/room/room-gallery/');
        @mkdir($directory, 0775, true);
        $img->move($directory, $filename);

        $pi = new RoomImage();
        $pi->image = $filename;
        $pi->save();
        return response()->json(['status' => 'success', 'file_id' => $pi->id]);
    }
    public function imagermv(Request $request)
    {
        $pi = RoomImage::findOrFail($request->fileid);
        @unlink(public_path('assets/img/room/room-gallery/') . $pi->image);
        $pi->delete();
        return $pi->id;
    }
    public function imagedbrmv(Request $request)
    {
        $pi = RoomImage::findOrFail($request->fileid);
        $image_count = RoomImage::where('room_id', $pi->room_id)->get()->count();
        if ($image_count > 1) {
            @unlink(public_path('assets/img/room/room-gallery/') . $pi->image);
            $pi->delete();

            Session::flash('success', __('Slider image deleted successfully') . '!');
            return Response::json(['status' => 'success'], 200);
        } else {
            Session::flash('warning', __('You can\'t delete all images') . '!');
            return Response::json(['status' => 'success'], 200);
        }
    }
    public function getState(Request $request)
    {
        $data['states'] = State::where('country_id', $request->id)->get();
        $data['cities'] = City::where('country_id', $request->id)->get();
        return $data;
    }
    public function getCity(Request $request)
    {
        $data = City::where('state_id', $request->id)->get();
        return $data;
    }
    public function store(RoomStoreRequest $request)
    {
        $vendorId = $request->vendor_id;

        $current_package = VendorPermissionHelper::packagePermission($vendorId);
        if ($vendorId != 0) {
            $current_packageRoom = $current_package->number_of_room;
        } else {
            $current_packageRoom = 999999;
        }

        $totalHotelAdded = vendorTotalAddedRoom($vendorId);

        if ($totalHotelAdded < $current_packageRoom) {

            $featuredImgURL = $request->feature_image;

            $languages = Language::all();

            $in = $request->all();

            if ($request->feature_image) {
                $featuredImgExt = $featuredImgURL->getClientOriginalExtension();
                // set a name for the featured image and store it to local storage
                $featuredImgName = uniqid() . '.' . $featuredImgExt;
                $featuredDir = public_path('assets/img/room/featureImage/');

                if (!file_exists($featuredDir)) {
                    @mkdir($featuredDir, 0777, true);
                }

                copy($featuredImgURL, $featuredDir . $featuredImgName);
                $in['feature_image'] = $featuredImgName;
            }
            $in['vendor_id'] = $vendorId;

            $prices = $request->prices;
            $in['prices'] = json_encode($prices);

            $room = Room::create($in);

            $hours = BookingHour::orderBy('serial_number', 'asc')->get();
            for ($i = 0; $i < $hours->count(); $i++) {
                $hourlyRoomPrice = new HourlyRoomPrice();

                $hourlyRoomPrice->room_id = $room->id;
                $hourlyRoomPrice->vendor_id = $room->vendor_id;
                $hourlyRoomPrice->hotel_id = $room->hotel_id;
                $hourlyRoomPrice->hour_id = $hours[$i]->id;
                $hourlyRoomPrice->hour = $hours[$i]->hour;
                $hourlyRoomPrice->price = $prices[$i];

                $hourlyRoomPrice->save();
            }
            $roomprice = Room::findOrFail($room->id);

            $roomprice->min_price  = roomMinPrice($room->id);
            $roomprice->max_price   = roomMaxPrice($room->id);

            $roomprice->save();


            $hotel = Hotel::findOrFail($request->hotel_id);

            $hotel->min_price  = hotelMinPrice($request->hotel_id);
            $hotel->max_price   = hotelMaxPrice($request->hotel_id);

            $hotel->save();


            $siders = $request->slider_images;
            if ($siders) {
                $pis = RoomImage::findOrFail($siders);

                foreach ($pis as $key => $pi) {
                    $pi->room_id = $room->id;
                    $pi->save();
                }
            }

            foreach ($languages as $language) {
                $code = $language->code;
                if (
                    $language->is_default == 1 ||
                    $request->filled($code . '_title')
                ) {
                    $roomContent = new RoomContent();

                    $roomContent->language_id = $language->id;
                    $roomContent->room_id = $room->id;
                    $roomContent->title = $request[$code . '_title'];
                    $roomContent->slug = createSlug($request[$code . '_title']);
                    $roomContent->room_category = $request[$code . '_room_category'];
                    $amenities = $request->input($code . '_amenities', []);
                    $roomContent->amenities = json_encode($amenities);
                    $roomContent->description = Purifier::clean($request[$code . '_description'], 'youtube');
                    $roomContent->meta_keyword = $request[$code . '_meta_keyword'];
                    $roomContent->meta_description = $request[$code . '_meta_description'];

                    $roomContent->save();
                }
            }
            Session::flash('success', __('New Room added successfully') . '!');

            return Response::json(['status' => 'success'], 200);
        } else {
            Session::flash('success', __('Room limit reached or exceeded') . '!');

            return Response::json(['status' => 'success'], 200);
        }
    }




    public function manageAdditionalService(Request $request, $id)
    {
        $information['room'] =  Room::findorfail($id);

        $information['room_id'] = $id;
        $information['specifications'] = HotelCounter::where('hotel_id', $id)->get();

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        $information['services'] = AdditionalService::join('additional_service_contents', 'additional_services.id', '=', 'additional_service_contents.additional_service_id')
            ->Where('additional_service_contents.language_id', $language->id)
            ->select('additional_services.id as id', 'additional_services.status', 'additional_service_contents.title', 'additional_services.serial_number')
            ->get();

        return view('admin.room-management.additional-service', $information);
    }

    public function updateAdditionalService(Request $request, $id)
    {

        $adminLangCode = Auth::guard('admin')->user()->code;

        $language = Language::Where('code', $adminLangCode)->first();

        $room = Room::where('id', $id)->first();

        if ($request->checkbox) {
            $data = [
                'checkbox' => $request->checkbox,
            ];

            $additional = [];

            $rules = [];
            $customMessages = [];

            foreach ($data['checkbox'] as $value) {
                $service = AdditionalServiceContent::where([['additional_service_id', $value], ['language_id', $language->id]])->first();

                $rules['price_' . $value] = 'required|numeric';

                if ($service) {
                    $customMessages['price_' . $value . '.required'] = ucfirst($service->title) . ' price is required.';
                    $customMessages['price_' . $value . '.numeric'] = ucfirst($service->title) . ' price must be a number.';
                } else {
                    $customMessages['price_' . $value . '.required'] = 'Price is required for the selected service.';
                    $customMessages['price_' . $value . '.numeric'] = 'Price must be a number for the selected service.';
                }
            }

            $validator = Validator::make($request->all(), array_merge($rules));

            $validator->setCustomMessages($customMessages);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->checkbox) {
                foreach ($data['checkbox'] as $value) {
                    $additional[$value] = $request->input('price_' . $value) ?? 0;
                }
            } else {
                $additional = null;
            }

            $room->additional_service = $additional;
        } else {
            $room->additional_service = null;
        }


        $room->save();

        Session::flash('success', __('Additional Specification Updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function edit($id)
    {
        $vendorId = Room::where('id', $id)->pluck('vendor_id')->first();
        $language =  Language::query()->where('is_default', '=', 1)->firstOrFail();

        $language_id = $language->id;

        $information['hotels'] = Hotel::with([
            'hotel_contents' => function ($q) use ($language_id) {
                $q->where('language_id', $language_id);
            },
            'vendor'
        ])
            ->where('vendor_id', $vendorId)
            ->orderBy('id', 'desc')
            ->select('id')
            ->get();

        if ($vendorId != 0) {
            $current_package = VendorPermissionHelper::packagePermission($vendorId);


            if ($current_package != '[]') {
                $information['room'] = Room::with('room_galleries')->findOrFail($id);
                $information['languages'] = Language::all();
                $information['bookingHours'] = BookingHour::orderBy('serial_number', 'asc')->get();
                $information['prices'] = HourlyRoomPrice::Where('room_id', $id)->get();
                return view('admin.room-management.edit', $information);
            } else {

                Session::flash('success', __('This vendor has\'t membership') . '!');
                return redirect()->route('admin.room_management.rooms');
            }
        } else {
            $information['room'] = Room::with('room_galleries')->findOrFail($id);
            $information['languages'] = Language::all();
            $information['bookingHours'] = BookingHour::orderBy('serial_number', 'asc')->get();
            $information['prices'] = HourlyRoomPrice::Where('room_id', $id)->get();
            return view('admin.room-management.edit', $information);
        }
    }

    public function update(RoomUpdateRequest $request, $id)
    {
        $featuredImgURL = $request->thumbnail;

        $allowedExts = array('jpg', 'png', 'jpeg', 'svg');
        if ($request->hasFile('thumbnail')) {
            $rules['thumbnail'] = [
                'required',
                function ($attribute, $value, $fail) use ($featuredImgURL, $allowedExts) {
                    $ext = $featuredImgURL->getClientOriginalExtension();
                    if (!in_array($ext, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg images are allowed");
                    }
                },
            ];
        }

        $languages = Language::all();

        $in = $request->all();
        $room = Room::findOrFail($id);
        if ($request->hasFile('thumbnail')) {
            $featuredImgExt = $featuredImgURL->getClientOriginalExtension();

            $featuredImgName = time() . '.' . $featuredImgExt;
            $featuredDir = public_path('assets/img/room/featureImage/');

            if (!file_exists($featuredDir)) {
                mkdir($featuredDir, 0777, true);
            }
            copy($featuredImgURL, $featuredDir . $featuredImgName);
            @unlink(public_path('assets/img/room/featureImage/') . $room->feature_image);

            $in['feature_image'] = $featuredImgName;
        }

        $prices = $request->prices;
        $in['prices'] = json_encode($prices);



        $totalPrice =  HourlyRoomPrice::Where('room_id', $id)->get();

        $hours = BookingHour::orderBy('serial_number', 'asc')->get();
        for ($i = 0; $i < $hours->count(); $i++) {

            if (isset($totalPrice[$i]->id)) {
                $hourlyRoomPrice =  HourlyRoomPrice::where('id', $totalPrice[$i]->id)->first();
            } else {
                $hourlyRoomPrice = new HourlyRoomPrice();
            }

            $hourlyRoomPrice->room_id = $id;
            $hourlyRoomPrice->vendor_id = $room->vendor_id;
            $hourlyRoomPrice->hotel_id = $room->hotel_id;
            $hourlyRoomPrice->hour_id = $hours[$i]->id;
            $hourlyRoomPrice->hour = $hours[$i]->hour;
            $hourlyRoomPrice->price = $prices[$i];

            $hourlyRoomPrice->save();
        }

        $in['min_price'] = roomMinPrice($id);
        $in['max_price'] = roomMaxPrice($id);
        $room->update($in);

        $hotel = Hotel::findOrFail($request->hotel_id);

        $hotel->min_price  = hotelMinPrice($request->hotel_id);
        $hotel->max_price   = hotelMaxPrice($request->hotel_id);

        $hotel->save();

        $slders = $request->slider_images;
        if ($slders) {
            $pis = RoomImage::findOrFail($slders);
            foreach ($pis as $key => $pi) {
                $pi->room_id = $request->room_id;
                $pi->save();
            }
        }
        foreach ($languages as $language) {

            $code = $language->code;

            $roomContent =  RoomContent::where('room_id', $id)->where('language_id', $language->id)->first();

            if (empty($roomContent)) {
                $roomContent = new RoomContent();
            }

            if (
                $language->is_default == 1 ||
                $request->filled($code . '_title')
            ) {
                $roomContent->language_id = $language->id;
                $roomContent->room_id = $id;
                $roomContent->title = $request[$code . '_title'];
                $roomContent->slug = createSlug($request[$code . '_title']);
                $roomContent->room_category = $request[$code . '_room_category'];
                $amenities = $request->input($code . '_amenities', []);
                $roomContent->amenities = json_encode($amenities);
                $roomContent->description = Purifier::clean($request[$code . '_description'], 'youtube');
                $roomContent->meta_keyword = $request[$code . '_meta_keyword'];
                $roomContent->meta_description = $request[$code . '_meta_description'];

                $roomContent->save();
            }
        }

        Session::flash('success', __('Room Updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function delete($id)
    {
        $room = Room::findOrFail($id);

        //delete all the contents of this room
        $contents = $room->room_content()->get();

        foreach ($contents as $content) {
            $content->delete();
        }


        //delete  the feature of this room
        $room->room_feature()->delete();

        //delete all the price of this room
        $prices = $room->room_prices()->get();

        foreach ($prices as $price) {
            $price->delete();
        }


        // delete feature_image of this room
        if (!is_null($room->feature_image)) {
            @unlink(public_path('assets/img/room/featureImage/') . $room->feature_image);
        }


        //delete all the images of this room
        $room_galleries = $room->room_galleries()->get();

        foreach ($room_galleries as $gallery) {
            @unlink(public_path('assets/img/room/room-gallery/') . $gallery->image);
            $gallery->delete();
        }

        //delete all reviews for this room
        $reviews = RoomReview::where('room_id', $room->id)->get();
        if (!is_null($reviews)) {
            foreach ($reviews as $review) {
                $review->delete();
            }
        }

        //delete all visit for this room
        $visitors  = Visitor::where('room_id', $room->id)->get();
        if (!is_null($visitors)) {
            foreach ($visitors as $visitor) {
                $visitor->delete();
            }
        }

        // finally, delete this room
        $room->delete();

        Session::flash('success', __('Room deleted successfully') . '!');

        return redirect()->back();
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $room = Room::findOrFail($id);

            //delete all the contents of this room
            $contents = $room->room_content()->get();

            foreach ($contents as $content) {
                $content->delete();
            }

            //delete  the feature of this room
            $room->room_feature()->delete();

            //delete all the price of this room
            $prices = $room->room_prices()->get();

            foreach ($prices as $price) {
                $price->delete();
            }

            // delete feature_image of this room
            if (!is_null($room->feature_image)) {
                @unlink(public_path('assets/img/room/featureImage/') . $room->feature_image);
            }


            //delete all the images of this room
            $room_galleries = $room->room_galleries()->get();

            foreach ($room_galleries as $gallery) {
                @unlink(public_path('assets/img/room/room-gallery/') . $gallery->image);
                $gallery->delete();
            }

            //delete all reviews for this room
            $reviews = RoomReview::where('room_id', $room->id)->get();
            if (!is_null($reviews)) {
                foreach ($reviews as $review) {
                    $review->delete();
                }
            }


            //delete all visit for this room
            $visitors  = Visitor::where('room_id', $room->id)->get();
            if (!is_null($visitors)) {
                foreach ($visitors as $visitor) {
                    $visitor->delete();
                }
            }


            // finally, delete this room
            $room->delete();
        }

        Session::flash('success', __('Room deleted successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }
}
