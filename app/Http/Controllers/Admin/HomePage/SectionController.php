<?php

namespace App\Http\Controllers\Admin\HomePage;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\HomePage\Section;
use App\Models\HomePage\SectionContent;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\UploadFile;
use App\Models\HomePage\CustomSection;
use Illuminate\Support\Facades\Validator;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SectionController extends Controller
{
    public function index()
    {
        $sectionInfo = Section::query()->first();

        $themeVersion = Basic::query()->pluck('theme_version')->first();
        $customSectons = CustomSection::where('page_type', 'home')->get();



        return view('admin.home-page.section-customization', compact('sectionInfo', 'themeVersion', 'customSectons'));
    }

    public function update(Request $request)
    {
        $sectionInfo = Section::query()->first();

        $sectionInfo->update($request->all());

        Session::flash('success', __('Section status updated successfully') . '!');

        return redirect()->back();
    }

    public function sectionContent(Request $request)
    {
        $Language = Language::where('code', $request->language)->firstorfail();
        $information['langs'] = Language::all();
        $Language_id = $Language->id;

        $information['images']  = Basic::select(
            'hero_section_image',
            'feature_section_image',
            'counter_section_image',
            'call_to_action_section_image',
            'call_to_action_section_inner_image',
            'testimonial_section_image'
        )->first();

        $information['data'] = SectionContent::where('language_id', $Language_id)->first();
        return view('admin.home-page.section-title', $information);
    }

    public function updateImages(Request $request)
    {
        // Retrieve existing image paths from 'basic_settings'
        $data = DB::table('basic_settings')->select(
            'hero_section_image',
            'feature_section_image',
            'counter_section_image',
            'call_to_action_section_image',
            'call_to_action_section_inner_image',
            'testimonial_section_image'
        )->first();

        // Define validation rules
        $rules = [];

        // Require specific images if not set in the database
        if (!$request->filled('hero_section_image') && is_null($data->hero_section_image)) {
            $rules['hero_section_image'] = 'required';
        }
        if (!$request->filled('feature_section_image') && is_null($data->feature_section_image)) {
            $rules['feature_section_image'] = 'required';
        }
        if (!$request->filled('counter_section_image') && is_null($data->counter_section_image)) {
            $rules['counter_section_image'] = 'required';
        }
        if (!$request->filled('call_to_action_section_image') && is_null($data->call_to_action_section_image)) {
            $rules['call_to_action_section_image'] = 'required';
        }
        if (!$request->filled('call_to_action_section_inner_image') && is_null($data->call_to_action_section_inner_image)) {
            $rules['call_to_action_section_inner_image'] = 'required';
        }
        if (!$request->filled('testimonial_section_image') && is_null($data->testimonial_section_image)) {
            $rules['testimonial_section_image'] = 'required';
        }

        // Apply custom image mime type rule for each image field if uploaded
        foreach (['hero_section_image', 'counter_section_image', 'call_to_action_section_image', 'call_to_action_section_inner_image', 'testimonial_section_image', 'feature_section_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $rules[$imageField] = new ImageMimeTypeRule();
            }
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        // Initialize an array to hold the paths of images to update
        $updatedImages = [];

        // Define the base directory for storing images
        $imageDir = public_path('assets/img/homepage/');

        // Create the directory if it does not exist
        if (!file_exists($imageDir)) {
            @mkdir($imageDir, 0777, true);
        }

        // Handle file upload and update each image in 'basic_settings'
        foreach (['hero_section_image', 'counter_section_image', 'call_to_action_section_image', 'call_to_action_section_inner_image', 'testimonial_section_image', 'feature_section_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $newImage = $request->file($imageField);
                $currentImage = $data->$imageField;

                // Delete old image if exists and upload new one
                if (!empty($currentImage)) {
                    @unlink($imageDir . $currentImage);
                    $imageName = UploadFile::update($imageDir, $newImage, $currentImage);
                } else {
                    $imageName = UploadFile::store($imageDir, $newImage);
                }

                $updatedImages[$imageField] = $imageName;
            }
        }

        // Update the 'basic_settings' table with new image paths
        DB::table('basic_settings')->updateOrInsert(
            ['uniqid' => 12345],
            $updatedImages
        );

        Session::flash('success', __('Images updated successfully') . '!');
        return response()->json(['status' => 'success'], 200);
    }


    public function updateTexts(Request $request)
    {

        $Language = Language::where('code', $request->language)->first();
        $information['languages'] = Language::all();
        $Language_id = $Language->id;

        $themeInfo = DB::table('basic_settings')->select('theme_version')->first();


        $content = SectionContent::where('Language_id', $Language_id)->first();

        $rules = [

            'hero_section_title' => in_array($themeInfo->theme_version, [1, 3])
                ? 'required|string|max:255'
                : '',
            'city_section_title' => 'required|max:255',
            'featured_section_title' => 'required|max:255',
            'featured_room_section_title' => 'required|max:255',
            'featured_room_section_button_text' => 'max:255',
            'counter_section_video_link' =>
            in_array($themeInfo->theme_version, [1, 2])
                ? 'max:255'
                : '',
            'call_to_action_section_title' => 'required|max:255',
            'call_to_action_button_url' => 'max:255',
            'call_to_action_section_btn' => 'max:255',
            'blog_section_title' => 'required|max:255',
            'blog_section_button_text' => 'required|max:255',
            'hero_section_subtitle' => 'max:255',
            'testimonial_section_title' => in_array($themeInfo->theme_version, [1, 2])
                ? 'required|string|max:255'
                : '',
            'benifit_section_title' => 'max:255',
            'testimonial_section_subtitle' => 'max:255',
            'testimonial_section_clients' => 'max:255',
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

        $fields = [
            'hero_section_title',
            'hero_section_subtitle',
            'city_section_title',
            'featured_section_title',
            'featured_section_text',
            'featured_room_section_title',
            'featured_room_section_button_text',
            'counter_section_video_link',
            'blog_section_title',
            'blog_section_button_text',
            'call_to_action_section_title',
            'call_to_action_button_url',
            'call_to_action_section_btn',
            'testimonial_section_title',
            'benifit_section_title',
            'testimonial_section_subtitle',
            'testimonial_section_clients',
        ];

        if (!empty($content)) {
            // Loop through the request data and update only the existing values
            $content->Language_id = $Language_id;
            
            // Update only fields that are present in the request
            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $content->{$field} = $request->{$field};
                }
            }

            $content->save();
        } else {
            $content = new SectionContent();
            $content->Language_id = $Language_id;

            // Same fields as above
            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $content->{$field} = $request->{$field};
                }
            }

            $content->save();
        }

        Session::flash('success', __('Images & Texts section updated successfully') . '!');
        return response()->json(['status' => 'success']);
    }
}
