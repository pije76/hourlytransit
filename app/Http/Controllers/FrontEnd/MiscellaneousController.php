<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\BasicSettings\Basic;
use App\Models\Language;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MiscellaneousController extends Controller
{
  public function getLanguage()
  {
    // get the current locale of this system
    if (Session::has('currentLocaleCode')) {
      $locale = Session::get('currentLocaleCode');
    }

    if (empty($locale)) {
      $language = Language::where('is_default', 1)->first();
    } else {
      $language = Language::where('code', $locale)->first();
      if (empty($language)) {
        $language = Language::where('is_default', 1)->first();
      }
    }

    return $language;
  }

  public function storeSubscriber(Request $request)
  {
    $rules = [
      'email_id' => [
        'required',
        'email:rfc,dns',
        Rule::unique('subscribers', 'email_id')
      ]
    ];
    $messsage = [];
    $messsage = [
      'email_id.required' => __('Email address field is required.'),
      'email_id.unique' => __('The email address has already been taken.'),
      'email_id.email' => __('The email address is not valid.')
    ];

    $validator = Validator::make($request->all(), $rules, $messsage);
    if ($validator->fails()) {
      return Response::json([
        'error' => $validator->getMessageBag()
      ], 400);
    }

    Subscriber::create([
      'email_id' => $request->email_id
    ]);

    return response()->json(['message' => __('You have successfully subscribed to our newsletter.'), 'alert_type' => 'success']);
  }


  public function changeLanguage(Request $request)
  {
    // put the selected language in session
    $langCode = $request['lang_code'];

    $request->session()->put('currentLocaleCode', $langCode);

    return redirect()->back();
  }

  public function getPageHeading($language)
  {
    if (Route::is('frontend.rooms')) {
      $pageHeading = $language->pageName()->select('rooms_page_title')->first();
    } elseif (Route::is('frontend.hotels')) {
      $pageHeading = $language->pageName()->select('hotel_page_title')->first();
    } elseif (Route::is('frontend.room.checkout')) {
      $pageHeading = $language->pageName()->select('room_checkout_page_title')->first();
    } elseif (Route::is('frontend.vendors')) {
      $pageHeading = $language->pageName()->select('vendor_page_title')->first();
    } elseif (Route::is('user.login')) {
      $pageHeading = $language->pageName()->select('login_page_title')->first();
    } elseif (Route::is('user.signup')) {
      $pageHeading = $language->pageName()->select('signup_page_title')->first();
    } elseif (Route::is('about_us')) {
      $pageHeading = $language->pageName()->select('about_us_title')->first();
    } elseif (Route::is('blog') || Route::is('blog_details')) {
      $pageHeading = $language->pageName()->select('blog_page_title')->first();
    } elseif (Route::is('frontend.pricing')) {
      $pageHeading = $language->pageName()->select('pricing_page_title')->first();
    } elseif (Route::is('faq')) {
      $pageHeading = $language->pageName()->select('faq_page_title')->first();
    } elseif (Route::is('contact')) {
      $pageHeading = $language->pageName()->select('contact_page_title')->first();
    } elseif (Route::is('vendor.login')) {
      $pageHeading = $language->pageName()->select('vendor_login_page_title')->first();
    } elseif (Route::is('vendor.signup')) {
      $pageHeading = $language->pageName()->select('vendor_signup_page_title')->first();
    } elseif (Route::is('user.forget_password')) {
      $pageHeading = $language->pageName()->select('forget_password_page_title')->first();
    } elseif (Route::is('vendor.forget.password')) {
      $pageHeading = $language->pageName()->select('vendor_forget_password_page_title')->first();
    } elseif (Route::is('user.wishlist.room')) {
      $pageHeading = $language->pageName()->select('room_wishlist_page_title')->first();
    } elseif (Route::is('user.wishlist.hotel')) {
      $pageHeading = $language->pageName()->select('hotel_wishlist_page_title')->first();
    } elseif (Route::is('user.dashboard')) {
      $pageHeading = $language->pageName()->select('dashboard_page_title')->first();
    } elseif (Route::is('user.room_bookings')) {
      $pageHeading = $language->pageName()->select('room_bookings_page_title')->first();
    } elseif (Route::is('user.room_booking_details')) {
      $pageHeading = $language->pageName()->select('room_booking_details_page_title')->first();
    } elseif (Route::is('user.support_ticket')) {
      $pageHeading = $language->pageName()->select('support_ticket_page_title')->first();
    } elseif (Route::is('user.support_ticket.create')) {
      $pageHeading = $language->pageName()->select('support_ticket_create_page_title')->first();
    } elseif (Route::is('user.change_password')) {
      $pageHeading = $language->pageName()->select('change_password_page_title')->first();
    } elseif (Route::is('user.edit_profile')) {
      $pageHeading = $language->pageName()->select('edit_profile_page_title')->first();
    } else {
      $pageHeading = null;
    }

    return $pageHeading;
  }


  public static function getBreadcrumb()
  {
    $breadcrumb = Basic::select('breadcrumb')->first();

    return $breadcrumb;
  }


  public function countAdView($id)
  {
    try {
      $ad = Advertisement::findOrFail($id);

      $ad->update([
        'views' => $ad->views + 1
      ]);

      return response()->json(['success' => 'Advertisement view counted successfully.']);
    } catch (ModelNotFoundException $e) {
      return response()->json(['error' => 'Sorry, something went wrong!']);
    }
  }


  public function serviceUnavailable()
  {
    $info = Basic::select('maintenance_img', 'maintenance_msg')->first();

    return view('errors.503', compact('info'));
  }
}
