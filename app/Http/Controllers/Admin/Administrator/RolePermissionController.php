<?php

namespace App\Http\Controllers\Admin\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RolePermission;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = RolePermission::query()->orderByDesc('id')->get();

        return view('admin.administrator.role-permission.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $rule = ['name' => 'required'];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        RolePermission::query()->create($request->all());

        Session::flash('success', __('New role added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function permissions($id)
    {
        $role = RolePermission::query()->findOrFail($id);

        return view('admin.administrator.role-permission.permissions', compact('role'));
    }

    public function updatePermissions(Request $request, $id)
    {
        $role = RolePermission::query()->find($id);

        $role->update([
            'permissions' => json_encode($request->permissions)
        ]);

        Session::flash('success', __('Permissions updated successfully') . '!');

        return redirect()->back();
    }

    public function update(Request $request)
    {
        $rule = ['name' => 'required'];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $role = RolePermission::query()->find($request->id);

        $role->update($request->all());

        Session::flash('success', __('Role updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $role = RolePermission::query()->find($id);

        if ($role->adminInfo()->count() > 0) {
            return redirect()->back()->with('warning',  __('Advertisementdeletedsuccessfully') . '!');
        } else {
            $role->delete();

            return redirect()->back()->with('success',  __('Role deleted successfully') . '!');
        }
    }
}
