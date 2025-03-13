<?php

namespace App\Http\Controllers\Admin\HotelManagement\FeaturedHotel;

use App\Http\Controllers\Controller;
use App\Models\FeaturedHotelCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class ChargeController extends Controller
{
    public function index()
    {
        $information['charges'] = FeaturedHotelCharge::orderBy('id', 'desc')->get();

        return view('admin.hotel-management.featured-hotel.charge.index', $information);
    }
    public function store(Request $request)
    {
        $rules = [
            'price' => 'required',
            'days' => 'required',
        ];

        $message = [
            'price.required' => 'The price field is required.',
            'days.required' => 'The days field is required.'
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }
        FeaturedHotelCharge::query()->create($request->except('language'));

        Session::flash('success', __('Charge stored successfully') . '!');
        

        return Response::json(['status' => 'success'], 200);
    }
    public function update(Request $request)
    {
        $rules = [
            'price' => 'required',
            'days' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $chargeInfo = FeaturedHotelCharge::query()->find($request->id);

        $chargeInfo->update([
            'price' => $request->price,
            'days' => $request->days,
        ]);

        Session::flash('success', __('Charge updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function destroy($id)
    {

        $charge = FeaturedHotelCharge::query()->find($id);

        $charge->delete();

        return redirect()->back()->with('success',  __('Charge deleted successfully') . '!');
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request['ids'];

        foreach ($ids as $id) {
            $charge = FeaturedHotelCharge::query()->find($id);

            $charge->delete();
        }

        Session::flash('success', __('Selected Informations deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
