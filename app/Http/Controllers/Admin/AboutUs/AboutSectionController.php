<?php

namespace App\Http\Controllers\Admin\AboutUs;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\BasicSettings\AboutUs;
use App\Models\BasicSettings\Basic;
use App\Models\HomePage\CustomSection;
use App\Models\HomePage\Section;
use App\Models\HomePage\SectionContent;
use App\Models\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AboutSectionController extends Controller
{
    public function about_us(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $information['langs'] = Language::all();

        $information['images']  = Basic::select(
            'about_section_image',
            'counter_section_image'
        )->first();

        $information['data'] = AboutUs::query()->where('language_id', $language->id)->first();
        $information['counterdata'] = SectionContent::where('language_id', $language->id)->first();

        return view('admin.about-us.about-us', $information);
    }

    public function updateImage(Request $request)
    {

        $data = DB::table('basic_settings')->select('about_section_image', 'counter_section_image')->first();

        $rules = [];

        if (!$request->filled('about_section_image') && is_null($data->about_section_image)) {
            $rules['about_section_image'] = 'required';
        }
        if (!$request->filled('counter_section_image') && is_null($data->counter_section_image)) {
            $rules['counter_section_image'] = 'required';
        }

        if ($request->hasFile('about_section_image')) {
            $rules['about_section_image'] = new ImageMimeTypeRule();
        }
        if ($request->hasFile('counter_section_image')) {
            $rules['counter_section_image'] = new ImageMimeTypeRule();
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        if ($request->hasFile('about_section_image')) {
            $newImage = $request->file('about_section_image');
            if (!empty($data->about_section_image)) {
                @unlink(public_path('assets/img/homepage/') . $data->about_section_image);
                $imageName = UploadFile::update(public_path('assets/img/homepage/'), $newImage, $data->about_section_image);
            } else {
                $imageName = UploadFile::store(public_path('assets/img/homepage/'), $newImage);
            }

            DB::table('basic_settings')->updateOrInsert(
                ['uniqid' => 12345],
                ['about_section_image' => $imageName]
            );
        }

        if ($request->hasFile('counter_section_image')) {
            $newImage = $request->file('counter_section_image');
            if (!empty($data->counter_section_image)) {
                @unlink(public_path('assets/img/homepage/') . $data->counter_section_image);
                $imageName = UploadFile::update(public_path('assets/img/homepage/'), $newImage, $data->counter_section_image);
            } else {
                $imageName = UploadFile::store(public_path('assets/img/homepage/'), $newImage);
            }

            DB::table('basic_settings')->updateOrInsert(
                ['uniqid' => 12345],
                ['counter_section_image' => $imageName]
            );
        }
        Session::flash('success', __('Image updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function updateAboutUs(Request $request)
    {
        $rules = [
            'title' => 'max:255',
            'subtitle' => 'max:255',
            'button_text' => 'max:255',
            'button_url' => 'max:255',
        ];

        if ($request->button_text != null) {
            $rules['button_url'] = 'required:max:255';
        }
        if ($request->counter_section_video_link) {
            $rules['counter_section_video_link'] = 'max:255';
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $language = Language::where('code', $request->language)->firstOrFail();
        $aboutUs = AboutUs::where('language_id', $language->id)->first();
        $counter = SectionContent::where('language_id', $language->id)->first();


        if (!empty($aboutUs)) {
            $aboutUs->update($request->except('language_id') + [
                'language_id' => $language->id,
            ]);
        } else {
            AboutUs::create($request->except('language_id') + [
                'language_id' => $language->id,
            ]);
        }

        if (!empty($counter)) {
            $counter->update([
                'counter_section_video_link' => $request->counter_section_video_link,
            ]);
        } else {
            SectionContent::create([
                'language_id' => $language->id,
                'counter_section_video_link' => $request->counter_section_video_link,
            ]);
        }

        Session::flash('success', __('About us section updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function customizeSection()
    {
        $aboutSec = Section::select('about_section_status', 'about_testimonial_section_status', 'about_features_section_status', 'about_custom_section_status', 'about_counter_section_status')->first();
        $customSectons = CustomSection::where('page_type', 'about')->get();
        return view('admin.about-us.section-customization', compact('aboutSec', 'customSectons'));
    }

    public function customizeUpdate(Request $request)
    {
        $section =  Section::first();

        $section->about_section_status = $request->about_section_status;
        $section->about_features_section_status = $request->about_features_section_status;
        $section->about_counter_section_status = $request->about_counter_section_status;
        $section->about_testimonial_section_status = $request->about_testimonial_section_status;
        $section->about_custom_section_status = $request->about_custom_section_status;
        $section->save();

        return redirect()->back()->with('success', 'Section update successfully!');
    }
}
