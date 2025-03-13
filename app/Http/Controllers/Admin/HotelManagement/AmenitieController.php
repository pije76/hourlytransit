<?php

namespace App\Http\Controllers\Admin\HotelManagement;

use App\Http\Controllers\Controller;
use App\Models\Amenitie;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\HotelContent;
use App\Models\RoomContent;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class AmenitieController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['amenities'] = $language->amenitieInfo()->orderByDesc('id')->get();
        $information['langs'] = Language::all();
        $information['language'] = $language;

        return view('admin.hotel-management.amenitie.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'icon' => 'required',
            'title' => [
                'required',
                Rule::unique('amenities')->where(function ($query) use ($request) {

                    return $query->where('language_id', $request->input('language_id'));
                }),
                'max:255',
            ],
        ];

        $message = [
            'language_id.required' => __('The language field is required.')
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }
        Amenitie::query()->create($request->except('language'));

        Session::flash('success', __('Amenitie stored successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => [
                'required',
                Rule::unique('amenities')->where(function ($query) use ($request) {
                    return $query->where('language_id', $request->input('language_id'));
                })->ignore($request->id, 'id'),
                'max:255',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $aminiteInfo = Amenitie::query()->find($request->id);

        $aminiteInfo->update($request->except('language'));

        Session::flash('success', __('Aminite updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {

        $hotel = HotelContent::select('amenities')->get();
        $data = json_decode($hotel, true);
        $found = false;

        foreach ($data as $item) {

            $amenities = json_decode($item['amenities']);

            if (in_array($id, $amenities)) {
                $found = true;
                break;
            }
        }
        if ($found) {
            return redirect()->back()->with('warning',  __('First delete all the hotel of this Amenitie') . '!');
        } else {

            $room = RoomContent::select('amenities')->get();
            $roomData = json_decode($room, true);
            $found2 = false;

            foreach ($roomData as $item2) {

                $roomAmenities = json_decode($item2['amenities']);

                if (in_array($id, $roomAmenities)) {
                    $found2 = true;
                    break;
                }
            }

            if ($found2) {
                return redirect()->back()->with('warning',  __('First delete all the room of this Amenitie') . '!');
            } else {

                $aminiteInfo = Amenitie::query()->find($id);
                $aminiteInfo->delete();
                return redirect()->back()->with('success',  __('Amenitie deleted successfully') . '!');
            }
        }
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request['ids'];

        $hotel = HotelContent::select('amenities')->get();
        $data = json_decode($hotel, true);
        $found = false;
        $errorOccurred = false;
        $errorOccurred2 = false;

        foreach ($ids as $id) {
            $found = false;

            foreach ($data as $item) {

                $aminities = json_decode($item['amenities']);
                if (in_array($id, $aminities)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $errorOccurred = true;
                break;
            } else {
                $room = RoomContent::select('amenities')->get();
                $roomData = json_decode($room, true);
                $found2 = false;

                foreach ($roomData as $item2) {

                    $roomAmenities = json_decode($item2['amenities']);

                    if (in_array($id, $roomAmenities)) {
                        $found2 = true;
                        break;
                    }
                }
                if ($found2) {
                    $errorOccurred2 = true;
                    break;
                } else {

                    $aminiteInfo = Amenitie::query()->find($id);
                    $aminiteInfo->delete();
                }
            }
        }
        if ($errorOccurred == true) {
            Session::flash('warning', __('First delete all the hotel of these Amenities') . '!');
        } elseif ($errorOccurred2 == true) {
            Session::flash('warning', __('First delete all the room of these Amenities') . '!');
        } else {
            Session::flash('success', __('Selected Informations deleted successfully') . '!');
        }
        return Response::json(['status' => 'success'], 200);
    }
}
