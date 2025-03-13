<?php

namespace App\Http\Controllers\Admin\HotelManagement\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Location\Country;
use App\Models\Location\State;
use Illuminate\Support\Facades\Session;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['countries'] = $language->countryInfo()->orderByDesc('id')->get();
        $information['states'] = $language->stateInfo()->orderByDesc('id')->get();
        $information['langs'] = Language::all();
        $information['language'] = $language;

        return view('admin.hotel-management.location.state.index', $information);
    }
    public function getCountry($language_id)
    {
        $countries = Country::where('language_id', $language_id)->get();
        $states = State::where('language_id', $language_id)->get();

        return response()->json([
            'status' => 'success',
            'countries' => $countries,
            'states' => $states
        ], 200);
    }

    public function store(Request $request)
    {
        $totalCountry = Country::Where('language_id', $request->m_language_id)->count();
        if ($totalCountry > 0) {
            $country = true;
        } else {
            $country = false;
        }

        $rules = [
            'm_language_id' => 'required',
            'name' => 'required',
            'country_id' => $country ? 'required' : '',
        ];

        $messages = [
            'm_language_id.required' => __('The language field is required.'),
            'country_id.required' => __('The country field is required.'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $state = new State();

        $state->language_id = $request->m_language_id;
        $state->name = $request->name;
        $state->country_id = $request->country_id;
        $state->save();

        Session::flash('success', __('State stored successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }


    public function update(Request $request)
    {
        $rules = [
            'name' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $aminiteInfo = State::query()->find($request->id);

        $aminiteInfo->update($request->except('language'));

        Session::flash('success', __('State updated successfully') . '!');


        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $state = State::query()->find($id);

        $city = $state->cities()->get();
        $contents = $state->hotel_state()->get();

        if (count($city) > 0) {
            return redirect()->back()->with('warning',  __('First delete all the cities of this State') . '!');
        } else {
            if (count($contents) > 0) {
                return redirect()->back()->with('warning',  __('First delete all the hotel of this State') . '!');
            } else {
                $state->delete();
                return redirect()->back()->with('success',  __('State deleted successfully') . '!');
            }
        }
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request['ids'];

        $errorOccurred = false;
        $errorOccurred2 = false;
        foreach ($ids as $id) {
            $state = State::query()->find($id);
            $city = $state->cities()->get();
            $contents = $state->hotel_state()->get();

            if (count($city) > 0) {

                $errorOccurred = true;
                break;
            } else {
                if (count($contents) > 0) {
                    $errorOccurred2 = true;
                    break;
                } else {
                    $state->delete();
                }
            }
        }

        if ($errorOccurred == true) {
            Session::flash('warning', __('First delete all the city of these State') . '!');
        } elseif ($errorOccurred2 == true) {
            Session::flash('warning', __('First delete all the hotel of these State') . '!');
        } else {
            Session::flash('success', __('Selected Informations deleted successfully') . '!');
        }
        return Response::json(['status' => 'success'], 200);
    }
}
