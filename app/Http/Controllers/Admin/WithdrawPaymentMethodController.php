<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdraw\WithdrawPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WithdrawPaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $information = [];
        $collection =  WithdrawPaymentMethod::get();
        $information['collection'] = $collection;
        return view('admin.withdraw.index', $information);
    }
    //store
    public function store(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                Rule::unique('withdraw_payment_methods'),
                'max:255',
            ],
            'min_limit' => 'required|numeric',
            'max_limit' => 'required|numeric|gt:min_limit',
            'fixed_charge' => 'nullable|numeric',
            'percentage_charge' => 'nullable|numeric',
            'status' => 'required|in:0,1',
        ];

        $fixed_charge = $request->fixed_charge;
        $percentage = $request->percentage_charge;
        $min_limit = $request->min_limit;

        $percentage_balance = (($request->min_limit - $fixed_charge) * $percentage) / 100;
        $total_charge = $percentage_balance + $fixed_charge;


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        if ($total_charge >= $min_limit) {
            return response()->json(['error' => "Minimum limit amount must be more then Fixed charge"], 400);
        } else {

            WithdrawPaymentMethod::create($request->all());
        }

        Session::flash('success', __('New Withdraw Payment Method Added successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                Rule::unique('withdraw_payment_methods')->ignore($request->id, 'id'),
                'max:255',
            ],
            'min_limit' => 'required|numeric',
            'max_limit' => 'required|numeric|gt:min_limit',
            'fixed_charge' => 'nullable|numeric',
            'percentage_charge' => 'nullable|numeric',
            'status' => 'required|in:0,1',
        ];

        $fixed_charge = $request->fixed_charge;
        $percentage = $request->percentage_charge;
        $min_limit = $request->min_limit;

        $percentage_balance = (($request->min_limit - $fixed_charge) * $percentage) / 100;
        $total_charge = $percentage_balance + $fixed_charge;


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        if ($total_charge >= $min_limit) {
            return response()->json(['error' => "Minimum limit amount must be more then Fixed charge"], 400);
        } else {
            WithdrawPaymentMethod::where('id', $request->id)->first()->update($request->all());
        }
        Session::flash('success', __('Update Withdraw Payment Method successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
    public function destroy($id)
    {
        WithdrawPaymentMethod::where('id', $id)->first()->delete();

        return redirect()->back()->with('success',  __('Withdraw Payment Method deleted successfully') . '!');
    }
}
