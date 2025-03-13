<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\BasicSettings\AboutUs;
use App\Models\BasicSettings\Basic;
use App\Models\HomePage\Banner;
use App\Models\HomePage\CustomSection;
use App\Models\HomePage\Feature;
use App\Models\HomePage\Section;
use App\Models\HomePage\SectionContent;
use App\Models\Journal\Blog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Location\City;
use App\Models\Package;
use App\Models\RoomContent;

class HomeController extends Controller
{
  public function index(Request $request)
  {
    $themeVersion = Basic::query()->pluck('theme_version')->first();

    $secInfo = Section::query()->first();

    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['language'] = $language;

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_home', 'meta_description_home')->first();

    if ($themeVersion == 2) {
      $information['sliderInfos'] = $language->sliderInfo()->orderByDesc('id')->get();
    }
    if ($themeVersion == 3 && $secInfo->benifit_section_status == 1) {
      $information['benifits'] =  $language->benifits()->orderByDesc('id')->get();
    }

    $information['sectionContent'] = SectionContent::where('language_id', $language->id)->first();

    $information['images']  = Basic::select(
      'hero_section_image',
      'feature_section_image',
      'counter_section_image',
      'call_to_action_section_image',
      'call_to_action_section_inner_image',
      'testimonial_section_image'
    )->first();



    if ($secInfo->featured_section_status == 1) {
      $information['features'] = Feature::where('language_id', $language->id)->get();
    }


    if ($themeVersion == 1) {
      $information['banners'] = Banner::where('language_id', $language->id)->get();
    }

    if ($secInfo->work_process_section_status == 1 && ($themeVersion == 1 || $themeVersion == 4)) {
      $information['workProcessSecInfo'] = $language->workProcessSection()->first();
      $information['processes'] = $language->workProcess()->orderBy('serial_number', 'asc')->get();
    }


    if ($secInfo->counter_section_status == 1) {
      $information['counters'] = $language->counterInfo()->orderByDesc('id')->get();
    }

    $information['currencyInfo'] = $this->getCurrencyInfo();

    if ($secInfo->testimonial_section_status == 1) {
      $information['testimonials'] = $language->testimonial()->orderByDesc('id')->get();
    }

    if ($secInfo->blog_section_status == 1) {

      $information['blogs'] = Blog::query()->join('blog_informations', 'blogs.id', '=', 'blog_informations.blog_id')
        ->join('blog_categories', 'blog_categories.id', '=', 'blog_informations.blog_category_id')
        ->where('blogs.status', '=', 1)
        ->where('blog_categories.status', '=', 1)
        ->where('blog_informations.language_id', '=', $language->id)
        ->select('blogs.image', 'blogs.id', 'blog_categories.name AS categoryName', 'blog_categories.slug AS categorySlug', 'blog_informations.title', 'blog_informations.slug', 'blog_informations.author', 'blogs.created_at', 'blog_informations.content')
        ->orderBy('blogs.serial_number', 'desc')
        ->limit(3)
        ->get();

      $information['blog_count']  = Blog::query()->join('blog_informations', 'blogs.id', '=', 'blog_informations.blog_id')
        ->join('blog_categories', 'blog_categories.id', '=', 'blog_informations.blog_category_id')
        ->where('blog_informations.language_id', '=', $language->id)
        ->where('blog_categories.status', '=', 1)
        ->where('blogs.status', '=', 1)
        ->select('blogs.id')
        ->get();
    }

    if ($themeVersion == 1) {
      $information['cities'] = City::has('hotel_city')
        ->where('language_id', $language->id)
        ->inRandomOrder()
        ->take(10)
        ->get();
    }
    if ($themeVersion == 2 || $themeVersion == 3) {
      $information['cities'] = City::has('hotel_city')->where('language_id', $language->id)->orderBy('updated_at', 'asc')->get();
    }

    $information['secInfo'] = $secInfo;

    $information['room_contents']  = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
      ->Join('room_features', 'rooms.id', '=', 'room_features.room_id')
      ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
      ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
      ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
      ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
      ->where('hotel_contents.language_id', $language->id)
      ->where('room_categories.status', 1)
      ->where('hotel_categories.status', 1)
      ->where('room_contents.language_id', $language->id)
      ->where('room_features.order_status', '=', 'apporved')
      ->where('rooms.status',  '=',    '1')
      ->where('hotels.status',  '=',    '1')
      ->whereDate('room_features.end_date', '>=', Carbon::now()->format('Y-m-d'))
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
        'rooms.*',
        'room_contents.title',
        'room_contents.slug',
        'room_contents.amenities',
        'hotels.id as hotelId',
        'hotels.stars as stars',
        'hotels.latitude as latitude',
        'hotels.longitude as longitude',
        'hotels.logo as hotelImage',
        'hotel_contents.title as hotelName',
        'hotel_contents.slug as hotelSlug',
        'hotel_contents.city_id',
        'hotel_contents.state_id',
        'hotel_contents.country_id'
      )
      ->inRandomOrder()
      ->limit(6)
      ->get();

    $information['room_contents_count']  = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
      ->Join('room_features', 'rooms.id', '=', 'room_features.room_id')
      ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
      ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
      ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
      ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
      ->where('hotel_contents.language_id', $language->id)
      ->where('room_categories.status', 1)
      ->where('hotel_categories.status', 1)
      ->where('room_contents.language_id', $language->id)
      ->where('room_features.order_status', '=', 'apporved')
      ->where('rooms.status',  '=',    '1')
      ->where('hotels.status',  '=',    '1')
      ->whereDate('room_features.end_date', '>=', Carbon::now()->format('Y-m-d'))
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
        'rooms.id'
      )
      ->get();

    $sections = [
      'hero_section',
      'city_section',
      'featured_section',
      'featured_room_section',
      'counter_section',
      'testimonial_section',
      'blog_section',
      'call_to_action_section',
      'benifit_section'
    ];

    foreach ($sections as $section) {
      $information["after_" . str_replace('_section', '', $section)] = CustomSection::where('order', $section)
        ->where('page_type', 'home')
        ->orderBy('serial_number', 'asc')
        ->get();
    }

    $sectionInfo = Section::select('custom_section_status')->first();
    if (!empty($sectionInfo->custom_section_status)) {
      $info = json_decode($sectionInfo->custom_section_status, true);
      $information['homecusSec'] = $info;
    }

    if ($themeVersion == 1) {
      return view('frontend.home.index-v1', $information);
    } elseif ($themeVersion == 2) {
      return view('frontend.home.index-v2', $information);
    } elseif ($themeVersion == 3) {
      return view('frontend.home.index-v3', $information);
    }
  }

  public function about()
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['themeVersion'] = Basic::query()->pluck('theme_version')->first();

    $information['seoInfo'] = $language->seoInfo()->select('meta_keywords_about_page', 'meta_description_about_page')->first();
    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['about'] = AboutUs::where('language_id', $language->id)->first();

    $information['bgImg'] = $misc->getBreadcrumb();
    $secInfo = Section::query()->first();
    $information['secInfo'] = $secInfo;
    $information['sectionContent'] = SectionContent::where('language_id', $language->id)->first();
    $information['images']  = Basic::select(
      'about_section_image',
      'feature_section_image',
      'counter_section_image',
      'call_to_action_section_image',
      'call_to_action_section_inner_image',
      'testimonial_section_image'
    )->first();

    if ($secInfo->about_features_section_status == 1) {
      $information['features'] = Feature::where('language_id', $language->id)->get();
    }
    if ($secInfo->work_process_section_status == 1) {
      $information['workProcessSecInfo'] = $language->workProcessSection()->first();
      $information['processes'] = $language->workProcess()->orderBy('serial_number', 'asc')->get();
    }

    if ($secInfo->about_testimonial_section_status == 1) {
      $information['testimonials'] = $language->testimonial()->orderByDesc('id')->get();
      $information['testimonialSecImage'] = Basic::query()->pluck('testimonial_section_image')->first();
    }

    if ($secInfo->about_counter_section_status == 1) {
      $information['counterSectionImage'] = Basic::query()->pluck('counter_section_image')->first();
      $information['counters'] = $language->counterInfo()->orderByDesc('id')->get();
    }

    $sections = ['about_section', 'features_section', 'counter_section', 'testimonial_section'];

    foreach ($sections as $section) {

      $information["after_" . str_replace('_section', '', $section)] = CustomSection::where('order', $section)
        ->where('page_type', 'about')
        ->orderBy('serial_number', 'asc')
        ->get();
    }

    $sectionInfo = Section::select('about_custom_section_status')->first();
    if (!empty($sectionInfo->about_custom_section_status)) {
      $info = json_decode($sectionInfo->about_custom_section_status, true);
      $information['aboutSec'] = $info;
    }
    
    return view('frontend.about-us', $information);
  }
  public function pricing(Request $request)
  {
    $misc = new MiscellaneousController();
    $language = $misc->getLanguage();
    $data['bgImg'] = $misc->getBreadcrumb();

    $data['seoInfo'] = $language->seoInfo()->select('meta_keyword_pricing', 'meta_description_pricing')->first();

    $terms = [];
    if (Package::query()->where('status', '1')->where('term', 'monthly')->count() > 0) {
      $terms[] = 'Monthly';
    }
    if (Package::query()->where('status', '1')->where('term', 'yearly')->count() > 0) {
      $terms[] = 'Yearly';
    }
    if (Package::query()->where('status', '1')->where('term', 'lifetime')->count() > 0) {
      $terms[] = 'Lifetime';
    }
    $data['terms'] = $terms;

    $data['pageHeading'] = $misc->getPageHeading($language);

    return view('frontend.pricing', $data);
  }

  //offline
  public function offline()
  {
    return view('frontend.offline');
  }
}
