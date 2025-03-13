<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Package\PackageStoreRequest;
use App\Http\Requests\Package\PackageUpdateRequest;
use App\Models\BasicSettings\Basic;
use App\Models\Language;
use App\Models\Package;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function settings()
    {
        $data['abe'] = Basic::first();
        return view('admin.packages.settings', $data);
    }

    public function updateSettings(Request $request)
    {
        $rules = [
            'expiration_reminder' => 'required|numeric|min:0|max:2147483647'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $be = Basic::first();
        $be->expiration_reminder = $request->expiration_reminder;
        $be->save();

        Session::flash('success', 'Settings updated successfully!');
        return back();
    }
    /**
     * Display a listing of the resource.
     *
     *
     */

    public function index(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $search = $request->search;
        $data['bex'] = $currentLang->basic_extended;
        $data['packages'] = Package::query()->when($search, function ($query, $search) {
            return $query->where('title', 'like', '%' . $search . '%');
        })->orderBy('id', 'DESC')->get();
        return view('admin.packages.index', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     */

    public function store(PackageStoreRequest $request)
    {
        try {
            $features = json_encode($request->features);

            $in = $request->all();
            $in['features'] = $features;
            $in['slug'] = createSlug($request->title);

            Package::create($in);

            Session::flash('success', __('Package Created Successfully') . '!');
            return Response::json(['status' => 'success'], 200);
        } catch (\Throwable $e) {
            return $e;
        }
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return
     */
    public function edit($id)
    {
        try {
            if (session()->has('lang')) {
                $currentLang = Language::where('code', session()->get('lang'))->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }
            $data['bex'] = $currentLang->basic_extended;
            $data['package'] = Package::query()->findOrFail($id);
            return view("admin.packages.edit", $data);
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     */
    public function update(PackageUpdateRequest $request)
    {
        try {
            $features = json_encode($request->features);
            return DB::transaction(function () use ($request, $features) {
                Package::query()->findOrFail($request->package_id)
                    ->update($request->except('features') + [
                        'slug' => createSlug($request->title),
                        'features' => $features,
                    ]);
                Session::flash('success', __('Package Update Successfully') . '!');
                return Response::json(['status' => 'success'], 200);
            });
        } catch (\Throwable $e) {
            return $e;
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function delete(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $package = Package::query()->findOrFail($request->package_id);
                if ($package->memberships()->count() > 0) {
                    foreach ($package->memberships as $key => $membership) {
                        @unlink(public_path('assets/img/membership/receipt/') . $membership->receipt);
                        $membership->delete();
                    }
                }
                $package->delete();
                Session::flash('success', __('Package deleted successfully') . '!');
                return back();
            });
        } catch (\Throwable $e) {
            return $e;
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $ids = $request->ids;
                foreach ($ids as $id) {
                    $package = Package::query()->findOrFail($id);
                    if ($package->memberships()->count() > 0) {
                        foreach ($package->memberships as $key => $membership) {
                            @unlink(public_path('assets/img/membership/receipt/') . $membership->receipt);
                            $membership->delete();
                        }
                    }
                    $package->delete();
                }
                Session::flash('success', __('Package bulk deletion is successful') . '!');
                return response()->json(['status' => 'success'], 200);
            });
        } catch (\Throwable $e) {
            return $e;
        }
    }
}
