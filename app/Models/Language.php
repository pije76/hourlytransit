<?php

namespace App\Models;

use App\Models\BasicSettings\AboutUs;
use App\Models\BasicSettings\CookieAlert;
use App\Models\BasicSettings\PageHeading;
use App\Models\BasicSettings\SEO;
use App\Models\CustomPage\PageContent;
use App\Models\FAQ;
use App\Models\Footer\FooterContent;
use App\Models\Footer\QuickLink;
use App\Models\HomePage\Banner;
use App\Models\HomePage\CounterInformation;
use App\Models\HomePage\Hero\Slider;
use App\Models\HomePage\Testimony\Testimonial;
use App\Models\Journal\BlogCategory;
use App\Models\Journal\BlogInformation;
use App\Models\MenuBuilder;
use App\Models\Popup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HomePage\Benifit;
use App\Models\HomePage\Feature;
use App\Models\HomePage\SectionContent;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;

class Language extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['name', 'code', 'direction', 'is_default'];

  public function faq()
  {
    return $this->hasMany(FAQ::class);
  }

  public function customPageInfo()
  {
    return $this->hasMany(PageContent::class);
  }

  public function footerContent()
  {
    return $this->hasOne(FooterContent::class);
  }

  public function footerQuickLink()
  {
    return $this->hasMany(QuickLink::class);
  }

  public function announcementPopup()
  {
    return $this->hasMany(Popup::class);
  }

  public function blogCategory()
  {
    return $this->hasMany(BlogCategory::class);
  }

  public function blogInformation()
  {
    return $this->hasMany(BlogInformation::class);
  }

  public function menuInfo()
  {
    return $this->hasOne(MenuBuilder::class, 'language_id', 'id');
  }

  public function features()
  {
    return $this->hasMany(Feature::class, 'language_id', 'id');
  }

  public function counterInfo()
  {
    return $this->hasMany(CounterInformation::class, 'language_id', 'id');
  }
  public function amenitieInfo()
  {
    return $this->hasMany(Amenitie::class, 'language_id', 'id');
  }
  public function countryInfo()
  {
    return $this->hasMany(Country::class, 'language_id', 'id');
  }
  public function stateInfo()
  {
    return $this->hasMany(State::class, 'language_id', 'id');
  }
  public function cityInfo()
  {
    return $this->hasMany(City::class, 'language_id', 'id');
  }

  public function testimonial()
  {
    return $this->hasMany(Testimonial::class, 'language_id', 'id');
  }

  public function hotelCategory()
  {
    return $this->hasMany(HotelCategory::class);
  }

  public function hotelContent()
  {
    return $this->hasMany(HotelContent::class);
  }
  public function roomCategories()
  {
    return $this->hasMany(RoomCategory::class);
  }
  public function roomContent()
  {
    return $this->hasMany(RoomContent::class);
  }

  public function serviceContent()
  {
    return $this->hasMany(AdditionalServiceContent::class);
  }

  public function amenities()
  {
    return $this->hasMany(Amenitie::class);
  }
  public function cities()
  {
    return $this->hasMany(City::class);
  }
  public function states()
  {
    return $this->hasMany(State::class);
  }
  public function countries()
  {
    return $this->hasMany(Country::class);
  }

  public function hotelCounterContents()
  {
    return $this->hasMany(HotelCounterContent::class);
  }



  public function vendorInfo()
  {
    return $this->hasOne(VendorInfo::class);
  }

  public function sliderInfo()
  {
    return $this->hasMany(Slider::class, 'language_id', 'id');
  }

  public function benifits()
  {
    return $this->hasMany(Benifit::class, 'language_id', 'id');
  }

  public function banner()
  {
    return $this->hasOne(Banner::class);
  }
  public function pageName()
  {
    return $this->hasOne(PageHeading::class);
  }

  public function seoInfo()
  {
    return $this->hasOne(SEO::class);
  }
  public function cookieAlertInfo()
  {
    return $this->hasOne(CookieAlert::class);
  }
  public function roomDetails()
  {
    return $this->hasMany(RoomContent::class);
  }
  public function aboutSection()
  {
    return $this->hasOne(AboutUs::class);
  }
  public function SectionContent()
  {
    return $this->hasOne(SectionContent::class);
  }
}
