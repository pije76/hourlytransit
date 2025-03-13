<?php

namespace App\Http\Controllers\Admin\HotelManagement;

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
use App\Http\Requests\Hotel\HotelStoreRequest;
use App\Http\Requests\Hotel\HotelUpdateRequest;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedHotelCharge;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\HotelContent;
use App\Models\HotelCounter;
use App\Models\HotelCounterContent;
use App\Models\HotelFeature;
use App\Models\HotelImage;
use App\Models\RoomReview;
use App\Models\Visitor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{

    public function settings()
    {
        $info = DB::table('basic_settings')->select('hotel_view',  'time_format')->first();
        return view('admin.hotel-management.settings', ['info' => $info]);
    }

    public function updateSettings(Request $request)
    {

        $rules = [
            'hotel_view' => 'required|numeric',
            'time_format' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // store the tax amount info into db
        DB::table('basic_settings')->updateOrInsert(
            ['uniqid' => 12345],
            [
                'hotel_view' => $request->hotel_view,
                'time_format' => $request->time_format
            ]
        );

        Session::flash('success', __('Settings updated successfull') . '!');

        return redirect()->back();
    }

    public function index(Request $request)
    {
        $information['currencyInfo'] = $this->getCurrencyInfo();
        $information['langs'] = Language::all();

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        $language_id = $language->id;
        $status = $title = $category = $vendor_id = $featured = null;
        if (request()->filled('status')) {
            $status = $request->status;
        }

        $category_hotelIds = [];
        if (request()->filled('category') && request()->input('category') !== "All") {
            $category = $request->category;
            $category_content = HotelCategory::where([['language_id', $language->id], ['slug', $category]])->first();

            if (!empty($category_content)) {
                $category = $category_content->id;
                $contents = HotelContent::where('language_id', $language->id)
                    ->where('category_id', $category)
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $category_hotelIds)) {
                        array_push($category_hotelIds, $content);
                    }
                }
            }
        }

        $featured_hotelIds = [];
        if ($request->filled('featured') && $request->input('featured') !== "All") {
            $featured = $request->input('featured');

            if ($featured == 'active') {
                $contents = HotelFeature::where('order_status', '=', 'apporved')
                    ->where('payment_status', '=', 'completed')
                    ->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $featured_hotelIds)) {
                        array_push($featured_hotelIds, $content);
                    }
                }
            }
            if ($featured == 'pending') {
                $contents = HotelFeature::where('order_status', '=', 'pending')
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $featured_hotelIds)) {
                        array_push($featured_hotelIds, $content);
                    }
                }
            }
            if ($featured == 'unfeatured') {
                $contents = HotelFeature::where('order_status', '=', 'pending')
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contents as $content) {
                    if (!in_array($content, $featured_hotelIds)) {
                        array_push($featured_hotelIds, $content);
                    }
                }
                $contentss = HotelFeature::where('order_status', '=', 'apporved')
                    ->where('payment_status', '=', 'completed')
                    ->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))
                    ->get()
                    ->pluck('hotel_id');
                foreach ($contentss as $conten) {
                    if (!in_array($conten, $featured_hotelIds)) {
                        array_push($featured_hotelIds, $conten);
                    }
                }
            }
        }


        $hotelIds = [];
        if ($request->filled('title')) {
            $title = $request->title;
            $room_contents = HotelContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('hotel_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $hotelIds)) {
                    array_push($hotelIds, $room_content);
                }
            }
        }

        if (request()->filled('vendor_id') && request()->input('vendor_id') !== "All") {
            $vendor_id = request()->input('vendor_id');
        }

        $information['hotels'] = Hotel::with([
            'hotel_contents' => function ($q) use ($language_id) {
                $q->where('language_id', $language_id);
            },
            'vendor'
        ])
            ->when($category, function ($query) use ($category_hotelIds) {
                return $query->whereIn('hotels.id', $category_hotelIds);
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
            ->when($featured, function ($query) use ($featured_hotelIds, $featured) {
                if ($featured !== 'unfeatured') {
                    return $query->whereIn('hotels.id', $featured_hotelIds);
                } else {
                    return $query->whereNotIn('hotels.id', $featured_hotelIds);
                }
            })
            ->when($vendor_id, function ($query) use ($vendor_id) {
                if ($vendor_id === 'admin') {
                    return $query->where('vendor_id', '0');
                } else {
                    return $query->where('vendor_id', $vendor_id);
                }
            })
            ->when($title, function ($query) use ($hotelIds) {
                return $query->whereIn('hotels.id', $hotelIds);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
        $information['vendors'] = Vendor::where('id', '!=', 0)->get();
        $information['categories'] = HotelCategory::Where('language_id', $language_id)->get();

        //Feature part
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

        $charges = FeaturedHotelCharge::orderBy('days')->get();
        $information['charges'] = $charges;
        return view('admin.hotel-management.index', $information);
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
        return view('admin.hotel-management.select-vendor', $information);
    }
    public function findVendor(Request $request)
    {
        return redirect()->route('admin.hotel_management.create_hotel', ['vendor_id' => $request->vendor_id ?? 0]);
    }
    public function create($id)
    {

        if ($id != 0) {
            $package = VendorPermissionHelper::packagePermission($id);
            if ($package != '[]') {

                $information = [];
                $languages = Language::get();
                $information['languages'] = $languages;
                $information['vendor_id'] = $id;
                return view('admin.hotel-management.create', $information);
            } else {

                Session::flash('success', __('This vendor has\'t membership') . '!');
                return redirect()->route('admin.hotel_management.select_vendor');
            }
        } else {
            $information = [];
            $languages = Language::get();
            $information['languages'] = $languages;
            $information['vendor_id'] = $id;

            return view('admin.hotel-management.create', $information);
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

        $directory = public_path('assets/img/hotel/hotel-gallery/');
        @mkdir($directory, 0775, true);
        $img->move($directory, $filename);

        $pi = new HotelImage();
        $pi->image = $filename;
        $pi->save();
        return response()->json(['status' => 'success', 'file_id' => $pi->id]);
    }
    public function imagermv(Request $request)
    {
        $pi = HotelImage::findOrFail($request->fileid);
        @unlink(public_path('assets/img/hotel/hotel-gallery/') . $pi->image);
        $pi->delete();
        return $pi->id;
    }
    public function imagedbrmv(Request $request)
    {
        $pi = HotelImage::findOrFail($request->fileid);
        $image_count = HotelImage::where('hotel_id', $pi->hotel_id)->get()->count();
        if ($image_count > 1) {
            @unlink(public_path('assets/img/hotel/hotel-gallery/') . $pi->image);
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
    public function store(HotelStoreRequest $request)
    {
        $vendorId = $request->vendor_id;

        $current_package = VendorPermissionHelper::packagePermission($vendorId);
        if ($vendorId != 0) {
            $current_packageHotel = $current_package->number_of_hotel;
        } else {
            $current_packageHotel = 999999;
        }

        $totalHotelAdded = vendorTotalAddedHotel($vendorId);

        if ($totalHotelAdded < $current_packageHotel) {

            $logoImgURL = $request->logo;

            $languages = Language::all();

            $in = $request->all();

            $in['vendor_id'] = $vendorId;

            if ($request->logo) {
                $logoImgExt = $logoImgURL->getClientOriginalExtension();
                // set a name for the featured image and store it to local storage
                $logoImgName = time() . '.' . $logoImgExt;
                $logoDir = public_path('assets/img/hotel/logo/');

                if (!file_exists($logoDir)) {
                    @mkdir($logoDir, 0777, true);
                }

                copy($logoImgURL, $logoDir . $logoImgName);
                $in['logo'] = $logoImgName;
            }

            $hotel = Hotel::create($in);

            $siders = $request->slider_images;
            if ($siders) {
                $pis = HotelImage::findOrFail($siders);

                foreach ($pis as $key => $pi) {
                    $pi->hotel_id = $hotel->id;
                    $pi->save();
                }
            }

            foreach ($languages as $language) {
                $code = $language->code;
                if (
                    $language->is_default == 1 ||
                    $request->filled($code . '_title')
                ) {
                    $hotelContent = new HotelContent();

                    $hotelContent->language_id = $language->id;
                    $hotelContent->hotel_id = $hotel->id;
                    $hotelContent->title = $request[$code . '_title'];
                    $hotelContent->slug = createSlug($request[$code . '_title']);
                    $hotelContent->category_id = $request[$code . '_category_id'];
                    $hotelContent->country_id = $request[$code . '_country_id'];
                    $hotelContent->state_id = $request[$code . '_state_id'];
                    $hotelContent->city_id = $request[$code . '_city_id'];
                    $hotelContent->address =  $request[$code . '_address'];
                    $amenities = $request->input($code . '_aminities', []);
                    $hotelContent->amenities = json_encode($amenities);
                    $hotelContent->description = Purifier::clean($request[$code . '_description'], 'youtube');
                    $hotelContent->meta_keyword = $request[$code . '_meta_keyword'];
                    $hotelContent->meta_description = $request[$code . '_meta_description'];

                    $hotelContent->save();
                }
            }
            Session::flash('success', __('New Hotel added successfully') . '!');

            return Response::json(['status' => 'success'], 200);
        } else {
            Session::flash('warning', __('Hotel limit reached or exceeded') . '!');

            return Response::json(['status' => 'success'], 200);
        }
    }

    public function updateStatus(Request $request)
    {

        $hotel = Hotel::findOrFail($request->hotelId);

        if ($request->status == 1) {
            $hotel->update(['status' => 1]);

            Session::flash('success', __('Hotel Active successfully') . '!');
        }
        if ($request->status == 0) {
            $hotel->update(['status' => 0]);
            Session::flash('success', __('Hotel Deactive successfully') . '!');
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

            $errorMessageKey = "select_days_" . $request->hotel_id;
            Session::flash($errorMessageKey, __('Please select promotion list') . '.');
            return redirect()->back()->withInput();
        }
        $gatewayId = $request->gateway;
        $offlineGateway = OfflineGateway::query()->find($gatewayId);
        $chargeID = $request->charge;
        $charge = FeaturedHotelCharge::findorfail($chargeID);
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays($charge->days);

        $vendor_id = Hotel::where('id', $request->hotel_id)->pluck('vendor_id')->first();

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

        $order =  HotelFeature::where('hotel_id', $request->hotel_id)->first();
        if (empty($order)) {
            $order = new HotelFeature();
        }

        $order->hotel_id = $request->hotel_id;
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

        Session::flash('success', __('Hotel Featured successfully') . '!');
        return  redirect()->back();
    }

    public function unfeature($id)
    {
        $feature = HotelFeature::find($id);

        // delete the attachment
        @unlink(public_path('assets/file/attachments/hotel-feature/') . $feature->attachment);

        // delete the invoice
        @unlink(public_path('assets/file/invoices/hotel-feature/') . $feature->invoice);

        $feature->delete();

        return redirect()->back()->with('success',  __('Unfeatured successfully') . '!');
    }


    public function manageCounterInformation($id)
    {
        Hotel::where('id', $id)->firstOrFail();
        $vendorId = Hotel::where('id', $id)->pluck('vendor_id')->first();
        $current_package = VendorPermissionHelper::packagePermission($vendorId);

        if ($vendorId == 0) {
            $information['hotel_id'] = $id;
            $information['languages'] = Language::all();
            $information['specifications'] = HotelCounter::where('hotel_id', $id)->get();
            return view('admin.hotel-management.counter', $information);
        } else {

            if ($current_package != '[]') {

                $information['hotel_id'] = $id;
                $information['languages'] = Language::all();
                $information['specifications'] = HotelCounter::where('hotel_id', $id)->get();
                return view('admin.hotel-management.counter', $information);
            } else {

                Session::flash('success', __('This vendor has no package') . '!');
                return redirect()->route('admin.hotel_management.hotels', ['language' => Auth::user()->code]);
            }
        }
    }

    public function updateCounterInformation(Request $request, $id)
    {
        $languages = Language::all();

        $HotelCounters = HotelCounter::where('hotel_id', $id)->get();
        foreach ($HotelCounters as $HotelCounter) {
            $HotelCountersContents = HotelCounterContent::where('hotel_counter_id', $HotelCounter->id)->get();
            foreach ($HotelCountersContents as $HotelCountersContent) {
                $HotelCountersContent->delete();
            }
            $HotelCounter->delete();
        }

        foreach ($languages as $language) {


            if (!empty($request[$language->code . '_label'])) {
                $label_datas = $request[$language->code . '_label'];
                foreach ($label_datas as $key => $data) {
                    $property_specification = HotelCounter::where([['hotel_id', $id], ['key', $key]])->first();
                    if (is_null($property_specification)) {
                        $property_specification = new HotelCounter();
                        $property_specification->hotel_id = $id;
                        $property_specification->key  = $key;
                        $property_specification->save();
                    }
                    $property_specification_content = new HotelCounterContent();
                    $property_specification_content->language_id = $language->id;
                    $property_specification_content->hotel_counter_id = $property_specification->id;
                    $property_specification_content->label = $data;
                    $property_specification_content->value = $request[$language->code . '_value'][$key];
                    $property_specification_content->save();
                }
            }
        }

        Session::flash('success', __('Counter Information Updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function CounterDelete(Request $request)
    {
        $hotel_counter = HotelCounter::find($request->spacificationId);
        $hotel_counter_contents = HotelCounterContent::where('hotel_counter_id', $hotel_counter->id)->get();
        foreach ($hotel_counter_contents as $hotel_counter_content) {
            $hotel_counter_content->delete();
        }
        $hotel_counter->delete();

        Session::flash('success', __('Counter deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }


    public function edit($id)
    {
        $vendorId = Hotel::where('id', $id)->pluck('vendor_id')->first();
        $defaultLang = Language::query()->where('is_default', 1)->first();
        if ($vendorId != 0) {
            $current_package = VendorPermissionHelper::packagePermission($vendorId);

            if ($current_package != '[]') {
                $information['hotel'] = Hotel::with('hotel_galleries')->findOrFail($id);
                $information['hotelAddress'] = HotelContent::where([
                    ['language_id', $defaultLang->id],
                    [
                        'hotel_id',
                        $id
                    ]
                ])->pluck('address')->first();
 
                $information['languages'] = Language::all();
                return view('admin.hotel-management.edit', $information);
            } else {

                Session::flash('warning', __('This vendor has no package') . '!');
                return redirect()->route('admin.hotel_management.hotels', ['language' => Auth::user()->code]);
            }
        } else {
            $information['hotel'] = Hotel::with('hotel_galleries')->findOrFail($id);
            $information['hotelAddress'] = HotelContent::where([
                ['language_id', $defaultLang->id],
                ['hotel_id', $id]
            ])->pluck('address')->first();

            $information['languages'] = Language::all();
            return view('admin.hotel-management.edit', $information);
        }
    }

    public function update(HotelUpdateRequest $request, $id)
    {
        $logoImgURL = $request->logo;

        $languages = Language::all();

        $in = $request->all();
        $hotel = Hotel::findOrFail($request->hotel_id);


        if ($request->hasFile('logo')) {
            $logoImgExt = $logoImgURL->getClientOriginalExtension();

            $logoImgName = time() . '.' . $logoImgExt;
            $logoDir = public_path('assets/img/hotel/logo/');

            if (!file_exists($logoDir)) {
                mkdir($logoDir, 0777, true);
            }
            copy($logoImgURL, $logoDir . $logoImgName);
            @unlink(public_path('assets/img/hotel/logo/') . $hotel->logo);

            $in['logo'] = $logoImgName;
        }

        $in['min_price'] = hotelMinPrice($request->hotel_id);
        $in['max_price'] = hotelMaxPrice($request->hotel_id);

        $hotel = $hotel->update($in);

        $slders = $request->slider_images;
        if ($slders) {
            $pis = HotelImage::findOrFail($slders);
            foreach ($pis as $key => $pi) {
                $pi->hotel_id = $request->hotel_id;
                $pi->save();
            }
        }


        foreach ($languages as $language) {
            $code = $language->code;

            $hotelContent =  HotelContent::where('hotel_id', $request->hotel_id)->where('language_id', $language->id)->first();

            if (empty($hotelContent)) {
                $hotelContent = new HotelContent();
            }

            if (
                $language->is_default == 1 ||
                $request->filled($code . '_title')
            ) {
                $hotelContent->language_id = $language->id;
                $hotelContent->hotel_id = $request->hotel_id;
                $hotelContent->title = $request[$code . '_title'];
                $hotelContent->slug = createSlug($request[$code . '_title']);
                $hotelContent->category_id = $request[$code . '_category_id'];
                $hotelContent->country_id = $request[$code . '_country_id'];
                $hotelContent->state_id = $request[$code . '_state_id'];
                $hotelContent->city_id = $request[$code . '_city_id'];
                $hotelContent->address = $request[$code . '_address'];
                $amenities = $request->input($code . '_aminities', []);
                $hotelContent->amenities = json_encode($amenities);
                $hotelContent->description = Purifier::clean($request[$code . '_description'], 'youtube');
                $hotelContent->meta_keyword = $request[$code . '_meta_keyword'];
                $hotelContent->meta_description = $request[$code . '_meta_description'];
                $hotelContent->save();
            }
        }

        Session::flash('success', __('Hotel Updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function delete($id)
    {
        $hotel = Hotel::findOrFail($id);

        //delete all the contents of this hotel
        $contents = $hotel->hotel_contents()->get();

        foreach ($contents as $content) {
            $content->delete();
        }

        //delete all the holidays  of this hotel
        $holidays = $hotel->holidays()->get();

        foreach ($holidays as $holiday) {
            $holiday->delete();
        }

        //delete  the feature of this hotel
        $hotel->hotel_feature()->delete();


        if (!is_null($hotel->logo)) {
            @unlink(public_path('assets/img/hotel/logo/') . $hotel->logo);
        }

        //delete all the images of this hotel
        $galleries = $hotel->hotel_galleries()->get();

        foreach ($galleries as $gallery) {
            @unlink(public_path('assets/img/hotel/hotel-gallery/') . $gallery->image);
            $gallery->delete();
        }

        $rooms = $hotel->room()->get();
        foreach ($rooms as $room) {

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

            //delete all visit for this room
            $visitors  = Visitor::where('room_id', $room->id)->get();
            if (!is_null($visitors)) {
                foreach ($visitors as $visitor) {
                    $visitor->delete();
                }
            }

            //delete all reviews for this room
            $reviews = RoomReview::where('room_id', $room->id)->get();
            if (!is_null($reviews)) {
                foreach ($reviews as $review) {
                    $review->delete();
                }
            }


            // finally, delete this room
            $room->delete();
        }

        // finally, delete this hotel
        $hotel->delete();

        Session::flash('success', __('Hotel deleted successfully') . '!');

        return redirect()->back();
    }
    public function bulkDelete(Request $request)
    {

        $ids = $request->ids;

        foreach ($ids as $id) {
            $hotel = Hotel::findOrFail($id);

            //delete all the contents of this hotel
            $contents = $hotel->hotel_contents()->get();

            foreach ($contents as $content) {
                $content->delete();
            }

            //delete all the holidays  of this hotel
            $holidays = $hotel->holidays()->get();

            foreach ($holidays as $holiday) {
                $holiday->delete();
            }

            //delete  the feature of this hotel
            $hotel->hotel_feature()->delete();


            if (!is_null($hotel->logo)) {   
                @unlink(public_path('assets/img/hotel/logo/') . $hotel->logo);
            }

            //delete all the images of this hotel
            $galleries = $hotel->hotel_galleries()->get();

            foreach ($galleries as $gallery) {
                @unlink(public_path('assets/img/hotel/hotel-gallery/') . $gallery->image);
                $gallery->delete();
            }

            $rooms = $hotel->room()->get();
            foreach ($rooms as $room) {

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

            // finally, delete this hotel
            $hotel->delete();
        }

        Session::flash('success', __('Hotel deleted successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }
}
