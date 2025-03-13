<?php

namespace App\Http\Controllers\Admin\HomePage\Hero;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\BasicSettings\Basic;
use App\Models\HomePage\Hero\Slider;
use App\Models\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        $information['sliders'] = $language->sliderInfo()->orderByDesc('id')->get();

        $information['langs'] = Language::all();

        $information['basic'] = Basic::where('uniqid', 12345)->select('hero_section_video_url')->first();

        return view('admin.home-page.hero-section.slider-version.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'language_id' => 'required',
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

        // store image in storage
        $imgName = UploadFile::store(public_path('assets/img/hero/sliders/'), $request->file('image'));

        Slider::query()->create($request->except('image') + [
            'image' => $imgName
        ]);

        Session::flash('success', __('New slider added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rule = [
            'image' => $request->hasFile('image') ? new ImageMimeTypeRule() : '',
            'title' => 'required'
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $slider = Slider::query()->find($request['id']);

        if ($request->hasFile('image')) {
            $newImage = $request->file('image');
            $oldImage = $slider->image;
            $imgName = UploadFile::update(public_path('assets/img/hero/sliders/'), $newImage, $oldImage);
        }

        $slider->update($request->except('image') + [
            'image' => $request->hasFile('image') ? $imgName : $slider->image
        ]);

        Session::flash('success', __('Slider updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $slider = Slider::query()->find($id);

        $image_count = Slider::where('language_id', $slider->language_id)->get()->count();

        if ($image_count > 1) {
            @unlink(public_path('assets/img/hero/sliders/') . $slider->image);

            $slider->delete();

            return redirect()->back()->with('success',  __('Slider deleted successfully') . '!');
        } else {
            Session::flash('warning', __('You can\'t delete all images') . '!');
            return redirect()->back()->with('warning',  __('You can\'t delete all slider') . '!');
        }
    }
}
