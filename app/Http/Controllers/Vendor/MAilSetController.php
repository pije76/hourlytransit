<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class MAilSetController extends Controller
{
    public function mailToVendor()
    {
        $data = DB::table('vendors')->where('id', Auth::guard('vendor')->user()->id)->select('to_mail')->first();

        return view('vendors.mail-to-vendor', ['data' => $data]);
    }

    public function updateMailToVendor(Request $request)
    {
        $rule = [
            'to_mail' => 'required'
        ];

        $message = [
            'to_mail.required' => __('The mail address field is required.')
        ];

        $validator = Validator::make($request->all(), $rule, $message);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        DB::table('vendors')->where('id', Auth::guard('vendor')->user()->id)->update(
            ['to_mail' => $request->to_mail]
        );

        Session::flash('success', __('Mail info updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
