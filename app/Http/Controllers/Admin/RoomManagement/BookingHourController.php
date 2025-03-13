<?php

namespace App\Http\Controllers\Admin\RoomManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingHour;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookingHourController extends Controller
{
    public function index()
    {
        $information['hours'] =  BookingHour::orderBy('serial_number', 'asc')->get();
        return view('admin.room-management.booking-hours.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'hour' => [
                'required',
                'integer',
                'between:1,24',
                Rule::unique('booking_hours'),
            ],

            'serial_number' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $in = $request->all();

        BookingHour::create($in);

        Session::flash('success', __('New Hour added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rules = [

            'hour' => [
                'required',
                'integer',
                'between:1,24',
                Rule::unique('booking_hours')->ignore($request->id, 'id')
            ],
            'serial_number' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $category = BookingHour::find($request->id);
        $in = $request->all();
        $category->update($in);

        Session::flash('success', __('Booking Hour updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {

        $hour = BookingHour::find($id);
        $prices = $hour->prices()->get();

        foreach ($prices as $price) {
            $price->delete();
        }

        $hour->delete();

        return redirect()->back()->with('success',  __('Hour deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {

        $ids = $request->ids;

        foreach ($ids as $id) {
            $hour = BookingHour::find($id);

            $prices = $hour->prices()->get();

            foreach ($prices as $price) {
                $price->delete();
            }

            $hour->delete();
        }

        Session::flash('success', __('Hour deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
