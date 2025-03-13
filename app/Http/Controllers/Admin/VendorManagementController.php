<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MegaMailer;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Admin;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\Language;
use App\Models\Membership;
use App\Models\Package;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Room;
use App\Models\RoomReview;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorInfo;
use App\Models\Visitor;
use App\Rules\ImageMimeTypeRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VendorManagementController extends Controller
{
    public function settings()
    {
        $setting = DB::table('basic_settings')->where('uniqid', 12345)->select('vendor_email_verification', 'vendor_admin_approval', 'admin_approval_notice')->first();
        return view('admin.end-user.vendor.settings', compact('setting'));
    }
    //update_setting
    public function update_setting(Request $request)
    {
        if ($request->vendor_email_verification) {
            $vendor_email_verification = 1;
        } else {
            $vendor_email_verification = 0;
        }
        if ($request->vendor_admin_approval) {
            $vendor_admin_approval = 1;
        } else {
            $vendor_admin_approval = 0;
        }
        // finally, store the favicon into db
        DB::table('basic_settings')->updateOrInsert(
            ['uniqid' => 12345],
            [
                'vendor_email_verification' => $vendor_email_verification,
                'vendor_admin_approval' => $vendor_admin_approval,
                'admin_approval_notice' => $request->admin_approval_notice,
            ]
        );

        Session::flash('success', __('Update Settings Successfully') . '!');
        return back();
    }

    public function index(Request $request)
    {
        $searchKey = null;

        if ($request->filled('info')) {
            $searchKey = $request['info'];
        }

        $vendors = Vendor::when($searchKey, function ($query, $searchKey) {
            return $query->where('username', 'like', '%' . $searchKey . '%')
                ->orWhere('email', 'like', '%' . $searchKey . '%');
        })
            ->where('id', '!=', 0)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.end-user.vendor.index', compact('vendors'));
    }

    //add
    public function add(Request $request)
    {
        // first, get the language info from db
        $language = Language::query()->where('code', '=', $request->language)->first();
        $information['language'] = $language;
        $information['languages'] = Language::get();
        return view('admin.end-user.vendor.create', $information);
    }
    public function create(Request $request)
    {
        $admin = Admin::select('username')->first();
        $admin_username = $admin->username;
        $rules = [
            'username' => "required|unique:vendors|not_in:$admin_username",
            'email' => 'required|email|unique:vendors',
            'password' => 'required|min:6',
            'photo' => [
                'required',
                'dimensions:width=80,height=80',
                new ImageMimeTypeRule(),
            ],
        ];

        $languages = Language::get();
        foreach ($languages as $language) {

            $code = $language->code;

            if (
                $language->is_default == 1 ||
                $request->filled($code . '_name') ||
                $request->filled($code . '_country') ||
                $request->filled($code . '_city') ||
                $request->filled($code . '_state') ||
                $request->filled($code . '_zip_code') ||
                $request->filled($code . '_details') ||
                $request->filled($code . '_address')
            ) {
                $rules[$code . '_name'] = 'required|max:255';
            }
        }

        $messages = [];
        foreach ($languages as $language) {
            $messages[$language->code . '_name.required'] =
                __('The name field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $in = $request->all();
        $in['password'] = Hash::make($request->password);
        $in['to_mail'] =  $request->email;
        $in['status'] = 1;

        $file = $request->file('photo');
        if ($file) {
            $extension = $file->getClientOriginalExtension();
            $directory = public_path('assets/admin/img/vendor-photo/');
            $fileName = uniqid() . '.' . $extension;
            @mkdir($directory, 0775, true);
            $file->move($directory, $fileName);
            $in['photo'] = $fileName;
        }
        $in['email_verified_at'] = Carbon::now();

        $language = Language::query()->where('is_default', 1)->first();
        $in['lang_code'] = 'admin_ ' . $language->code;
        $in['code'] =  $language->code;

        $vendor = Vendor::create($in);
        $vendor_id = $vendor->id;
        foreach ($languages as $language) {
            $vendorInfo = new VendorInfo();
            if (
                $language->is_default == 1 ||
                $request->filled($language->code . '_name')
            ) {
                $vendorInfo->language_id = $language->id;
                $vendorInfo->vendor_id = $vendor_id;
                $vendorInfo->name = $request[$language->code . '_name'];
                $vendorInfo->country = $request[$language->code . '_country'];
                $vendorInfo->city = $request[$language->code . '_city'];
                $vendorInfo->state = $request[$language->code . '_state'];
                $vendorInfo->zip_code = $request[$language->code . '_zip_code'];
                $vendorInfo->address = $request[$language->code . '_address'];
                $vendorInfo->details = $request[$language->code . '_details'];
                $vendorInfo->save();
            }
        }

        Session::flash('success', __('Add Vendor Successfully') . '!');
        return Response::json(['status' => 'success'], 200);
    }

    public function show($id)
    {
        $information['langs'] = Language::all();
        $information['currencyInfo'] = $this->getCurrencyInfo();
        $information['onlineGateways'] = OnlineGateway::where('status', 1)->get();

        $information['offline_gateways'] = OfflineGateway::where('status', 1)->orderBy('serial_number', 'asc')->get();

        $stripe = OnlineGateway::where('keyword', 'stripe')->first();
        $stripe_info = json_decode($stripe->information, true);
        $information['stripe_key'] = $stripe_info['key'];

        $authorizenet = OnlineGateway::query()->whereKeyword('authorize.net')->first();
        $anetInfo = json_decode($authorizenet->information);

        if ($anetInfo->sandbox_check == 1) {
            $information['anetSource'] = 'https://jstest.authorize.net/v1/Accept.js';
        } else {
            $information['anetSource'] = 'https://js.authorize.net/v1/Accept.js';
        }

        $currency_info = $this->getCurrencyInfo();
        $information['currency_info'] = $currency_info;

        $language = Language::where('code', request()->input('language'))->firstOrFail();
        $information['language'] = $language;
        $language_id = $language->id;
        $vendor = Vendor::with([
            'vendor_info' => function ($query) use ($language) {
                return $query->where('language_id', $language->id);
            }
        ])->where('id', $id)->firstOrFail();
        $information['vendor'] = $vendor;
        $charges = FeaturedRoomCharge::orderBy('days')->get();
        $information['charges'] = $charges;

        $information['langs'] = Language::all();
        $information['packages'] = Package::query()->where('status', '1')->get();
        $online = OnlineGateway::query()->where('status', 1)->get();
        $offline = OfflineGateway::where('status', 1)->get();
        $information['gateways'] = $online->merge($offline);

        $information['rooms'] = Room::with([
            'room_content' => function ($q) use ($language_id) {
                $q->where('language_id', $language_id);
            },
            'vendor'
        ])
            ->Where('vendor_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.end-user.vendor.details', $information);
    }
    public function updateAccountStatus(Request $request, $id)
    {

        $user = Vendor::find($id);
        if ($request->account_status == 1) {
            $user->update(['status' => 1]);
        } else {
            $user->update(['status' => 0]);
        }
        Session::flash('success', __('Account status updated successfully') . '!');

        return redirect()->back();
    }

    public function updateEmailStatus(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        if ($request->email_status == 1) {
            $vendor->update(['email_verified_at' => now()]);
        } else {
            $vendor->update(['email_verified_at' => NULL]);
        }
        Session::flash('success', __('Email status updated successfully') . '!');

        return redirect()->back();
    }
    public function changePassword($id)
    {
        $userInfo = Vendor::findOrFail($id);

        return view('admin.end-user.vendor.change-password', compact('userInfo'));
    }
    public function updatePassword(Request $request, $id)
    {
        $rules = [
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required'
        ];

        $messages = [
            'new_password.confirmed' => __('Password confirmation does not match.'),
            'new_password_confirmation.required' => __('The confirm new password field is required.')
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $user = Vendor::find($id);

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        Session::flash('success', __('Password updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function edit($id)
    {
        $information['languages'] = Language::get();
        $vendor = Vendor::where('id', $id)->firstOrFail();
        $information['vendor'] = $vendor;
        $information['currencyInfo'] = $this->getCurrencyInfo();
        return view('admin.end-user.vendor.edit', $information);
    }

    //update
    public function update(Request $request, $id, Vendor $vendor)
    {
        $vendor  = Vendor::where('id', $id)->first();
        $admin = Admin::select('username')->first();
        $admin_username = $admin->username;
        $rules = [
            'username' => [
                'required',
                Rule::unique('vendors', 'username')->ignore($id),
                Rule::notIn([$admin_username]),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('vendors', 'email')->ignore($id)
            ]
        ];

        if (!$vendor->photo) {
            $rules['photo'] = [
                'required'
            ];
        }

        if ($request->hasFile('photo')) {
            $rules['photo'] = [
                'dimensions:width=80,height=80',
                new ImageMimeTypeRule(),
            ];
        }

        $languages = Language::get();
        foreach ($languages as $language) {

            $hasExistingContent = VendorInfo::where('language_id', $language->id)->where('vendor_id', $id)->exists();
            $code = $language->code;

            if (
                $hasExistingContent ||
                $language->is_default == 1 ||
                $request->filled($code . '_name') ||
                $request->filled($code . '_country') ||
                $request->filled($code . '_city') ||
                $request->filled($code . '_state') ||
                $request->filled($code . '_zip_code') ||
                $request->filled($code . '_details') ||
                $request->filled($code . '_address')
            ) {
                $rules[$code . '_name'] = 'required|max:255';
            }
        }

        $messages = [];

        foreach ($languages as $language) {
            $messages[$language->code . '_name.required'] =
                __('The name field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $in = $request->all();

        $file = $request->file('photo');
        if ($file) {
            $extension = $file->getClientOriginalExtension();
            $directory = public_path('assets/admin/img/vendor-photo/');
            $fileName = uniqid() . '.' . $extension;
            @mkdir($directory, 0775, true);
            $file->move($directory, $fileName);

            @unlink(public_path('assets/admin/img/vendor-photo/') . $vendor->photo);
            $in['photo'] = $fileName;
        }

        if ($request->show_email_addresss) {
            $in['show_email_addresss'] = 1;
        } else {
            $in['show_email_addresss'] = 0;
        }
        if ($request->show_phone_number) {
            $in['show_phone_number'] = 1;
        } else {
            $in['show_phone_number'] = 0;
        }
        if ($request->show_contact_form) {
            $in['show_contact_form'] = 1;
        } else {
            $in['show_contact_form'] = 0;
        }

        $vendor->update($in);

        $languages = Language::get();
        $vendor_id = $vendor->id;
        foreach ($languages as $language) {
            $code = $language->code;

            $vendorInfo = VendorInfo::where('vendor_id', $vendor_id)->where('language_id', $language->id)->first();
            if ($vendorInfo == NULL) {
                $vendorInfo = new VendorInfo();
            }
            if (
                $language->is_default == 1 ||
                $request->filled($code . '_name')
            ) {
                $vendorInfo->language_id = $language->id;
                $vendorInfo->vendor_id = $vendor_id;
                $vendorInfo->name = $request[$language->code . '_name'];
                $vendorInfo->country = $request[$language->code . '_country'];
                $vendorInfo->city = $request[$language->code . '_city'];
                $vendorInfo->state = $request[$language->code . '_state'];
                $vendorInfo->zip_code = $request[$language->code . '_zip_code'];
                $vendorInfo->address = $request[$language->code . '_address'];
                $vendorInfo->details = $request[$language->code . '_details'];
                $vendorInfo->save();
            }
        }
        Session::flash('success', __('Vendor updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }


    public function sendMail($memb, $package, $paymentMethod, $vendor, $bs, $mailType, $replacedPackage = NULL, $removedPackage = NULL)
    {

        if ($mailType != 'admin_removed_current_package' && $mailType != 'admin_removed_next_package') {
            $transaction_id = VendorPermissionHelper::uniqidReal(8);
            $activation = $memb->start_date;
            $expire = $memb->expire_date;
            $info['start_date'] = $activation->toFormattedDateString();
            $info['expire_date'] = $expire->toFormattedDateString();
            $info['payment_method'] = $paymentMethod;
            $lastMemb = $vendor->memberships()->orderBy('id', 'DESC')->first();

            $file_name = $this->makeInvoice($info, "membership", $vendor, NULL, $package->price, "Stripe", $vendor->phone, $bs->base_currency_symbol_position, $bs->base_currency_symbol, $bs->base_currency_text, $transaction_id, $package->title, $lastMemb);
        }

        $mailer = new MegaMailer();
        $data = [
            'toMail' => $vendor->email,
            'toName' => $vendor->username,
            'username' => $vendor->username,
            'website_title' => $bs->website_title,
            'templateType' => $mailType
        ];

        if ($mailType != 'admin_removed_current_package' && $mailType != 'admin_removed_next_package') {
            $data['package_title'] = $package->title;
            $data['package_price'] = ($bs->base_currency_text_position == 'left' ? $bs->base_currency_text . ' ' : '') . $package->price . ($bs->base_currency_text_position == 'right' ? ' ' . $bs->base_currency_text : '');
            $data['activation_date'] = $activation->toFormattedDateString();
            $data['expire_date'] = Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString();
            $data['membership_invoice'] = $file_name;
        }
        if ($mailType != 'admin_removed_current_package' || $mailType != 'admin_removed_next_package') {
            $data['removed_package_title'] = $removedPackage;
        }

        if (!empty($replacedPackage)) {
            $data['replaced_package'] = $replacedPackage;
        }

        $mailer->mailFromAdmin($data);
        @unlink(public_path('assets/front/invoices/' . $file_name));
    }

    public function addCurrPackage(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::where('id', $vendor_id)->first();
        $bs = Basic::first();

        $selectedPackage = Package::find($request->package_id);

        // calculate expire date for selected package
        if ($selectedPackage->term == 'monthly') {
            $exDate = Carbon::now()->addMonth()->format('d-m-Y');
        } elseif ($selectedPackage->term == 'yearly') {
            $exDate = Carbon::now()->addYear()->format('d-m-Y');
        } elseif ($selectedPackage->term == 'lifetime') {
            $exDate = Carbon::maxValue()->format('d-m-Y');
        }
        // store a new membership for selected package
        $selectedMemb = Membership::create([
            'price' => $selectedPackage->price,
            'currency' => $bs->base_currency_text,
            'currency_symbol' => $bs->base_currency_symbol,
            'payment_method' => $request->payment_method,
            'transaction_id' => uniqid(),
            'status' => 1,
            'receipt' => NULL,
            'transaction_details' => NULL,
            'settings' => null,
            'package_id' => $selectedPackage->id,
            'vendor_id' => $vendor_id,
            'start_date' => Carbon::parse(Carbon::now()->format('d-m-Y')),
            'expire_date' => Carbon::parse($exDate),
            'is_trial' => 0,
            'trial_days' => 0,
        ]);

        $this->sendMail($selectedMemb, $selectedPackage, $request->payment_method, $vendor, $bs, 'admin_added_current_package');

        Session::flash('success', __('Current Package has been added successfully') . '!');
        return response()->json(['status' => 'success'], 200);
    }


    public function changeCurrPackage(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::findOrFail($vendor_id);
        $currMembership = VendorPermissionHelper::currMembOrPending($vendor_id);
        $nextMembership = VendorPermissionHelper::nextMembership($vendor_id);

        $bs = Basic::first();

        $selectedPackage = Package::find($request->package_id);

        // if the vendor has a next package to activate & selected package is 'lifetime' package
        if (!empty($nextMembership) && $selectedPackage->term == 'lifetime') {
            Session::flash('warning', __('To add a Lifetime package as Current Package, You have to remove the next package'));
            return back();
        }

        // expire the current package
        $currMembership->expire_date = Carbon::parse(Carbon::now()->subDay()->format('d-m-Y'));
        $currMembership->modified = 1;
        if ($currMembership->status == 0) {
            $currMembership->status = 2;
        }
        $currMembership->save();

        // calculate expire date for selected package
        if ($selectedPackage->term == 'monthly') {
            $exDate = Carbon::now()->addMonth()->format('d-m-Y');
        } elseif ($selectedPackage->term == 'yearly') {
            $exDate = Carbon::now()->addYear()->format('d-m-Y');
        } elseif ($selectedPackage->term == 'lifetime') {
            $exDate = Carbon::maxValue()->format('d-m-Y');
        }
        // store a new membership for selected package
        $selectedMemb = Membership::create([
            'price' => $selectedPackage->price,
            'currency' => $bs->base_currency_text,
            'currency_symbol' => $bs->base_currency_symbol,
            'payment_method' => $request->payment_method,
            'transaction_id' => uniqid(),
            'status' => 1,
            'receipt' => NULL,
            'transaction_details' => NULL,
            'settings' => null,
            'package_id' => $selectedPackage->id,
            'vendor_id' => $vendor_id,
            'start_date' => Carbon::parse(Carbon::now()->format('d-m-Y')),
            'expire_date' => Carbon::parse($exDate),
            'is_trial' => 0,
            'trial_days' => 0,
        ]);

        // if the user has a next package to activate & selected package is not 'lifetime' package
        if (!empty($nextMembership) && $selectedPackage->term != 'lifetime') {
            $nextPackage = Package::find($nextMembership->package_id);

            // calculate & store next membership's start_date
            $nextMembership->start_date = Carbon::parse(Carbon::parse($exDate)->addDay()->format('d-m-Y'));

            // calculate & store expire date for next membership
            if ($nextPackage->term == 'monthly') {
                $exDate = Carbon::parse(Carbon::parse(Carbon::parse($exDate)->addDay()->format('d-m-Y'))->addMonth()->format('d-m-Y'));
            } elseif ($nextPackage->term == 'yearly') {
                $exDate = Carbon::parse(Carbon::parse(Carbon::parse($exDate)->addDay()->format('d-m-Y'))->addYear()->format('d-m-Y'));
            } else {
                $exDate = Carbon::parse(Carbon::maxValue()->format('d-m-Y'));
            }
            $nextMembership->expire_date = $exDate;
            $nextMembership->save();
        }

        $currentPackage = Package::select('title')->findOrFail($currMembership->package_id);
        $this->sendMail($selectedMemb, $selectedPackage, $request->payment_method, $vendor, $bs, 'admin_changed_current_package', $currentPackage->title);


        Session::flash('success', __('Current Package changed successfully') . '!');
        return back();
    }

    public function removeCurrPackage(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::where('id', $vendor_id)->firstOrFail();
        $currMembership = VendorPermissionHelper::currMembOrPending($vendor_id);
        $currPackage = Package::select('title')->findOrFail($currMembership->package_id);
        $nextMembership = VendorPermissionHelper::nextMembership($vendor_id);
        $bs = Basic::first();

        $today = Carbon::now();

        // just expire the current package
        $currMembership->expire_date = $today->subDay();
        $currMembership->modified = 1;
        if ($currMembership->status == 0) {
            $currMembership->status = 2;
        }
        $currMembership->save();

        // if next package exists
        if (!empty($nextMembership)) {
            $nextPackage = Package::find($nextMembership->package_id);

            $nextMembership->start_date = Carbon::parse(Carbon::today()->format('d-m-Y'));
            if ($nextPackage->term == 'monthly') {
                $nextMembership->expire_date = Carbon::parse(Carbon::today()->addMonth()->format('d-m-Y'));
            } elseif ($nextPackage->term == 'yearly') {
                $nextMembership->expire_date = Carbon::parse(Carbon::today()->addYear()->format('d-m-Y'));
            } elseif ($nextPackage->term == 'lifetime') {
                $nextMembership->expire_date = Carbon::parse(Carbon::maxValue()->format('d-m-Y'));
            }
            $nextMembership->save();
        }

        $this->sendMail(NULL, NULL, $request->payment_method, $vendor, $bs,  'admin_removed_current_package', NULL, $currPackage->title);

        Session::flash('success', __('Current Package removed successfully') . '!');
        return back();
    }

    public function addNextPackage(Request $request)
    {
        $vendor_id = $request->vendor_id;

        $hasPendingMemb = VendorPermissionHelper::hasPendingMembership($vendor_id);
        if ($hasPendingMemb) {
            Session::flash('warning', __('This user already has a Pending Package. Please take an action (change / remove / approve / reject) for that package first.'));
            return response()->json(['status' => 'success'], 200);
        }

        $currMembership = VendorPermissionHelper::userPackage($vendor_id);
        $currPackage = Package::find($currMembership->package_id);
        $vendor = Vendor::where('id', $vendor_id)->first();
        $bs = Basic::first();

        $selectedPackage = Package::find($request->package_id);

        if ($currMembership->is_trial == 1) {
            Session::flash('warning', 'If your current package is trial package, then you have to change / remove the current package first.');
            Session::flash('warning', __('If your current package is trial package, then you have to change / remove the current package first.'));
            return response()->json(['status' => 'success'], 200);
        }


        // if current package is not lifetime package
        if ($currPackage->term != 'lifetime') {
            // calculate expire date for selected package
            if ($selectedPackage->term == 'monthly') {
                $exDate = Carbon::parse($currMembership->expire_date)->addDay()->addMonth()->format('d-m-Y');
            } elseif ($selectedPackage->term == 'yearly') {
                $exDate = Carbon::parse($currMembership->expire_date)->addDay()->addYear()->format('d-m-Y');
            } elseif ($selectedPackage->term == 'lifetime') {
                $exDate = Carbon::parse(Carbon::maxValue()->format('d-m-Y'));
            }
            // store a new membership for selected package
            $selectedMemb = Membership::create([
                'price' => $selectedPackage->price,
                'currency' => $bs->base_currency_text,
                'currency_symbol' => $bs->base_currency_symbol,
                'payment_method' => $request->payment_method,
                'transaction_id' => uniqid(),
                'status' => 1,
                'receipt' => NULL,
                'transaction_details' => NULL,
                'settings' => null,
                'package_id' => $selectedPackage->id,
                'vendor_id' => $vendor_id,
                'start_date' => Carbon::parse(Carbon::parse($currMembership->expire_date)->addDay()->format('d-m-Y')),
                'expire_date' => Carbon::parse($exDate),
                'is_trial' => 0,
                'trial_days' => 0,
            ]);

            $this->sendMail($selectedMemb, $selectedPackage, $request->payment_method, $vendor, $bs, 'admin_added_next_package');
        } else {
            Session::flash('success', __('If your current package is lifetime package, then you have to change / remove the current package first.') . '!');
            return response()->json(['status' => 'success'], 200);
        }


        Session::flash('success', __('Next Package has been added successfully') . '!');
        return response()->json(['status' => 'success'], 200);
    }

    public function changeNextPackage(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::where('id', $vendor_id)->first();
        $bs = Basic::first();
        $nextMembership = VendorPermissionHelper::nextMembership($vendor_id);
        $nextPackage = Package::find($nextMembership->package_id);
        $selectedPackage = Package::find($request->package_id);

        $prevStartDate = $nextMembership->start_date;
        // set the start_date to unlimited
        $nextMembership->start_date = Carbon::parse(Carbon::maxValue()->format('d-m-Y'));
        $nextMembership->modified = 1;
        $nextMembership->save();

        // calculate expire date for selected package
        if ($selectedPackage->term == 'monthly') {
            $exDate = Carbon::parse($prevStartDate)->addMonth()->format('d-m-Y');
        } elseif ($selectedPackage->term == 'yearly') {
            $exDate = Carbon::parse($prevStartDate)->addYear()->format('d-m-Y');
        } elseif ($selectedPackage->term == 'lifetime') {
            $exDate = Carbon::parse(Carbon::maxValue()->format('d-m-Y'));
        }

        // store a new membership for selected package
        $selectedMemb = Membership::create([
            'price' => $selectedPackage->price,
            'currency' => $bs->base_currency_text,
            'currency_symbol' => $bs->base_currency_symbol,
            'payment_method' => $request->payment_method,
            'transaction_id' => uniqid(),
            'status' => 1,
            'receipt' => NULL,
            'transaction_details' => NULL,
            'settings' => json_encode($bs),
            'package_id' => $selectedPackage->id,
            'vendor_id' => $vendor_id,
            'start_date' => Carbon::parse($prevStartDate),
            'expire_date' => Carbon::parse($exDate),
            'is_trial' => 0,
            'trial_days' => 0,
        ]);

        $this->sendMail($selectedMemb, $selectedPackage, $request->payment_method, $vendor, $bs, 'admin_changed_next_package', $nextPackage->title);

        Session::flash('success', __('Next Package changed successfully') . '!');
        return back();
    }

    public function removeNextPackage(Request $request)
    {
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::where('id', $vendor_id)->first();
        $bs = Basic::first();
        $nextMembership = VendorPermissionHelper::nextMembership($vendor_id);
        // set the start_date to unlimited
        $nextMembership->start_date = Carbon::parse(Carbon::maxValue()->format('d-m-Y'));
        $nextMembership->modified = 1;
        $nextMembership->save();

        $nextPackage = Package::select('title')->findOrFail($nextMembership->package_id);


        $this->sendMail(NULL, NULL, $request->payment_method, $vendor, $bs, 'admin_removed_next_package', NULL, $nextPackage->title);

        Session::flash('success', __('Next Package removed successfully') . '!');
        return back();
    }

    //secrtet login
    public function secret_login($id)
    {
        Session::put('secret_login', 1);
        $vendor = Vendor::where('id', $id)->first();
        Auth::guard('vendor')->login($vendor);
        return redirect()->route('vendor.dashboard');
    }

    //update_vendor_balance
    public function update_vendor_balance(Request $request, $id)
    {
        $rules = [
            'amount_status' => 'required',
            'amount' => 'required_if:amount_status,1|numeric',
        ];
        $messages = [
            'amount_status.required' => 'Please select add or subtract',
            'amount' => 'Amount filed is required',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $currency_info = Basic::select('base_currency_symbol_position', 'base_currency_symbol')
            ->first();
        $vendor = Vendor::where('id', $id)->first();
        //add or subtract vendor balance
        if ($request->amount_status == 1) {
            //store data to transcation table
            $transaction = Transaction::create([
                'transcation_id' => time(),
                'booking_id' => NULL,
                'transcation_type' => 'balance_add',
                'user_id' => NULL,
                'vendor_id' => $vendor->id,
                'payment_status' => 1,
                'payment_method' => NULL,
                'grand_total' => $request->amount,
                'pre_balance' => $vendor->amount != 0 ? $vendor->amount : 0.00,
                'after_balance' => $vendor->amount + $request->amount,
                'gateway_type' => NULL,
                'currency_symbol' => $currency_info->base_currency_symbol,
                'currency_symbol_position' => $currency_info->base_currency_symbol_position,
            ]);

            $new_vendor_amount = $vendor->amount + $request->amount;
            $message = __('Balance Added successfully') . '!';
        } else {
            //store data to transcation table
            $transaction = Transaction::create([
                'transcation_id' => time(),
                'booking_id' => NULL,
                'transcation_type' => 'balance_subtract',
                'user_id' => NULL,
                'vendor_id' => $vendor->id,
                'payment_status' => 1,
                'payment_method' => NULL,
                'grand_total' => $request->amount,
                'pre_balance' => $vendor->amount != 0 ? $vendor->amount : 0.00,
                'after_balance' => $vendor->amount - $request->amount,
                'gateway_type' => NULL,
                'currency_symbol' => $currency_info->base_currency_symbol,
                'currency_symbol_position' => $currency_info->base_currency_symbol_position,
            ]);

            $new_vendor_amount = $vendor->amount - $request->amount;
            $message = __('Balance Subtract successfully') . '!';
        }

        $vendor->amount = $new_vendor_amount;
        $vendor->save();

        Session::flash('success', $message);

        return Response::json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        // vendor memeberships
        $memberships = $vendor->memberships()->get();
        foreach ($memberships as $membership) {
            @unlink(public_path('assets/img/membership/receipt/') . $membership->receipt);
            $membership->delete();
        }
        //vendor infos 
        $vendor_infos = $vendor->vendor_infos()->get();
        foreach ($vendor_infos as $info) {
            $info->delete();
        }
        //delete vendor hotels
        $hotels = $vendor->hotels()->get();
        foreach ($hotels as $hotel) {

            //delete all the contents of this hotel
            $contents = $hotel->hotel_contents()->get();

            foreach ($contents as $content) {
                $content->delete();
            }

            //delete all the holidays  of this hotel
            $holidays = $hotel->holidays()->get();

            foreach ($holidays as $holiday) {
                $holiday->delete();
            }

            //delete  the feature of this hotel
            $hotel->hotel_feature()->delete();

            // delete  logo image of this hotel

            if (!is_null($hotel->logo)) {
                @unlink(public_path('assets/img/hotel/logo/') . $hotel->logo);
            }

            //delete all the images of this hotel
            $galleries = $hotel->hotel_galleries()->get();

            foreach ($galleries as $gallery) {
                @unlink(public_path('assets/img/hotel/hotel-gallery/') . $gallery->image);
                $gallery->delete();
            }

            $rooms = $hotel->room()->get();
            foreach ($rooms as $room) {

                //delete all the contents of this room
                $contents = $room->room_content()->get();

                foreach ($contents as $content) {
                    $content->delete();
                }

                //delete  the feature of this room
                $room->room_feature()->delete();

                //delete all the price of this room
                $prices = $room->room_prices()->get();

                foreach ($prices as $price) {
                    $price->delete();
                }

                // delete feature_image of this room
                if (!is_null($room->feature_image)) {
                    @unlink(public_path('assets/img/room/featureImage/') . $room->feature_image);
                }


                //delete all the images of this room
                $room_galleries = $room->room_galleries()->get();

                foreach ($room_galleries as $gallery) {
                    @unlink(public_path('assets/img/room/room-gallery/') . $gallery->image);
                    $gallery->delete();
                }

                //delete all reviews for this room
                $reviews = RoomReview::where('room_id', $room->id)->get();
                if (!is_null($reviews)) {
                    foreach ($reviews as $review) {
                        $review->delete();
                    }
                }

                //delete all visit for this room
                $visitors  = Visitor::where('room_id', $room->id)->get();
                if (!is_null($visitors)) {
                    foreach ($visitors as $visitor) {
                        $visitor->delete();
                    }
                }

                // finally, delete this room
                $room->delete();
            }

            // finally, delete this hotel
            $hotel->delete();
        }
        //delete all vendor's support ticket
        $support_tickets = SupportTicket::where([['user_id', $vendor->id], ['user_type', 'vendor']])->get();
        foreach ($support_tickets as $support_ticket) {
            //delete conversation 
            $messages = $support_ticket->messages()->get();
            foreach ($messages as $message) {
                @unlink(public_path('assets/admin/img/support-ticket/' . $message->file));
                $message->delete();
            }
            @unlink(public_path('assets/admin/img/support-ticket/attachment/') . $support_ticket->attachment);
            $support_ticket->delete();
        }

        //finally delete the vendor
        @unlink(public_path('assets/admin/img/vendor-photo/') . $vendor->photo);
        $vendor->delete();

        return redirect()->back()->with('success',  __('Vendor info deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $vendor = Vendor::findOrFail($id);
            // vendor memeberships
            $memberships = $vendor->memberships()->get();
            foreach ($memberships as $membership) {
                @unlink(public_path('assets/img/membership/receipt/') . $membership->receipt);
                $membership->delete();
            }
            //vendor infos 
            $vendor_infos = $vendor->vendor_infos()->get();
            foreach ($vendor_infos as $info) {
                $info->delete();
            }
            //delete vendor hotels
            $hotels = $vendor->hotels()->get();
            foreach ($hotels as $hotel) {

                //delete all the contents of this hotel
                $contents = $hotel->hotel_contents()->get();

                foreach ($contents as $content) {
                    $content->delete();
                }

                //delete all the holidays  of this hotel
                $holidays = $hotel->holidays()->get();

                foreach ($holidays as $holiday) {
                    $holiday->delete();
                }

                //delete  the feature of this hotel
                $hotel->hotel_feature()->delete();

                // delete logo image of this hotel

                if (!is_null($hotel->logo)) {
                    @unlink(public_path('assets/img/hotel/logo/') . $hotel->logo);
                }

                //delete all the images of this hotel
                $galleries = $hotel->hotel_galleries()->get();

                foreach ($galleries as $gallery) {
                    @unlink(public_path('assets/img/hotel/hotel-gallery/') . $gallery->image);
                    $gallery->delete();
                }

                $rooms = $hotel->room()->get();
                foreach ($rooms as $room) {

                    //delete all the contents of this room
                    $contents = $room->room_content()->get();

                    foreach ($contents as $content) {
                        $content->delete();
                    }

                    //delete  the feature of this room
                    $room->room_feature()->delete();

                    //delete all the price of this room
                    $prices = $room->room_prices()->get();

                    foreach ($prices as $price) {
                        $price->delete();
                    }

                    // delete feature_image of this room
                    if (!is_null($room->feature_image)) {
                        @unlink(public_path('assets/img/room/featureImage/') . $room->feature_image);
                    }


                    //delete all the images of this room
                    $room_galleries = $room->room_galleries()->get();

                    foreach ($room_galleries as $gallery) {
                        @unlink(public_path('assets/img/room/room-gallery/') . $gallery->image);
                        $gallery->delete();
                    }

                    //delete all reviews for this room
                    $reviews = RoomReview::where('room_id', $room->id)->get();
                    if (!is_null($reviews)) {
                        foreach ($reviews as $review) {
                            $review->delete();
                        }
                    }
                    //delete all visit for this room
                    $visitors  = Visitor::where('room_id', $room->id)->get();
                    if (!is_null($visitors)) {
                        foreach ($visitors as $visitor) {
                            $visitor->delete();
                        }
                    }

                    // finally, delete this room
                    $room->delete();
                }

                // finally, delete this hotel
                $hotel->delete();
            }
            //delete all vendor's support ticket
            $support_tickets = SupportTicket::where([['user_id', $vendor->id], ['user_type', 'vendor']])->get();
            foreach ($support_tickets as $support_ticket) {
                //delete conversation 
                $messages = $support_ticket->messages()->get();
                foreach ($messages as $message) {
                    @unlink(public_path('assets/admin/img/support-ticket/' . $message->file));
                    $message->delete();
                }
                @unlink(public_path('assets/admin/img/support-ticket/attachment/') . $support_ticket->attachment);
                $support_ticket->delete();
            }

            //finally delete the vendor
            @unlink(public_path('assets/admin/img/vendor-photo/') . $vendor->photo);
            $vendor->delete();
        }
        Session::flash('success', __('Vendors info deleted successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }
}
