<?php

namespace App\Http\Controllers\Admin\RoomManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomCategory;
use App\Models\Language;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoomCategoryController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->firstOrFail();
        $information['language'] = $language;
        $information['categories'] = $language->roomCategories()->orderByDesc('id')->get();

        $information['langs'] = Language::all();
        
        return view('admin.room-management.room-categories.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'status' => 'required',
            'name' => [
                'required',
                Rule::unique('room_categories')->where(function ($query) use ($request) {
                    return $query->where('language_id', $request->input('language_id'));
                }),
                'max:255',
            ],
            'serial_number' => 'required|numeric'
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

        $in = $request->all();
        $in['slug'] = createSlug($request->name);

        RoomCategory::create($in);

        Session::flash('success', __('New category added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                Rule::unique('room_categories')->where(function ($query) use ($request) {
                    return $query->where('language_id', $request->input('language_id'));
                })->ignore($request->id, 'id'),
                'max:255',
            ],
            'serial_number' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $category = RoomCategory::find($request->id);

        $in = $request->all();
        $in['slug'] = createSlug($request->name);


        $category->update($in);

        Session::flash('success', __('Category updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {

        $category = RoomCategory::find($id);
        $contents = $category->room_category()->get();

        if (count($contents) > 0) {
            return redirect()->back()->with('warning',  __('First delete all the room of this category') . '!');
        } else {
            $category->delete();

            return redirect()->back()->with('success',  __('Category deleted successfully') . '!');
        }
    }

    public function bulkDestroy(Request $request)
    {

        $ids = $request->ids;

        $errorOccurred = false;

        foreach ($ids as $id) {
            $category = RoomCategory::find($id);
            $contents = $category->room_category()->get();

            if (count($contents) > 0) {
                $errorOccurred = true;
                break;
            } else {
                $category->delete();
            }
        }

        if ($errorOccurred == true) {
            Session::flash('warning', __('First delete all the room of these categories') . '!');
        } else {
            Session::flash('success', __('Room categories deleted successfully') . '!');
        }

        return Response::json(['status' => 'success'], 200);
    }
}
