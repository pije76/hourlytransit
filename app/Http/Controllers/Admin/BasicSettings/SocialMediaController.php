<?php

namespace App\Http\Controllers\Admin\BasicSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BasicSettings\SocialMedia;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;


class SocialMediaController extends Controller
{
    public function index()
    {
        $information['medias'] = SocialMedia::orderByDesc('id')->get();

        return view('admin.basic-settings.social-media.index', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'icon' => 'required',
            'url' => 'required|url',
            'serial_number' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        SocialMedia::create($request->all());

        Session::flash('success', __('New social media added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rules = [
            'url' => 'required|url',
            'serial_number' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        SocialMedia::find($request->id)->update($request->all());

        Session::flash('success', __('Social media updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        SocialMedia::find($id)->delete();

        return redirect()->back()->with('success',  __('Social media deleted successfully') . '!');
    }
}
