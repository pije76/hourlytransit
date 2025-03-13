<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Holiday;
use App\Models\Hotel;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $hotel_id = null;
        if (request()->filled('hotel_id')) {
            $hotel_id = $request->hotel_id;
            if ($hotel_id == "ALL") {
                $hotel_id = null;
            }
        }

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();

        $current_package = VendorPermissionHelper::packagePermission(Auth::guard('vendor')->user()->id);


        $hotels = Hotel::join('hotel_contents', 'hotels.id', '=', 'hotel_contents.hotel_id')
            ->Where('hotels.vendor_id', Auth::guard('vendor')->user()->id)
            ->Where('hotel_contents.language_id', $language->id)
            ->select('hotels.id', 'hotel_contents.title')
            ->get();

        $globalHoliday = Holiday::join('hotel_contents', 'holidays.hotel_id', '=', 'hotel_contents.hotel_id')
            ->where('holidays.vendor_id', Auth::guard('vendor')->user()->id)
            ->where('hotel_contents.language_id', $language->id)
            ->when($hotel_id, function ($query) use ($hotel_id) {
                return $query->where('holidays.hotel_id', $hotel_id);
            })
            ->select('holidays.id', 'holidays.date', 'hotel_contents.title', 'hotel_contents.slug', 'hotel_contents.hotel_id')
            ->get();


        return view('vendors.hotel.holiday.index', compact('globalHoliday', 'hotels'));
    }

    public function store(Request $request)
    {
        $vendorId = Auth::guard('vendor')->user()->id;
        $current_package = VendorPermissionHelper::packagePermission($vendorId);
        $defaultLang = Language::query()->where('is_default', 1)->first();

        if ($current_package == '[]') {
            Session::flash('warning', __('Please Buy a plan to add the holiday') . '!');
            return Response::json([
                'redirect' => route('vendor.hotel_management.hotel.holiday', ['language' =>$defaultLang->code])
            ], 200);

        }

        $rules = [
            'date' => 'required',
            'hotel_id' => 'required',
        ];
        $messages = [
            'date.required' => 'The date field is required',
            'hotel_id.required' => 'The hotel field is required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json(
                [
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                400
            );
        }

        $holiday = Holiday::where('vendor_id', Auth::guard('vendor')->user()->id)->pluck('date')->toArray();
        $date = date('Y-m-d', strtotime($request->date));

        if (in_array($date, $holiday)) {
            Session::flash('warning', __('The date exists in the holiday list') . '!');
            return Response::json(['status' => 'success'], 200);
        } else {
            Holiday::create([
                'date' => $date,
                'vendor_id' => Auth::guard('vendor')->user()->id,
                'hotel_id' => $request->hotel_id,
            ]);
            Session::flash('success', __('Holiday added successfully') . '!');

            return Response::json(['status' => 'success'], 200);
        }
    }

    public function destroy($id)
    {
        $UserStaffHoliday = Holiday::find($id);
        $UserStaffHoliday->delete();
        return redirect()->back()->with('success', __('Holiday delete successfully') . '!');
    }

    public function blukDestroy(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $UserStaffHoliday = Holiday::find($id);
            $UserStaffHoliday->delete();
        }

        Session::flash('success', __('Holiday delete successfully') . '!');
        return Response::json(['status' => 'success'], 200);
    }
}
