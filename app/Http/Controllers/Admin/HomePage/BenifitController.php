<?php

namespace App\Http\Controllers\Admin\HomePage;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\HomePage\Benifit;
use App\Models\HomePage\BenifitSection;
use App\Models\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BenifitController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        $information['benifits'] = $language->benifits()->orderByDesc('id')->get();

        $information['langs'] = Language::all();

        return view('admin.home-page.benifit.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'title' => 'required',
            'background_image' => [
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
        $imgName = UploadFile::store(public_path('assets/img/benifits/'), $request->file('background_image'));

        Benifit::query()->create($request->except('background_image') + [
            'background_image' => $imgName
        ]);

        Session::flash('success', __('New benifit added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rule = [
            'background_image' => $request->hasFile('background_image') ? new ImageMimeTypeRule() : '',
            'title' => 'required'
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $benifit = Benifit::query()->find($request['id']);

        if ($request->hasFile('background_image')) {
            $newImage = $request->file('background_image');
            $oldImage = $benifit->background_image;
            $imgName = UploadFile::update(public_path('assets/img/benifits/'), $newImage, $oldImage);
        }

        $benifit->update($request->except('background_image') + [
            'background_image' => $request->hasFile('background_image') ? $imgName : $benifit->background_image
        ]);

        Session::flash('success', __('Benifit updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $benifit = Benifit::query()->find($id);

        @unlink(public_path('assets/img/benifits/') . $benifit->background_image);

        $benifit->delete();

        return redirect()->back()->with('success',  __('Benifit deleted successfully') . '!');
    }
}
