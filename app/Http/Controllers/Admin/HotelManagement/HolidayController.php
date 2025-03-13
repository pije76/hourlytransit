<?php

namespace App\Http\Controllers\Admin\HotelManagement;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Holiday;
use App\Models\Hotel;
use App\Models\Language;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->vendor_id == 'admin') {
            $vendor_id = 0;
        } else {
            $vendor_id = $request->vendor_id;
        }

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();

        if (is_numeric($vendor_id) && (int)$vendor_id == $vendor_id) {
            if ($vendor_id != 0) {
                $current_package = VendorPermissionHelper::packagePermission($vendor_id);
                if ($current_package == '[]') {
                    return redirect()->route('admin.hotel_management.hotel.holiday', [
                        'language'  => $language->code,
                        'vendor_id' => 'admin'
                    ]);
                }
            }
            $vendors = Vendor::join('memberships', 'vendors.id', '=', 'memberships.vendor_id')
                ->where([
                    ['memberships.status', '=', 1],
                    ['memberships.start_date', '<=', Carbon::now()->format('Y-m-d')],
                    ['memberships.expire_date', '>=', Carbon::now()->format('Y-m-d')]
                ])
                ->select('vendors.id', 'vendors.username')
                ->get();

            $hotels = Hotel::join('hotel_contents', 'hotels.id', '=', 'hotel_contents.hotel_id')
                ->Where('hotels.vendor_id', $vendor_id)
                ->Where('hotel_contents.language_id', $language->id)
                ->select('hotels.id', 'hotel_contents.title')
                ->get();


            $globalHoliday = Holiday::join('hotel_contents', 'holidays.hotel_id', '=', 'hotel_contents.hotel_id')
                ->where('holidays.vendor_id', $vendor_id)
                ->where('hotel_contents.language_id', $language->id)
                ->select('holidays.id', 'holidays.date', 'hotel_contents.title', 'hotel_contents.slug', 'hotel_contents.hotel_id')
                ->get();


            return view('admin.hotel-management.holiday.index', compact('globalHoliday', 'vendors', 'hotels'));
        } else {
            return redirect()->route('admin.hotel_management.hotel.holiday', [
                'language'  => $language->code,
                'vendor_id' => 'admin'
            ]);
        }
    }

    public function store(Request $request)
    {
        if ($request->vendor_id == 'admin') {
            $vendor_id = 0;
        } else {
            $vendor_id = $request->vendor_id;
        }

        $rules = [
            'date' => 'required',
            'hotel_id' => 'required',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(
                [
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                400
            );
        }

        $holiday = Holiday::where('vendor_id', $vendor_id)->pluck('date')->toArray();
        $date = date('Y-m-d', strtotime($request->date));

        if (in_array($date, $holiday)) {
            Session::flash('warning', __('The date exists in the holiday list') . '!');
            return Response::json(['status' => 'success'], 200);
        } else {
            Holiday::create([
                'date' => $date,
                'vendor_id' => $vendor_id,
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
