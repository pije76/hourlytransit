<?php

namespace App\Http\Controllers\Admin\RoomManagement;

use App\Http\Controllers\Controller;
use App\Models\AdditionalService;
use App\Models\AdditionalServiceContent;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Language;
use App\Models\Room;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdditionalServiceController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->firstOrFail();
        $information['language'] = $language;

        $information['services'] = AdditionalService::join('additional_service_contents', 'additional_services.id', '=', 'additional_service_contents.additional_service_id')
            ->Where('additional_service_contents.language_id', $language->id)
            ->select('additional_services.id as id', 'additional_services.status', 'additional_service_contents.title', 'additional_services.serial_number')
            ->orderby('additional_services.id','desc')
            ->get();

        $information['langs'] = Language::all();

        return view('admin.room-management.additional-service.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'status' => 'required',
            'serial_number' => 'required|numeric'
        ];


        $languages = Language::all();

        foreach ($languages as $lan) {
            $rules[$lan->code . '_title'] = 'required|unique:additional_service_contents,title';
            $message[$lan->code . '_title.required'] = 'The name field is required for ' . $lan->name . ' language.';
            $message[$lan->code . '_title.unique'] = 'The name field must be unique for ' . $lan->name . ' language.';
        }

        $validator = Validator::make($request->all(), $rules, $message);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }
        $in = $request->all();

        $service = AdditionalService::create($in);


        foreach ($languages as $lang) {

            AdditionalServiceContent::create([
                'language_id' => $lang->id,
                'additional_service_id' => $service->id,
                'title' => $request[$lang->code . '_title'],
            ]);
        }

        Session::flash('success', __('New service added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {

        $rules = [
            'status' => 'required',
            'serial_number' => 'required|numeric'
        ];
        $languages = Language::all();
        $message = [];
        foreach ($languages as $lan) {
            $rules[$lan->code . '_title'] = 'required';
            $message[$lan->code . '_title.required'] =
            __('The title field is required for') . ' ' . $lan->name . ' ' . __('language') . '.';
        }

        $validator = Validator::make($request->all(), $rules, $message);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $service = AdditionalService::find($request->id);

        $in = $request->all();

        $service->update($in);

        foreach ($languages as $lang) {
            AdditionalServiceContent::updateOrCreate(
                [
                    'language_id' => $lang->id,
                    'additional_service_id' => $service->id,
                ],
                [
                    'title' => $request[$lang->code . '_title'],
                ]
            );
        }


        Session::flash('success', __('Additional Service updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {

        $service = AdditionalService::find($id);
        $serviceontents = AdditionalServiceContent::Where('additional_service_id', $service->id)->get();
        foreach ($serviceontents as $content) {
            $content->delete();
        }

        $rooms = Room::get();

        foreach ($rooms as $room) {
            $additionalService = json_decode($room->additional_service, true);

            if ($additionalService) {
                if (isset($additionalService[$id])) {
                    unset($additionalService[$id]);
                }
                $room->additional_service = json_encode($additionalService);

                $room->save();
            }
        }

        $bookings = Booking::get();

        foreach ($bookings as $booking) {
            $bookingService = explode(',', $booking->additional_service);

            if ($bookingService) {
                $updatedServices = array_diff($bookingService, [$id]);

                $booking->additional_service = implode(',', $updatedServices);

                $booking->save();
            }
        }



        $service->delete();

        return redirect()->back()->with('success',  __('Additional Service deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {

        $ids = $request->ids;

        $errorOccurred = false;

        foreach ($ids as $id) {
            $service = AdditionalService::find($id);
            $serviceontents = AdditionalServiceContent::Where('additional_service_id', $service->id)->get();
            foreach ($serviceontents as $content) {
                $content->delete();
            }
            $rooms = Room::get();

            foreach ($rooms as $room) {
                $additionalService = json_decode($room->additional_service, true);

                if ($additionalService) {
                    if (isset($additionalService[$id])) {
                        unset($additionalService[$id]);
                    }
                    $room->additional_service = json_encode($additionalService);

                    $room->save();
                }
            }

            $bookings = Booking::get();

            foreach ($bookings as $booking) {
                $bookingService = explode(',', $booking->additional_service);

                if ($bookingService) {
                    $updatedServices = array_diff($bookingService, [$id]);

                    $booking->additional_service = implode(',', $updatedServices);

                    $booking->save();
                }
            }

            $service->delete();
        }

        Session::flash('success', __('Additional Service deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
