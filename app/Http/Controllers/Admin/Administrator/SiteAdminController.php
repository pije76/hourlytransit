<?php

namespace App\Http\Controllers\Admin\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Helpers\UploadFile;
use App\Http\Requests\Admin\StoreRequest;
use App\Http\Requests\Admin\UpdateRequest;
use App\Models\Admin;
use App\Models\Language;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SiteAdminController extends Controller
{
    public function index()
    {
        $information['roles'] = RolePermission::all();

        $admins = Admin::query()->where('role_id', '!=', NULL)->get();

        $admins->map(function ($admin) {
            $role = $admin->role()->first();
            $admin['roleName'] = $role->name;
        });

        $information['admins'] = $admins;

        return view('admin.administrator.site-admin.index', $information);
    }

    public function store(StoreRequest $request)
    {
        $imageName = UploadFile::store(public_path('assets/img/admins/'), $request->file('image'));
        $language = Language::query()->where('is_default', 1)->first();

        $lang_code = 'admin_' . $language->code;
        $code =  $language->code;


        Admin::query()->create($request->except('image', 'password') + [
            'image' => $imageName,
            'lang_code' => $lang_code,
            'code' => $code,
            'image' => $imageName,
            'password' => Hash::make($request->password)
        ]);

        Session::flash('success', __('New admin added successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $admin = Admin::query()->find($id);

        if ($request->status == 1) {
            $admin->update(['status' => 1]);
        } else {
            $admin->update(['status' => 0]);
        }

        Session::flash('success', __('Status updated successfully') . '!');

        return redirect()->back();
    }

    public function update(UpdateRequest $request)
    {
        $admin = Admin::query()->find($request->id);

        if ($request->hasFile('image')) {
            $imageName = UploadFile::update(public_path('assets/img/admins/'), $request->file('image'), $admin->image);
        }

        $admin->update($request->except('image') + [
            'image' => $request->hasFile('image') ? $imageName : $admin->image
        ]);

        Session::flash('success', __('Admin updated successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $admin = Admin::query()->find($id);

        // delete admin profile picture
        @unlink(public_path('assets/img/admins/') . $admin->image);

        $admin->delete();

        return redirect()->back()->with('success',  __('Admin deleted successfully') . '!');
    }
}
