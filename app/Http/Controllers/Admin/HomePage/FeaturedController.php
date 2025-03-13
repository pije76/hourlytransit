<?php

namespace App\Http\Controllers\Admin\HomePage;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\HomePage\Feature;
use App\Models\HomePage\FeatureSection;
use App\Models\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\Facades\Purifier;

class FeaturedController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $information['features'] = $language->features()->orderByDesc('id')->get();

        $information['langs'] = Language::all();

        return view('admin.home-page.feature-section.index', $information);
    }

    public function addFeature(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'serial_number' => 'required',
            'title' => 'required|string|max:255',
            'image' => ['required', new ImageMimeTypeRule()],
            'subtitle' => 'max:255',
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
        $imgName = UploadFile::store(public_path('assets/img/feature/'), $request->file('image'));
        Feature::query()->create([
            'language_id' => $request->language_id,
            'title' => $request->title,
            'subtitle' =>  $request->subtitle,
            'serial_number' =>  $request->serial_number,
            'image' =>  $imgName,
        ]);

        Session::flash('success', __('Information created successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function editFeature(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'serial_number' => 'required',
            'subtitle' => 'max:255',
        ];
        if ($request->image) {
            $rules['image'] = new ImageMimeTypeRule();
        }

        $message = [
            'title.required' => 'The title field is required.'
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }


        $featureInfo = Feature::query()->find($request->id);
        if ($request->hasFile('image')) {
            $newImage = $request->file('image');
            $oldImage = $featureInfo->image;
            $imgName = UploadFile::update(public_path('assets/img/feature/'), $newImage, $oldImage);
        }


        $featureInfo->update($request->except('language', 'image') + [
            'image' => $request->hasFile('image') ? $imgName : $featureInfo->image
        ]);


        Session::flash('success', __('Information updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function destroy($id)
    {
        $featureInfo = Feature::query()->find($id);

        @unlink(public_path('assets/img/feature/') . $featureInfo->image);

        $featureInfo->delete();

        return redirect()->back()->with('success',  __('Information deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request['ids'];

        foreach ($ids as $id) {
            $featureInfo = Feature::query()->find($id);

            @unlink(public_path('assets/img/feature/') . $featureInfo->image);

            $featureInfo->delete();
        }

        Session::flash('success', __('Selected Informations deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
