<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Http\Helpers\BasicMailer;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Booking;
use App\Models\HotelWishlist;
use App\Models\RoomContent;
use App\Models\RoomWishlist;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vendor;
use App\Rules\ImageMimeTypeRule;
use App\Rules\MatchEmailRule;
use App\Rules\MatchOldPasswordRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
  public function __construct()
  {
    $bs = DB::table('basic_settings')
      ->select('facebook_app_id', 'facebook_app_secret', 'google_client_id', 'google_client_secret')
      ->first();

    Config::set('services.facebook.client_id', $bs->facebook_app_id);
    Config::set('services.facebook.client_secret', $bs->facebook_app_secret);
    Config::set('services.facebook.redirect', url('user/login/facebook/callback'));

    Config::set('services.google.client_id', $bs->google_client_id);
    Config::set('services.google.client_secret', $bs->google_client_secret);
    Config::set('services.google.redirect', url('login/google/callback'));
  }

  public function login(Request $request)
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_login', 'meta_description_login')->first();

    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['bgImg'] = $misc->getBreadcrumb();

    // get the status of digital product (exist or not in the cart)
    if (!empty($request->input('digital_item'))) {
      $information['digitalProductStatus'] = $request->input('digital_item');
    }

    $information['bs'] = Basic::query()->select('google_recaptcha_status', 'facebook_login_status', 'google_login_status')->first();

    if ($request->redirectPath == 'hotelwishlist') {
      Session::put('redirectTo', URL::previous());
    }
    if ($request->redirectPath == 'roomwishlist') {
      Session::put('redirectTo', URL::previous());
    }
    if ($request->redirectPath == 'roomDetails') {
      Session::put('redirectTo', URL::previous());
    }
    return view('frontend.user.login', $information);
  }

  public function redirectToFacebook()
  {
    return Socialite::driver('facebook')->redirect();
  }

  public function handleFacebookCallback(Request $request)
  {
    if ($request->has('error_code')) {
      Session::flash('error', $request->error_message);
      return redirect()->route('user.login');
    }
    return $this->authenticationViaProvider('facebook');
  }

  public function redirectToGoogle()
  {
    return Socialite::driver('google')->redirect();
  }

  public function handleGoogleCallback()
  {
    return $this->authenticationViaProvider('google');
  }

  public function authenticationViaProvider($driver)
  {
    // get the url from session which will be redirect after login
    if (Session::has('redirectTo')) {
      $redirectURL = Session::get('redirectTo');
    } else {
      $redirectURL = route('user.dashboard');
    }

    $responseData = Socialite::driver($driver)->user();
    $userInfo = $responseData->user;

    $isUser = User::query()->where('email', '=', $userInfo['email'])->first();

    if (!empty($isUser)) {
      // log in
      if ($isUser->status == 1) {
        Auth::guard('web')->login($isUser);

        return redirect($redirectURL);
      } else {
        Session::flash('error', __('Sorry, your account has been deactivated.'));
        
        return redirect()->route('user.login');
      }
    } else {
      // get user avatar and save it
      $avatar = $responseData->getAvatar();
      $fileContents = file_get_contents($avatar);

      $avatarName = $responseData->getId() . '.jpg';
      $path = public_path('assets/img/users/');

      file_put_contents($path . $avatarName, $fileContents);

      // sign up
      $user = new User();

      if ($driver == 'facebook') {
        $user->name = $userInfo['name'];
      } else {
        $user->name = $userInfo['given_name'];
      }

      $user->image = $avatarName;
      $user->username = $userInfo['id'];
      $user->email = $userInfo['email'];
      $user->email_verified_at = date('Y-m-d H:i:s');
      $user->status = 1;
      $user->provider = ($driver == 'facebook') ? 'facebook' : 'google';
      $user->provider_id = $userInfo['id'];
      $user->save();

      Auth::guard('web')->login($user);

      return redirect($redirectURL);
    }
  }

  public function loginSubmit(Request $request)
  {
    // get the url from session which will be redirect after login
    if ($request->session()->has('redirectTo')) {
      $redirectURL = $request->session()->get('redirectTo');
    } else {
      $redirectURL = null;
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
      return redirect()->route('user.login')->withErrors($validator->errors())->withInput();
    }

    // get the email and password which has provided by the user
    $credentials = $request->only('username', 'password');

    // login attempt
    if (Auth::guard('web')->attempt($credentials)) {
      $authUser = Auth::guard('web')->user();
      // second, check whether the user's account is active or not
      if ($authUser->email_verified_at == null) {
        Session::flash('error', __('Please verify your email address'));

        // logout auth user as condition not satisfied
        Auth::guard('web')->logout();

        return redirect()->back();
      }
      if ($authUser->status == 0) {
        Session::flash('error', __('Sorry, your account has been deactivated'));

        // logout auth user as condition not satisfied
        Auth::guard('web')->logout();

        return redirect()->back();
      }

      // otherwise, redirect auth user to next url
      if (is_null($redirectURL)) {
        return redirect()->route('user.dashboard');
      } else {
        // before, redirect to next url forget the session value
        $request->session()->forget('redirectTo');

        return redirect($redirectURL);
      }
    } else {
      Session::flash('error', __('Incorrect username or password'));

      return redirect()->back();
    }
  }

  public function forgetPassword()
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_forget_password', 'meta_description_forget_password')->first();

    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['bgImg'] = $misc->getBreadcrumb();
    $information['bs'] = Basic::query()->select('google_recaptcha_status', 'facebook_login_status', 'google_login_status')->first();

    return view('frontend.user.forget-password', $information);
  }

  public function forgetPasswordMail(Request $request)
  {
    $rules = [
      'email' => [
        'required',
        'email:rfc,dns',
        new MatchEmailRule('user')
      ]
    ];

    $info = Basic::select('google_recaptcha_status')->first();

    $messages = [];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors())->withInput();
    }

    $user = User::query()->where('email', '=', $request->email)->first();

    // store user email in session to use it later
    $request->session()->put('userEmail', $user->email);

    // get the mail template information from db
    $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'reset_password')->first();
    $mailData['subject'] = $mailTemplate->mail_subject;
    $mailBody = $mailTemplate->mail_body;

    // get the website title info from db
    $info = Basic::select('website_title')->first();

    $name = $user->username;

    $link = '<a href=' . url("user/reset-password") . '>Click Here</a>';

    $mailBody = str_replace('{customer_name}', $name, $mailBody);
    $mailBody = str_replace('{password_reset_link}', $link, $mailBody);
    $mailBody = str_replace('{website_title}', $info->website_title, $mailBody);

    $mailData['body'] = $mailBody;

    $mailData['recipient'] = $user->email;

    $mailData['sessionMessage'] = 'A mail has been sent to your email address';

    BasicMailer::sendMail($mailData);

    return redirect()->back();
  }

  public function resetPassword()
  {
    $misc = new MiscellaneousController();

    $bgImg = $misc->getBreadcrumb();

    return view('frontend.user.reset-password', compact('bgImg'));
  }

  public function resetPasswordSubmit(Request $request)
  {
    if ($request->session()->has('userEmail')) {
      // get the user email from session
      $emailAddress = $request->session()->get('userEmail');

      $rules = [
        'new_password' => 'required|confirmed',
        'new_password_confirmation' => 'required'
      ];

      $messages = [
        'new_password.confirmed' => __('Password confirmation failed.'),
        'new_password_confirmation.required' => __('The confirm new password field is required.')
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return redirect()->back()->withErrors($validator->errors());
      }

      $user = User::query()->where('email', '=', $emailAddress)->first();

      $user->update([
        'password' => Hash::make($request->new_password)
      ]);

      Session::flash('success', __('Password updated successfully.'));
    } else {
      Session::flash('error', __('Something went wrong') . '!');
    }

    return redirect()->route('user.login');
  }

  public function signup()
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_signup', 'meta_description_signup')->first();

    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['bgImg'] = $misc->getBreadcrumb();

    $information['recaptchaInfo'] = Basic::select('google_recaptcha_status')->first();

    return view('frontend.user.signup', $information);
  }

  public function signupSubmit(Request $request)
  {
    $info = Basic::select('google_recaptcha_status', 'website_title')->first();

    // validation start
    $rules = [
      'username' => 'required|unique:users|max:255',
      'email' => 'required|email:rfc,dns|unique:users|max:255',
      'password' => 'required|confirmed',
      'password_confirmation' => 'required'
    ];

    if ($info->google_recaptcha_status == 1) {
      $rules['g-recaptcha-response'] = 'required|captcha';
    }

    $messages = [
      'username.required' => __('The username field is required.'),
      'username.unique' => __('This username is already taken.'),
      'username.max' => __('The username may not be greater than 255 characters.'),
      'email.required' => __('The email field is required.'),
      'email.email' => __('Please provide a valid email address.'),
      'email.unique' => __('This email is already registered.'),
      'email.max' => __('The email may not be greater than 255 characters.'),
      'password.required' => __('The password field is required.'),
      'password.confirmed' => __('The password confirmation does not match.'),
      'password_confirmation.required' => __('The password confirmation field is required.'),
    ];

    if ($info->google_recaptcha_status == 1) {
      $messages['g-recaptcha-response.required'] = __('Please verify that you are not a robot.');
      $messages['g-recaptcha-response.captcha'] = __('Captcha error! Try again later or contact site admin.');
    }

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors())->withInput();
    }
    // validation end

    $user = new User();
    $user->username = $request->username;
    $user->email = $request->email;
    $user->status = 1;
    $user->password = Hash::make($request->password);
    $user->save();

    // get the mail template information from db
    $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'verify_email')->first();
    $mailData['subject'] = $mailTemplate->mail_subject;
    $mailBody = $mailTemplate->mail_body;

    $link = '<a href=' . url("user/signup-verify/" . $user->id) . '>Click Here</a>';

    $mailBody = str_replace('{username}', $user->username, $mailBody);
    $mailBody = str_replace('{verification_link}', $link, $mailBody);
    $mailBody = str_replace('{website_title}', $info->website_title, $mailBody);

    $mailData['body'] = $mailBody;

    $mailData['recipient'] = $user->email;

    $mailData['sessionMessage'] = __('A verification mail has been sent to your email address');

    BasicMailer::sendMail($mailData);

    $information['authUser'] = $user;
    return back();
  }

  public function signupVerify($id)
  {
    $user = User::where('id', $id)->firstOrFail();
    $user->email_verified_at = Carbon::now();
    $user->save();
    Auth::login($user);
    return redirect()->route('user.dashboard');
  }

  public function redirectToDashboard()
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['language'] = $language;

    $information['bgImg'] = $misc->getBreadcrumb();
    $information['pageHeading'] = $misc->getPageHeading($language);

    $user = Auth::guard('web')->user();

    $information['authUser'] = $user;
    $information['roomwishlists'] = RoomWishlist::where('user_id', $user->id)
      ->join('rooms', 'rooms.id', '=', 'room_wishlists.room_id')
      ->join('room_contents', 'room_contents.room_id', '=', 'room_wishlists.room_id')
      ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
      ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
      ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
      ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
      ->where('room_contents.language_id', $language->id)
      ->where('hotel_contents.language_id', $language->id)
      ->where('rooms.status',  '=',    '1')
      ->where('hotels.status',  '=',    '1')
      ->where('room_categories.status', 1)
      ->where('hotel_categories.status', 1)

      ->when('rooms.vendor_id' != "0", function ($query) {
        return $query->leftJoin('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('rooms.vendor_id', '=', 0);
          });
      })
      ->when('rooms.vendor_id' != "0", function ($query) {
        return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where([
              ['vendors.status', '=', 1],
            ])->orWhere('rooms.vendor_id', '=', 0);
          });
      })
      ->select(
        'room_contents.room_id as room_id',
        'room_contents.title as title',
        'room_contents.slug as slug'
      )
      ->get();

    $information['hotelwishlists'] = HotelWishlist::where('user_id', $user->id)
      ->join('hotels', 'hotels.id', '=', 'hotel_wishlists.hotel_id')
      ->join('hotel_contents', 'hotel_contents.hotel_id', '=', 'hotel_wishlists.hotel_id')
      ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
      ->where('hotel_contents.language_id', $language->id)
      ->where('hotels.status',  '=',    '1')
      ->where('hotel_categories.status', 1)

      ->when('hotels.vendor_id' != "0", function ($query) {
        return $query->leftJoin('memberships', 'hotels.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('hotels.vendor_id', '=', 0);
          });
      })
      ->when('hotels.vendor_id' != "0", function ($query) {
        return $query->leftJoin('vendors', 'hotels.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where([
              ['vendors.status', '=', 1],
            ])->orWhere('hotels.vendor_id', '=', 0);
          });
      })
      ->select(
        'hotel_contents.hotel_id as hotel_id',
        'hotel_contents.title as title',
        'hotel_contents.slug as slug'
      )
      ->get();
    $information['bookings'] = Booking::where('user_id', '=', $user->id)
      ->orderBy('id', 'desc')
      ->get();

    $information['supportTickets'] = SupportTicket::where([['user_id', Auth::guard('web')->user()->id], ['user_type', 'user']])->orderBy('id', 'desc')->get();
    return view('frontend.user.dashboard', $information);
  }

  public function editProfile()
  {
    $misc = new MiscellaneousController();

    $information['bgImg'] = $misc->getBreadcrumb();
    $language = $misc->getLanguage();
    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['authUser'] = Auth::guard('web')->user();


    return view('frontend.user.edit-profile', $information);
  }

  public function updateProfile(Request $request)
  {
    if ($request->image) {
      $image = true;
    } else {
      $image = false;
    }

    $request->validate([
      'image' => $image ? [
        'required',
        'dimensions:width=80,height=80',
        new ImageMimeTypeRule()
      ] : '',
      'name' => 'required',
      'username' => [
        'required',
        'alpha_dash',
        Rule::unique('users', 'username')->ignore(Auth::guard('web')->user()->id),
      ],
      'email' => [
        'required',
        'email',
        Rule::unique('users', 'email')->ignore(Auth::guard('web')->user()->id)
      ],
    ]);

    $authUser = Auth::guard('web')->user();
    $in = $request->all();
    $file = $request->file('image');
    if ($file) {
      $extension = $file->getClientOriginalExtension();
      $directory = public_path('assets/img/users/');
      $fileName = uniqid() . '.' . $extension;
      @mkdir($directory, 0775, true);
      $file->move($directory, $fileName);
      $in['image'] = $fileName;
      @unlink(public_path('assets/img/users/') . $authUser->image);
    }

    $authUser->update($in);
    Session::flash('success', __('Your profile has been updated successfully.'));
    return redirect()->back();
  }

  public function changePassword()
  {
    $misc = new MiscellaneousController();

    $bgImg = $misc->getBreadcrumb();
    $language = $misc->getLanguage();
    $pageHeading = $misc->getPageHeading($language);

    return view('frontend.user.change-password', compact('bgImg', 'pageHeading'));
  }

  public function updatePassword(Request $request)
  {
    $rules = [
      'current_password' => [
        'required',
        new MatchOldPasswordRule('user')
      ],
      'new_password' => 'required|confirmed',
      'new_password_confirmation' => 'required'
    ];

    $messages = [
      'new_password.confirmed' => __('Password confirmation failed.'),
      'new_password_confirmation.required' => __('The confirm new password field is required.')
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $user = Auth::guard('web')->user();

    $user->update([
      'password' => Hash::make($request->new_password)
    ]);

    Session::flash('success', __('Password updated successfully') . '!');

    return redirect()->back();
  }

  //wishlist
  public function roomWishlist()
  {
    $misc = new MiscellaneousController();
    $bgImg = $misc->getBreadcrumb();
    $language = $misc->getLanguage();
    $information['language'] = $language;
    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['language'] = $language;
    $user_id = Auth::guard('web')->user()->id;
    $information['wishlists'] = RoomWishlist::where('user_id', $user_id)
      ->join('rooms', 'rooms.id', '=', 'room_wishlists.room_id')
      ->join('room_contents', 'room_contents.room_id', '=', 'room_wishlists.room_id')
      ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
      ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
      ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
      ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
      ->where('room_contents.language_id', $language->id)
      ->where('hotel_contents.language_id', $language->id)
      ->where('rooms.status',  '=',    '1')
      ->where('hotels.status',  '=',    '1')
      ->where('room_categories.status', 1)
      ->where('hotel_categories.status', 1)

      ->when('rooms.vendor_id' != "0", function ($query) {
        return $query->leftJoin('memberships', 'rooms.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('rooms.vendor_id', '=', 0);
          });
      })
      ->when('rooms.vendor_id' != "0", function ($query) {
        return $query->leftJoin('vendors', 'rooms.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where([
              ['vendors.status', '=', 1],
            ])->orWhere('rooms.vendor_id', '=', 0);
          });
      })
      ->select(
        'room_contents.room_id as room_id',
        'room_contents.title as title',
        'room_contents.slug as slug'
      )
      ->get();

    $information['bgImg'] = $bgImg;
    return view('frontend.user.wishlist.room', $information);
  }
  public function hotelWishlist()
  {
    $misc = new MiscellaneousController();
    $bgImg = $misc->getBreadcrumb();
    $language = $misc->getLanguage();
    $information['language'] = $language;
    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['language'] = $language;
    $user_id = Auth::guard('web')->user()->id;
    $information['wishlists'] = HotelWishlist::where('user_id', $user_id)
      ->join('hotels', 'hotels.id', '=', 'hotel_wishlists.hotel_id')
      ->join('hotel_contents', 'hotel_contents.hotel_id', '=', 'hotel_wishlists.hotel_id')
      ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
      ->where('hotel_contents.language_id', $language->id)
      ->where('hotels.status',  '=',    '1')
      ->where('hotel_categories.status', 1)

      ->when('hotels.vendor_id' != "0", function ($query) {
        return $query->leftJoin('memberships', 'hotels.vendor_id', '=', 'memberships.vendor_id')
          ->where(function ($query) {
            $query->where([
              ['memberships.status', '=', 1],
              ['memberships.start_date', '<=', now()->format('Y-m-d')],
              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
            ])->orWhere('hotels.vendor_id', '=', 0);
          });
      })
      ->when('hotels.vendor_id' != "0", function ($query) {
        return $query->leftJoin('vendors', 'hotels.vendor_id', '=', 'vendors.id')
          ->where(function ($query) {
            $query->where([
              ['vendors.status', '=', 1],
            ])->orWhere('hotels.vendor_id', '=', 0);
          });
      })
      ->select(
        'hotel_contents.hotel_id as hotel_id',
        'hotel_contents.title as title',
        'hotel_contents.slug as slug'
      )
      ->get();

    $information['bgImg'] = $bgImg;
    return view('frontend.user.wishlist.hotel', $information);
  }
  //room booking
  public function roomBooking()
  {
    $misc = new MiscellaneousController();
    $information['bgImg'] =  $misc->getBreadcrumb();
    $language = $misc->getLanguage();
    $information['pageHeading'] = $misc->getPageHeading($language);
    $user = Auth::guard('web')->user();

    $information['bookings'] = Booking::where('user_id', '=', $user->id)
      ->orderBy('id', 'desc')
      ->get();

    return view('frontend.user.booking.index', $information);
  }

  public function bookingDetails($id)
  {
    $misc = new MiscellaneousController();
    $information['bgImg'] =  $misc->getBreadcrumb();

    $language = $misc->getLanguage();
    $information['pageHeading'] = $misc->getPageHeading($language);
    $user = Auth::guard('web')->user();
    $information['user'] = $user;

    $booking =  Booking::where([['id', $id], ['user_id', $user->id]])->firstOrFail();

    $information['additional_services']   = json_decode($booking->service_details);
    $information['basic'] = Basic::select('base_currency_symbol', 'base_currency_symbol_position', 'base_currency_text', 'base_currency_text_position')->first();

    $information['roomContent'] = RoomContent::where([['language_id', $language->id], ['room_id', $booking->room_id]])->select('title', 'slug', 'room_id')->first();
    $information['bookingInfo'] = $booking;
    $information['seller'] = Vendor::where('id', $booking->vendor_id)->first();
    // dd($data['seller']);

    return view('frontend.user.booking.details', $information);
  }

  //add_to_wishlist hotel
  public function add_to_wishlist_hotel($id)
  {
    if (Auth::guard('web')->check()) {
      $user_id = Auth::guard('web')->user()->id;
      $check = HotelWishlist::where('hotel_id', $id)->where('user_id', $user_id)->first();

      if (!empty($check)) {
        return back()->with('success', __('Added to your wishlist successfully.'));
      } else {
        $add = new HotelWishlist;
        $add->hotel_id = $id;
        $add->user_id = $user_id;
        $add->save();
        return back()->with('success', __('Added to your wishlist successfully.'));
      }
    } else {
      return redirect()->route('user.login', ['redirectPath' => 'hotelwishlist']);
    }
  }
  //remove_wishlist hotel
  public function remove_wishlist_hotel($id)
  {
    if (Auth::guard('web')->check()) {
      $user_id = Auth::guard('web')->user()->id;
      $remove = HotelWishlist::where('hotel_id', $id)->where('user_id', $user_id)->first();
      if ($remove) {
        $remove->delete();
        return back()->with('success', __('Removed From wishlist successfully'));
      } else {
        return back()->with('warning', 'Something went wrong.');
      }
    } else {
      return redirect()->route('user.login', ['redirectPath' => 'hotelwishlist']);
    }
  }
  //add_to_wishlist room
  public function add_to_wishlist_room($id)
  {
    if (Auth::guard('web')->check()) {
      $user_id = Auth::guard('web')->user()->id;
      $check = RoomWishlist::where('room_id', $id)->where('user_id', $user_id)->first();

      if (!empty($check)) {
        return back()->with('success', __('Added to your wishlist successfully.'));
      } else {
        $add = new RoomWishlist;
        $add->room_id = $id;
        $add->user_id = $user_id;
        $add->save();
        return back()->with('success', __('Added to your wishlist successfully.'));
      }
    } else {
      return redirect()->route('user.login', ['redirectPath' => 'roomwishlist']);
    }
  }
  //remove_wishlist room
  public function remove_wishlist_room($id)
  {
    if (Auth::guard('web')->check()) {
      $user_id = Auth::guard('web')->user()->id;
      $remove = RoomWishlist::where('room_id', $id)->where('user_id', $user_id)->first();
      if ($remove) {
        $remove->delete();
        return back()->with('success', __('Removed From wishlist successfully'));
      } else {
        return back()->with('warning', 'Something went wrong.');
      }
    } else {
      return redirect()->route('user.login', ['redirectPath' => 'roomwishlist']);
    }
  }

  public function logoutSubmit(Request $request)
  {
    Auth::guard('web')->logout();
    Session::forget('secret_login');

    if ($request->session()->has('redirectTo')) {
      $request->session()->forget('redirectTo');
    }

    return redirect()->route('user.login');
  }
}
