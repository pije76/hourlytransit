<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CouponRequest;
use App\Models\Language;
use App\Models\Room;
use App\Models\RoomCoupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Response;

class CouponController extends Controller
{
    public function index()
    {
        // get the coupons from db
        $information['coupons'] = RoomCoupon::where('vendor_id', Auth::guard('vendor')->user()->id)->orderByDesc('id')->get();

        // also, get the currency information from db
        $information['currencyInfo'] = $this->getCurrencyInfo();

        $language = Language::where('is_default', 1)->first();

        $information['rooms'] = Room::where('vendor_id', Auth::guard('vendor')->user()->id)->get();

        $information['rooms']->map(function ($room) use ($language) {
            $room['title'] = $room->room_content()->where('language_id', $language->id)->pluck('title')->first();
        });

        return view('vendors.room.coupon.index', $information);
    }

    public function store(CouponRequest $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        if ($request->filled('rooms')) {
            $rooms = $request->rooms;
        }

        RoomCoupon::create($request->except('start_date', 'end_date', 'rooms') + [
            'start_date' => date_format($startDate, 'Y-m-d'),
            'end_date' => date_format($endDate, 'Y-m-d'),
            'vendor_id' => Auth::guard('vendor')->user()->id,
            'rooms' => isset($rooms) ? json_encode($rooms) : null
        ]);

        Session::flash('success', __('New coupon added successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function update(CouponRequest $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        if ($request->filled('rooms')) {
            $rooms = $request->rooms;
        }

        RoomCoupon::where('id', $request->id)->first()->update($request->except('start_date', 'end_date', 'rooms') + [
            'start_date' => date_format($startDate, 'Y-m-d'),
            'end_date' => date_format($endDate, 'Y-m-d'),
            'vendor_id' => Auth::guard('vendor')->user()->id,
            'rooms' => isset($rooms) ? json_encode($rooms) : null
        ]);

        session()->flash('success', 'Coupon updated successfully!');

        return response()->json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        RoomCoupon::find($id)->delete();

        return redirect()->back()->with('success',  __('Coupon deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            RoomCoupon::find($id)->delete();
        }
        Session::flash('success', __('Coupon deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
