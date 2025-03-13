<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\BasicMailer;
use App\Http\Helpers\MegaMailer;
use App\Http\Helpers\UploadFile;
use App\Models\Admin;
use App\Models\BasicSettings\Basic;
use App\Models\Journal\Blog;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Room;
use App\Models\Subscriber;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use App\Rules\ImageMimeTypeRule;
use App\Rules\MatchEmailRule;
use App\Rules\MatchOldPasswordRule;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function authentication(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // get the username and password which has provided by the admin
        $credentials = $request->only('username', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $authAdmin = Auth::guard('admin')->user();

            // check whether the admin's account is active or not
            if ($authAdmin->status == 0) {
                Session::flash('alert', __('Sorry, your account has been deactivated') . '!');

                // logout auth admin as condition not satisfied
                Auth::guard('admin')->logout();

                return redirect()->back();
            } else {
                return redirect()->route('admin.dashboard');
            }
        } else {
            return redirect()->back()->with('alert',  __('Oops, username or password does not match') . '!');
        }
    }

    public function forgetPassword()
    {
        return view('admin.forget-password');
    }

    public function forgetPasswordMail(Request $request)
    {
        // validation start
        $rules = [
            'email' => [
                'required',
                'email:rfc,dns',
                new MatchEmailRule('admin')
            ]
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        // validation end

        // create a new password and store it in db
        $newPassword = uniqid();

        $admin = Admin::query()->where('email', '=', $request->email)->first();

        $admin->update([
            'password' => Hash::make($newPassword)
        ]);

        // prepare a mail to send newly created password to admin
        $mailData['subject'] = 'Reset Password';

        $mailData['body'] = 'Hi ' . $admin->first_name . ',<br/><br/>Your password has been reset. Your new password is: ' . $newPassword . '<br/>Now, you can login with your new password. You can change your password later.<br/><br/>Thank you.';

        $mailData['recipient'] = $admin->email;

        $mailData['sessionMessage'] = 'A mail has been sent to your email address.';

        BasicMailer::sendMail($mailData);

        return redirect()->back();
    }

    public function redirectToDashboard()
    {
        $information['authAdmin'] = Auth::guard('admin')->user();
        $information['totalBlog'] = Blog::query()->count();
        $information['totalUser'] = User::query()->count();
        $information['totalSubscriber'] = Subscriber::query()->count();
        $information['payment_log'] = Membership::where('vendor_id', '!=', 0)->count();
        $information['vendors'] = Vendor::where('id', '!=', 0)->get()->count();
        $information['totalRooms'] = Room::get()->count();

        //income of event bookings 
        $totalPurchases = DB::table('memberships')
            ->select(DB::raw('month(created_at) as month'), DB::raw('sum(price) as total'))
            ->where('status', '=', 1)
            ->groupBy('month')
            ->whereYear('created_at', '=', date('Y'))
            ->get();
        $totalUsers = DB::table('users')
            ->select(DB::raw('month(created_at) as month'), DB::raw('count(id) as total'))
            ->where('status', '=', 1)
            ->groupBy('month')
            ->whereYear('created_at', '=', date('Y'))
            ->get();


        $months = [];
        $packagePurchaseIncomes = [];
        $totalUsersArr = [];


        //event icome calculation
        for ($i = 1; $i <= 12; $i++) {
            // get all 12 months name
            $monthNum = $i;
            $dateObj = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('M');
            array_push($months, $monthName);

            // get all 12 months's income
            $purchaseFound = false;
            foreach ($totalPurchases as $totalPurchase) {
                if ($totalPurchase->month == $i) {
                    $purchaseFound = true;
                    array_push($packagePurchaseIncomes, $totalPurchase->total);
                    break;
                }
            }
            if ($purchaseFound == false) {
                array_push($packagePurchaseIncomes, 0);
            }

            // get all 12 months's income
            $userFound = false;
            foreach ($totalUsers as $totalUser) {
                if ($totalUser->month == $i) {
                    $userFound = true;
                    array_push($totalUsersArr, $totalUser->total);
                    break;
                }
            }
            if ($userFound == false) {
                array_push($totalUsersArr, 0);
            }
        }
        $information['monthArr'] = $months;
        $information['packagePurchaseIncomesArr'] = $packagePurchaseIncomes;
        $information['totalUsersArr'] = $totalUsersArr;
        $information['earning'] = Basic::first();
        $information['transcation_count'] = Transaction::get()->count();

        return view('admin.admin.dashboard', $information);
    }

    public function changeTheme(Request $request)
    {
        DB::table('basic_settings')->updateOrInsert(
            ['uniqid' => 12345],
            ['admin_theme_version' => $request->admin_theme_version]
        );

        return redirect()->back();
    }

    public function editProfile()
    {
        $adminInfo = Auth::guard('admin')->user();

        return view('admin.admin.edit-profile', compact('adminInfo'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $rules = [];

        if (is_null($admin->image)) {
            $rules['image'] = 'required';
        }
        if ($request->hasFile('image')) {
            $rules['image'] = new ImageMimeTypeRule();
        }

        $rules['username'] = [
            'required',
            Rule::unique('admins')->ignore($admin->id),
            Rule::unique('vendors')
        ];

        $rules['email'] = [
            'required',
            Rule::unique('admins')->ignore($admin->id)
        ];

        $rules['first_name'] = 'required';

        $rules['last_name'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        if ($request->hasFile('image')) {
            $newImg = $request->file('image');
            $oldImg = $admin->image;
            $imageName = UploadFile::update(public_path('assets/img/admins/'), $newImg, $oldImg);
        }

        if ($request->show_email_address) {
            $show_email_address = 1;
        } else {
            $show_email_address = 0;
        }
        if ($request->show_phone_number) {
            $show_phone_number = 1;
        } else {
            $show_phone_number = 0;
        }


        $admin->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'image' => $request->hasFile('image') ? $imageName : $admin->image,
            'username' => $request->username,
            'email' => $request->email,
            'show_email_address' => $show_email_address,
            'phone' => $request->phone,
            'show_phone_number' => $show_phone_number,
            'address' => $request->address,
            'details' => $request->details,
        ]);

        Session::flash('success', __('Profile updated successfully') . '!');


        return redirect()->back();
    }

    public function changePassword()
    {
        return view('admin.admin.change-password');
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'current_password' => [
                'required',
                new MatchOldPasswordRule('admin')
            ],
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required'
        ];

        $messages = [
            'new_password.confirmed' => 'Password confirmation does not match.',
            'new_password_confirmation.required' => 'The confirm new password field is required.'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $admin = Auth::guard('admin')->user();

        $admin->update([
            'password' => Hash::make($request->new_password)
        ]);

        Session::flash('success', __('Password updated successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        // invalidate the admin's session
        $request->session()->invalidate();

        return redirect()->route('admin.login');
    }

    //membershipRequest
    public function membershipRequest()
    {
        $collections = Membership::where('memberships.status', '!=', 1)->paginate(10);
        $data['collections'] = $collections;
        return view('admin.admin.membership-request', $data);
    }
    public function membershipRequestUpdate(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);
        $vendor = Vendor::findorFail($membership->vendor_id);
        $package = Package::findOrFail($membership->package_id);
        $settings = json_decode($membership->settings, true);
        $activation = Carbon::parse($package->start_date);
        $expire = Carbon::parse($package->expire_date);

        $membership->update([
            'status' => 1,
            'modified' => 1
        ]);

        if ($request->status != 0) {
            $mailer = new MegaMailer();
            $data = [
                'toMail' => $vendor->email,
                'toName' => $vendor->fname,
                'username' => $vendor->username,
                'package_title' => $package->title,
                'package_price' => ($settings['base_currency_symbol_position'] == 'left' ? $settings['base_currency_symbol'] . ' ' : '') . $package->price . ($settings['base_currency_symbol_position'] == 'right' ? ' ' . $settings['base_currency_symbol'] : ''),
                'activation_date' => $activation->toFormattedDateString(),
                'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
                'website_title' => $settings['website_title'],
                'templateType' => $request->status == 1 ? 'payment_accepted_for_membership_(_offline_gateway_)' : 'payment_rejected_for_membership_(_offline_gateway_)',
            ];
            $mailer->mailFromAdmin($data);
        } else {
        }
        Session::flash('success', __('Updated payment status successfully') . '!');
        return back();
    }
    //transaction 
    public function transcation(Request $request)
    {
        $transcation_id = null;
        if ($request->filled('transcation_id')) {
            $transcation_id = $request->transcation_id;
        }

        $info['transcations'] = Transaction::when($transcation_id, function ($query) use ($transcation_id) {
            return $query->where('transcation_id', 'like', '%' . $transcation_id . '%');
        })->orderByDesc('id')->paginate(10);

        return view('admin.admin.transcation', $info);
    }
}
