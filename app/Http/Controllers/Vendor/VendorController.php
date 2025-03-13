<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\Admin;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Language;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Room;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorInfo;
use App\Rules\MatchEmailRule;
use App\Rules\MatchOldPasswordRule;
use Carbon\Carbon;
use Config;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\App;

class VendorController extends Controller
{
    //signup
    public function setLocaleAdmin(Request $request)
    {
        App::setLocale($request->code);
        $vendor_id = Auth::guard('vendor')->user()->id;
        $vendor = Vendor::find($vendor_id);
        $vendor->lang_code = 'admin_' . $request->code;
        $vendor->code =  $request->code;
        $vendor->save();


        return $request->code;
    }

    public function signup()
    {
        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        } else {
            $misc = new MiscellaneousController();

            $language = $misc->getLanguage();

            $information['seoInfo'] = $language->seoInfo()->select('meta_keywords_vendor_signup', 'meta_description_vendor_signup')->first();

            $information['pageHeading'] = $misc->getPageHeading($language);

            $information['recaptchaInfo'] = Basic::select('google_recaptcha_status')->first();

            $information['bgImg'] = $misc->getBreadcrumb();

            return view('frontend.vendor.auth.register', $information);
        }
    }
    //create
    public function create(Request $request)
    {
        $admin = Admin::select('username')->first();
        $admin_username = $admin->username;
        $rules = [
            'username' => "required|unique:vendors|not_in:$admin_username",
            'email' => 'required|email|unique:vendors',
            'password' => 'required|confirmed|min:6',
        ];
        $messages = [];

        $info = Basic::select('google_recaptcha_status')->first();
        if ($info->google_recaptcha_status == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $messages = [
            'username.required' => __('The username field is required.'),
            'username.unique' => __('This username is already taken.'),
            'username.not_in' => __('The username cannot be the same as the admin username.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('Please provide a valid email address.'),
            'email.unique' => __('This email is already registered.'),
            'password.required' => __('The password field is required.'),
            'password.confirmed' => __('The password confirmation does not match.'),
            'password.min' => __('The password must be at least :min characters.'),
        ];

        if ($info->google_recaptcha_status == 1) {
            $messages['g-recaptcha-response.required'] = __('Please verify that you are not a robot.');
            $messages['g-recaptcha-response.captcha'] = __('Captcha error! Try again later or contact site admin.');
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        if ($request->username == 'admin') {
            Session::flash('username_error', __('You can not use admin as a username') . '!');
            return redirect()->back();
        }


        $in = $request->all();
        $setting = DB::table('basic_settings')->where('uniqid', 12345)->select('vendor_email_verification', 'vendor_admin_approval')->first();

        if ($setting->vendor_email_verification == 1) {
            // first, get the mail template information from db
            $mailTemplate = MailTemplate::where('mail_type', 'verify_email')->first();

            $mailSubject = $mailTemplate->mail_subject;
            $mailBody = $mailTemplate->mail_body;

            // second, send a password reset link to user via email
            $info = DB::table('basic_settings')
                ->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
                ->first();

            $token =  $request->email;

            $link = '<a href=' . url("vendor/email/verify?token=" . $token) . '>Click Here</a>';

            $mailBody = str_replace('{username}', $request->username, $mailBody);
            $mailBody = str_replace('{verification_link}', $link, $mailBody);
            $mailBody = str_replace('{website_title}', $info->website_title, $mailBody);

            $data = [
                'subject' => $mailSubject,
                'to' => $request->email,
                'body' => $mailBody,
            ];

            // if smtp status == 1, then set some value for PHPMailer
            if ($info->smtp_status == 1) {
                try {
                    $smtp = [
                        'transport' => 'smtp',
                        'host' => $info->smtp_host,
                        'port' => $info->smtp_port,
                        'encryption' => $info->encryption,
                        'username' => $info->smtp_username,
                        'password' => $info->smtp_password,
                        'timeout' => null,
                        'auth_mode' => null,
                    ];
                    Config::set('mail.mailers.smtp', $smtp);
                } catch (\Exception $e) {
                    Session::flash('error', $e->getMessage());
                    return back();
                }

                // finally add other informations and send the mail
                try {
                    if ($info->smtp_status == 1) {
                        Mail::send([], [], function (Message $message) use ($data, $info) {
                            $fromMail = $info->from_mail;
                            $fromName = $info->from_name;
                            $message->to($data['to'])
                                ->subject($data['subject'])
                                ->from($fromMail, $fromName)
                                ->html($data['body'], 'text/html');
                        });
                    }

                    Session::flash('success', __('A verification mail has been sent to your email address') . '!');
                } catch (\Exception $e) {
                    Session::flash('warning', __('Mail could not be sent') . '!');
                    return redirect()->back();
                }
            } else {
                $in['email_verified_at'] = now();
            }

            $in['status'] = 0;
        } else {
            $in['email_verified_at'] = now();
            Session::flash('success', __('Sign up successfully completed.Please Login Now') . '!');
        }
        if ($setting->vendor_admin_approval == 1) {
            $in['status'] = 0;
        }

        if ($setting->vendor_admin_approval == 0 && $setting->vendor_email_verification == 0) {
            $in['status'] = 1;
        }

        $in['password'] = Hash::make($request->password);

        $language = Language::query()->where('is_default', 1)->first();
        $in['lang_code'] = 'admin_ ' . $language->code;
        $in['code'] =  $language->code;
        $in['to_mail'] =  $request->email;

        $vendor = Vendor::create($in);

        $misc = new MiscellaneousController();

        $language = $misc->getLanguage();

        $in['language_id'] = $language->id;
        $in['vendor_id'] = $vendor->id;
        VendorInfo::create($in);

        return redirect()->route('vendor.login');
    }

    //login
    public function login(Request $request)
    {

        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        } else {

            $misc = new MiscellaneousController();

            $language = $misc->getLanguage();

            $information['seoInfo'] = $language->seoInfo()->select('meta_keywords_vendor_login', 'meta_description_vendor_login')->first();

            $information['pageHeading'] = $misc->getPageHeading($language);

            $information['bgImg'] = $misc->getBreadcrumb();

            $information['bs'] = Basic::query()->select('google_recaptcha_status', 'facebook_login_status', 'google_login_status')->first();

            if ($request->redirectPath == 'buy_plan') {
                Session::put('redirectTo', 'buy_plan');
            }
            if ($request->package) {
                Session::put('package', $request->package);
            }

            return view('frontend.vendor.auth.login', $information);
        }
    }

    //authenticate
    public function authentication(Request $request)
    {
        if ($request->session()->has('redirectTo')) {
            $redirectURL = $request->session()->get('redirectTo');
        } else {
            $redirectURL = null;
        }
        if ($request->session()->has('package')) {
            $packageIds = $request->session()->get('package');
        } else {
            $packageIds = null;
        }

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $info = Basic::select('google_recaptcha_status')->first();
        if ($info->google_recaptcha_status == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $messages = [];

        if ($info->google_recaptcha_status == 1) {
            $messages['g-recaptcha-response.required'] = 'Please verify that you are not a robot.';
            $messages['g-recaptcha-response.captcha'] = 'Captcha error! try again later or contact site admin.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }


        if (Auth::guard('vendor')->attempt([
            'username' => $request->username,
            'password' => $request->password
        ])) {
            $authAdmin = Auth::guard('vendor')->user();


            $setting = DB::table('basic_settings')->where('uniqid', 12345)->select('vendor_email_verification', 'vendor_admin_approval')->first();

            // check whether the vendor's account is active or not
            if ($setting->vendor_email_verification == 1 && $authAdmin->email_verified_at == NULL) {
                Session::flash('error', __('Please verify your email address') . '!');

                // logout auth vendor as condition not satisfied
                Auth::guard('vendor')->logout();

                return redirect()->back();
            } elseif ($setting->vendor_email_verification == 0 && $setting->vendor_admin_approval == 1) {
                Session::put('secret_login', 0);
                if ($redirectURL == 'buy_plan') {
                    if ($packageIds) {
                        $request->session()->forget('redirectTo');
                        $request->session()->forget('package');
                        return redirect()->route('vendor.plan.extend.checkout', ['package_id' => $packageIds]);
                    } else {
                        $request->session()->forget('redirectTo');
                        $request->session()->forget('package');
                        return redirect()->route('vendor.plan.extend.index');
                    }
                } else {
                    $request->session()->forget('redirectTo');
                    $request->session()->forget('package');
                    return redirect()->route('vendor.dashboard');
                }
            } else {
                Session::put('secret_login', 0);
                if ($redirectURL == 'buy_plan') {
                    if ($packageIds) {
                        $request->session()->forget('redirectTo');
                        $request->session()->forget('package');
                        return redirect()->route('vendor.plan.extend.checkout', ['package_id' => $packageIds]);
                    } else {
                        $request->session()->forget('redirectTo');
                        $request->session()->forget('package');
                        return redirect()->route('vendor.plan.extend.index');
                    }
                } else {
                    $request->session()->forget('redirectTo');
                    $request->session()->forget('package');
                    return redirect()->route('vendor.dashboard');
                }
            }
        } else {
            return redirect()->back()->with('error', 'Incorrect username or password');
        }
    }
    //confirm_email'
    public function confirm_email()
    {
        $email = request()->input('token');
        $user = Vendor::where('email', $email)->first();
        $user->email_verified_at = now();
        $setting = DB::table('basic_settings')->where('uniqid', 12345)->select('vendor_admin_approval')->first();
        if ($setting->vendor_admin_approval != 1) {
            $user->status = 1;
        }
        $user->save();
        Auth::guard('vendor')->login($user);
        return redirect()->route('vendor.dashboard');
    }
    public function logout(Request $request)
    {
        Auth::guard('vendor')->logout();
        Session::forget('secret_login');
        return redirect()->route('vendor.login');
    }

    public function dashboard()
    {
        $vendor_id = Auth::guard('vendor')->user()->id;
        $information['totalRooms'] = Room::query()->where('vendor_id', $vendor_id)->count();

        $information['admin_setting'] = DB::table('basic_settings')->where('uniqid', 12345)->select('vendor_admin_approval', 'admin_approval_notice')->first();

        $support_status = DB::table('support_ticket_statuses')->first();
        if ($support_status->support_ticket_status == 'active') {
            $total_support_tickets = SupportTicket::where([['user_id', Auth::guard('vendor')->user()->id], ['user_type', 'vendor']])->get()->count();
            $information['total_support_tickets'] = $total_support_tickets;
        }
        $information['support_status'] = $support_status;
        $information['admin_setting'] = DB::table('basic_settings')->where('uniqid', 12345)->select('vendor_admin_approval', 'admin_approval_notice')->first();

        //total room posts
        $totalRooms = DB::table('rooms')
            ->select(DB::raw('month(created_at) as month'), DB::raw('count(id) as total'))
            ->groupBy('month')
            ->where('vendor_id', $vendor_id)
            ->whereYear('created_at', '=', date('Y'))
            ->get();
        //total room visitors
        $totalVisitors = DB::table('visitors')
            ->select(DB::raw('month(date) as month'), DB::raw('count(id) as total'))
            ->groupBy('month')
            ->where('vendor_id', $vendor_id)
            ->whereYear('date', '=', date('Y'))
            ->get();

        $months = [];
        $totalRoomArr = [];
        $totalVisitorArr = [];


        //event icome calculation
        for ($i = 1; $i <= 12; $i++) {
            // get all 12 months name
            $monthNum = $i;
            $dateObj = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('M');
            array_push($months, $monthName);

            // get all 12 months's room posts
            $carFound = false;
            foreach ($totalRooms as $totalRoom) {
                if ($totalRoom->month == $i) {
                    $carFound = true;
                    array_push($totalRoomArr, $totalRoom->total);
                    break;
                }
            }
            if ($carFound == false) {
                array_push($totalRoomArr, 0);
            }

            // get all 12 months's visitors
            $visitorFound = false;
            foreach ($totalVisitors as $totalVisitor) {
                if ($totalVisitor->month == $i) {
                    $visitorFound = true;
                    array_push($totalVisitorArr, $totalVisitor->total);
                    break;
                }
            }
            if ($visitorFound == false) {
                array_push($totalVisitorArr, 0);
            }
        }

        $payment_logs = Membership::where('vendor_id', $vendor_id)->get()->count();

        //package start
        $nextPackageCount = Membership::query()->where([
            ['vendor_id', Auth::guard('vendor')->user()->id],
            ['expire_date', '>=', Carbon::now()->toDateString()]
        ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->count();
        //current package
        $information['current_membership'] = Membership::query()->where([
            ['vendor_id', Auth::guard('vendor')->user()->id],
            ['start_date', '<=', Carbon::now()->toDateString()],
            ['expire_date', '>=', Carbon::now()->toDateString()]
        ])->where('status', 1)->whereYear('start_date', '<>', '9999')->first();
        if ($information['current_membership'] != null) {
            $countCurrMem = Membership::query()->where([
                ['vendor_id', Auth::guard('vendor')->user()->id],
                ['start_date', '<=', Carbon::now()->toDateString()],
                ['expire_date', '>=', Carbon::now()->toDateString()]
            ])->where('status', 1)->whereYear('start_date', '<>', '9999')->count();
            if ($countCurrMem > 1) {
                $information['next_membership'] = Membership::query()->where([
                    ['vendor_id', Auth::guard('vendor')->user()->id],
                    ['start_date', '<=', Carbon::now()->toDateString()],
                    ['expire_date', '>=', Carbon::now()->toDateString()]
                ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999')->orderBy('id', 'DESC')->first();
            } else {
                $information['next_membership'] = Membership::query()->where([
                    ['vendor_id', Auth::guard('vendor')->user()->id],
                    ['start_date', '>', $information['current_membership']->expire_date]
                ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->first();
            }
            $information['next_package'] = $information['next_membership'] ? Package::query()->where('id', $information['next_membership']->package_id)->first() : null;
        } else {
            $information['next_package'] = null;
        }
        $information['current_package'] = $information['current_membership'] ? Package::query()->where('id', $information['current_membership']->package_id)->first() : null;
        $information['package_count'] = $nextPackageCount;
        //package start end

        $information['monthArr'] = $months;
        $information['totalRoomsArr'] = $totalRoomArr;
        $information['visitorArr'] = $totalVisitorArr;
        $information['payment_logs'] = $payment_logs;

        return view('vendors.index', $information);
    }

    //change_password
    public function change_password()
    {
        return view('frontend.vendor.auth.change-password');
    }

    //update_password
    public function updated_password(Request $request)
    {
        $rules = [
            'current_password' => [
                'required',
                new MatchOldPasswordRule('vendor')

            ],
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required'
        ];

        $messages = [
            'new_password.confirmed' => __('Password confirmation does not match'),
            'new_password_confirmation.required' => __('The confirm new password field is required.')
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $vendor = Auth::guard('vendor')->user();

        $vendor->update([
            'password' => Hash::make($request->new_password)
        ]);

        Session::flash('success', __('Password updated successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    //edit_profile
    public function editProfile()
    {
        $information = [];
        $misc = new MiscellaneousController();

        $language = $misc->getLanguage();

        $information['language'] = $language;
        $information['languages'] = Language::get();

        $vendor_id = Auth::guard('vendor')->user()->id;
        $information['vendor'] = Vendor::with('vendor_info')->where('id', $vendor_id)->first();
        return view('frontend.vendor.auth.edit-profile', $information);
    }
    //update_profile
    public function updateProfile(Request $request, Vendor $vendor)
    {
        $id = Auth::guard('vendor')->user()->id;
        $admin = Admin::select('username')->first();
        $vendor  = Vendor::where('id', $id)->first();
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
            $rules['photo'] = 'mimes:png,jpeg,jpg|dimensions:min_width=80,max_width=80,min_width=80,min_height=80';
        }

        $languages = Language::get();

        foreach ($languages as $language) {

            $vendor_id = Auth::guard('vendor')->user()->id;

            $hasExistingContent = VendorInfo::where('language_id', $language->id)->where('vendor_id', $vendor_id)->exists();
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

        Session::flash('success', __('Profile updated successfully') . '!');

        return Response::json(['status' => 'success'], 200);
    }

    public function changeTheme(Request $request)
    {
        $vendorInfo = Vendor::where('id', Auth::guard('vendor')->user()->id)->first();
        $vendorInfo->vendor_theme_version = $request->vendor_theme_version;
        $vendorInfo->save();
        return redirect()->back();
    }
    //forget_passord
    public function forget_passord()
    {
        $misc = new MiscellaneousController();

        $language = $misc->getLanguage();

        $information['seoInfo'] = $language->seoInfo()->select('meta_keywords_vendor_forget_password', 'meta_descriptions_vendor_forget_password')->first();

        $information['pageHeading'] = $misc->getPageHeading($language);

        $information['bgImg'] = $misc->getBreadcrumb();
        $information['bs'] = Basic::query()->select('google_recaptcha_status', 'facebook_login_status', 'google_login_status')->first();
        return view('frontend.vendor.auth.forget-password', $information);
    }
    //forget_mail
    public function forget_mail(Request $request)
    {
        $rules = [
            'email' => [
                'required',
                'email:rfc,dns',
                new MatchEmailRule('vendor')
            ]
        ];

        $messages = [];


        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Vendor::where('email', $request->email)->first();

        // first, get the mail template information from db
        $mailTemplate = MailTemplate::where('mail_type', 'reset_password')->first();
        $mailSubject = $mailTemplate->mail_subject;
        $mailBody = $mailTemplate->mail_body;

        // second, send a password reset link to user via email
        $info = DB::table('basic_settings')
            ->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
            ->first();

        $name = $user->username;
        $token =  Str::random(32);
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
        ]);

        $link = '<a href=' . url("vendor/reset-password?token=" . $token) . '>Click Here</a>';

        $mailBody = str_replace('{customer_name}', $name, $mailBody);
        $mailBody = str_replace('{password_reset_link}', $link, $mailBody);
        $mailBody = str_replace('{website_title}', $info->website_title, $mailBody);

        $data = [
            'to' => $request->email,
            'subject' => $mailSubject,
            'body' => $mailBody,
        ];

        // if smtp status == 1, then set some value for PHPMailer
        if ($info->smtp_status == 1) {
            try {
                $smtp = [
                    'transport' => 'smtp',
                    'host' => $info->smtp_host,
                    'port' => $info->smtp_port,
                    'encryption' => $info->encryption,
                    'username' => $info->smtp_username,
                    'password' => $info->smtp_password,
                    'timeout' => null,
                    'auth_mode' => null,
                ];
                Config::set('mail.mailers.smtp', $smtp);
            } catch (\Exception $e) {
                Session::flash('error', $e->getMessage());
                return back();
            }
        }

        // finally add other informations and send the mail
        try {
            if ($info->smtp_status == 1) {
                Mail::send([], [], function (Message $message) use ($data, $info) {
                    $fromMail = $info->from_mail;
                    $fromName = $info->from_name;
                    $message->to($data['to'])
                        ->subject($data['subject'])
                        ->from($fromMail, $fromName)
                        ->html($data['body'], 'text/html');
                });
            }

            Session::flash('success', __('A mail has been sent to your email address') . '!');
        } catch (\Exception $e) {
            Session::flash('error', __('Mail could not be sent') . '!');
        }

        // store user email in session to use it later
        $request->session()->put('userEmail', $user->email);
        return redirect()->back();
    }
    //reset_password
    public function reset_password()
    {
        $misc = new MiscellaneousController();

        $language = $misc->getLanguage();

        $information['seoInfo'] = $language->seoInfo()->select('meta_keywords_vendor_forget_password', 'meta_descriptions_vendor_forget_password')->first();

        $information['bgImg'] = $misc->getBreadcrumb();
        return view('frontend.vendor.auth.reset-password', $information);
    }
    //update_password
    public function update_password(Request $request)
    {
        $rules = [
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required'
        ];

        $messages = [
            'new_password.confirmed' => 'Password confirmation failed.',
            'new_password_confirmation.required' => 'The confirm new password field is required.'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $reset = DB::table('password_resets')->where('token', $request->token)->first();
        $email = $reset->email;

        $vendor = Vendor::where('email',  $email)->first();

        $vendor->update([
            'password' => Hash::make($request->new_password)
        ]);
        DB::table('password_resets')->where('token', $request->token)->delete();
        Session::flash('success', __('Reset Your Password Successfully Completed.Please Login Now') . '!');

        return redirect()->route('vendor.login');
    }
    public function subscriptionLog(Request $request)
    {
        $search = $request->search;
        $data['memberships'] = Membership::query()->when($search, function ($query, $search) {
            return $query->where('transaction_id', 'like', '%' . $search . '%');
        })
            ->where('vendor_id', Auth::guard('vendor')->user()->id)
            ->orderBy('id', 'DESC')
            ->paginate(10);
        return view('vendors.subscription-log', $data);
    }

    //transaction 
    public function transcation(Request $request)
    {
        $transcation_id = null;
        if ($request->filled('transcation_id')) {
            $transcation_id = $request->transcation_id;
        }

        $info['transcations'] = Transaction::Where('vendor_id', Auth::guard('vendor')->user()->id)->when($transcation_id, function ($query) use ($transcation_id) {
            return $query->where('transcation_id', 'like', '%' . $transcation_id . '%');
        })->orderByDesc('id')->paginate(10);

        return view('vendors.transcation', $info);
    }
}
