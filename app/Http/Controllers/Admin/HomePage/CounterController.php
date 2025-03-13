<?php

namespace App\Http\Controllers\Admin\HomePage;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\CounterSection;
use App\Models\HomePage\CounterInformation;
use App\Models\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CounterController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['counters'] = $language->counterInfo()->orderByDesc('id')->get();
        $information['langs'] = Language::all();

        return view('admin.home-page.counter-section.index', $information);
    }
    public function aboutIndex(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['counters'] = $language->counterInfo()->orderByDesc('id')->get();
        $information['langs'] = Language::all();

        return view('admin.home-page.counter-section.about-index', $information);
    }

    public function storeCounter(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'serial_number' => 'required',
            'amount' => 'required|numeric',
            'title' => 'required',
            'image' => [
                'required',
                new ImageMimeTypeRule()
            ]
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
        $imgName = UploadFile::store(public_path('assets/img/counter/'), $request->file('image'));

        CounterInformation::query()->create($request->except('language', 'image') + [
            'image' => $request->hasFile('image') ? $imgName : NULL
        ]);

        Session::flash('success', __('Information stored successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function updateCounter(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric',
            'title' => 'required',
            'serial_number' => 'required'
        ];
        if ($request->image) {
            $rules['image'] = new ImageMimeTypeRule();
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }
        
        $counterInfo = CounterInformation::query()->find($request->id);
        if ($request->hasFile('image')) {
            $newImage = $request->file('image');
            $oldImage = $counterInfo->image;
            $imgName = UploadFile::update(public_path('assets/img/counter/'), $newImage, $oldImage);
        }

        $counterInfo->update($request->except('language', 'image') + [
            'image' => $request->hasFile('image') ? $imgName : $counterInfo->image
        ]);

        Session::flash('success', __('Information updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroyCounter($id)
    {
        $counterInfo = CounterInformation::query()->find($id);

        @unlink(public_path('assets/img/counter/') . $counterInfo->image);

        $counterInfo->delete();

        return redirect()->back()->with('success',  __('Information deleted successfully') . '!');
    }

    public function bulkDestroyCounter(Request $request)
    {
        $ids = $request['ids'];

        foreach ($ids as $id) {
            $counterInfo = CounterInformation::query()->find($id);
            @unlink(public_path('assets/img/counter/') . $counterInfo->image);
            $counterInfo->delete();
        }

        Session::flash('success', __('Selected Informations deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
