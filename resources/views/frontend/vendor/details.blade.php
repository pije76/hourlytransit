@extends('frontend.layout')

@section('pageHeading')
  {{ $vendor->username }}
@endsection
@section('metaKeywords')
  {{ $vendor->username }}, {{ !request()->filled('admin') ? @$vendorInfo->name : '' }}
@endsection

@section('metaDescription')
  {{ !request()->filled('admin') ? @$vendorInfo->details : '' }}
@endsection

@section('content')

  <!-- Page title start-->
  <div class="page-title-area blur-up lazyloaded header-next">
      <div class="overlay opacity-75"></div>
      <!-- Background Image -->
      <img class="lazyload blur-up bg-img"
        @if (!empty($bgImg->breadcrumb)) src="{{ asset('assets/img/' . $bgImg->breadcrumb) }}" @else
      src="{{ asset('assets/front/images/page-title-bg.jpg') }}" @endif
        alt="Bg-img">
    <div class="container">
      <div class="content">
        <div class="vendor mb-15">
          <figure class="vendor-img">
            <a href="javaScript:void(0)" class="lazy-container ratio ratio-1-1 radius-md">
              @if ($vendor_id == 0)
                <img class="lazyload" src="assets/images/placeholder.png"
                  data-src="{{ asset('assets/img/admins/' . $vendor->image) }}" alt="Vendor">
              @else
                @if ($vendor->photo != null)
                  <img class="lazyload" src="assets/images/placeholder.png"
                    data-src="{{ asset('assets/admin/img/vendor-photo/' . $vendor->photo) }}" alt="Vendor">
                @else
                  <img class="lazyload" src="assets/images/placeholder.png"
                    data-src="{{ asset('assets/img/blank-user.jpg') }}" alt="Vendor">
                @endif
              @endif
            </a>
          </figure>
          <div class="vendor-info">
            <h5 class="mb-1 color-white">{{ $vendor->username }}</h5>
            <span class="text-light font-sm">
              {{ $vendor->first_name ? @$vendor->first_name : @$vendorInfo->name }}
            </span>
            <span class="text-light font-sm d-block">{{ __('Member since') }}
              {{ \Carbon\Carbon::parse($vendor->created_at)->format('F Y') }}</span>
            <span class="text-light font-sm d-block">{{ __('Total Hotels') . ' : ' }}
              @php
                $total_vendor_hotel = App\Models\Hotel::where([
                    ['vendor_id', $vendor_id],
                    ['hotels.status', '=', '1'],
                ])
                    ->get()
                    ->count();
              @endphp
              {{ $total_vendor_hotel }}
            </span>
          </div>
        </div>

        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-start">
            <li class="breadcrumb-item"><a href="href="{{ route('index') }}"">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item" aria-current="page">{{ __('Vendor Details') }}</li>
          </ol>
        </nav>

      </div>
    </div>
  </div>
  <!-- Page title end-->

  <!-- Vendor-area start -->
  <div class="vendor-area pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        <div class="col-lg-9">
          <h4 class="title mb-20">{{ __('All Hotels') }}</h4>
          <div class="tabs-navigation tabs-navigation-3 mb-20">
            <ul class="nav nav-tabs">
              <li class="nav-item">
                <button class="nav-link btn-md active" data-bs-toggle="tab" data-bs-target="#tab_all"
                  type="button">{{ __('All Hotels') }}</button>
              </li>
              @php
                if (request()->filled('admin')) {
                    $vendor_id = 0;
                } else {
                    $vendor_id = $vendor_id;
                }
              @endphp
              @foreach ($categories as $category)
                @php
                  $category_id = $category->id;
                  $hotels_count = App\Models\Hotel::join('hotel_contents', 'hotel_contents.hotel_id', 'hotels.id')
                      ->where([['vendor_id', $vendor_id], ['hotels.status', '=', '1']])
                      ->where('hotel_contents.language_id', $language->id)
                      ->where('hotel_contents.category_id', $category_id)
                      ->get()
                      ->count();
                @endphp
                @if ($hotels_count > 0)
                  <li class="nav-item">
                    <button class="nav-link btn-md" data-bs-toggle="tab" data-bs-target="#tab_{{ $category->id }}"
                      type="button">{{ $category->name }}</button>
                  </li>
                @endif
              @endforeach
            </ul>
          </div>
          <div class="tab-content hotels" data-aos="fade-up">
            <div class="tab-pane fade show active" id="tab_all">
              <div class="row">
                @if (count($hotel_contents) > 0)
                  @foreach ($hotel_contents as $hotel_content)
                    @php
                      $total_review = App\Models\RoomReview::where('hotel_id', $hotel_content->id)->count();
                      $today_date = now()->format('Y-m-d');
                      $feature = App\Models\HotelFeature::where('order_status', '=', 'completed')
                          ->where('hotel_id', $hotel_content->id)
                          ->whereDate('end_date', '>=', $today_date)
                          ->first();
                    @endphp
                    @if (!empty($hotel_content))
                      <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="product-default product-default-style-2 border radius-md mb-25 border-primary">
                          <div class="product_top text-center">
                            <div class="p-20">
                              <figure class="product_img mx-auto mb-15">
                                <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                                  target="_self" title="{{ __('Link') }}">
                                  <img class="lazyload rounded-circle"
                                    data-src="{{ asset('assets/img/hotel/logo/' . $hotel_content->logo) }}"
                                    alt="Hotel">
                                </a>
                              </figure>
                              @if (Auth::guard('web')->check())
                                @php
                                  $user_id = Auth::guard('web')->user()->id;
                                  $checkWishList = checkHotelWishList($hotel_content->id, $user_id);
                                @endphp
                              @else
                                @php
                                  $checkWishList = false;
                                @endphp
                              @endif

                              <a href="{{ $checkWishList == false ? route('addto.wishlist.hotel', $hotel_content->id) : route('remove.wishlist.hotel', $hotel_content->id) }}"
                                class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }} "
                                data-tooltip="tooltip" data-bs-placement="top"
                                title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
                                <i class="fal fa-bookmark"></i>
                              </a>
                              <div class="rank-star">
                                @for ($i = 0; $i < $hotel_content->stars; $i++)
                                  <i class="fas fa-star"></i>
                                @endfor
                              </div>

                              <span class="product_subtitle">
                                <a href="{{ route('frontend.hotels', ['category' => $hotel_content->categorySlug]) }}"
                                  target="_self" title="{{ __('Link') }}">{{ $hotel_content->categoryName }}</a>
                              </span>
                              <div class="title lc-1">
                                <h4 title mb-0>
                                  <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                                    target="_self" title="{{ __('Link') }}">
                                    {{ $hotel_content->title }}
                                  </a>
                                </h4>
                              </div>

                              @php
                                $city = null;
                                $State = null;
                                $country = null;

                                if ($hotel_content->city_id) {
                                    $city = App\Models\Location\City::Where('id', $hotel_content->city_id)->first()
                                        ->name;
                                }
                                if ($hotel_content->state_id) {
                                    $State = App\Models\Location\State::Where('id', $hotel_content->state_id)->first()
                                        ->name;
                                }
                                if ($hotel_content->country_id) {
                                    $country = App\Models\Location\Country::Where(
                                        'id',
                                        $hotel_content->country_id,
                                    )->first()->name;
                                }

                              @endphp
                              <div class="rome-count">
                                <p>{{ __('Total Room') . ':' }}
                                  {{ totalHotelRoom($hotel_content->id) }}</p>
                              </div>
                              <ul class="product-info_list list-unstyled flex-column justify-content-center">
                                <li>
                                  <i class="fal fa-map-marker-alt"></i>
                                  <span>{{ @$city }}@if (@$State)
                                      ,{{ $State }}
                                      @endif @if (@$country)
                                        ,{{ @$country }}
                                      @endif
                                  </span>
                                </li>
                                <li>
                                  <div class="ratings"dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">
                                    <div class="product-ratings rate text-xsm">
                                      <div class="rating" style="width: {{ $hotel_content->average_rating * 20 }}%;">
                                      </div>
                                    </div>
                                    <span>{{ number_format($hotel_content->average_rating, 2) }}
                                      ({{ totalHotelReview($hotel_content->id) }}
                                      {{ __('Reviews') }})
                                    </span>
                                  </div>
                                </li>
                              </ul>
                              @php
                                $amenities = json_decode($hotel_content->amenities);
                                $totalAmenities = count($amenities);
                                $displayCount = 5;
                              @endphp
                              <ul class="product-icon_list justify-content-center mt-14 list-unstyled">

                                @foreach ($amenities as $index => $amenitie)
                                  @php
                                    if ($index >= $displayCount) {
                                        break;
                                    }
                                    $amin = App\Models\Amenitie::find($amenitie);
                                  @endphp
                                  <li class="list-item" data-tooltip="tooltip" data-bs-placement="bottom"
                                    aria-label="{{ $amin->title }}" data-bs-original-title="{{ $amin->title }}"
                                    aria-describedby="tooltip"><i class="{{ $amin->icon }}"></i></li>
                                @endforeach

                                @if ($totalAmenities > $displayCount)
                                  <li class="more_item_show_btn">
                                    (+{{ $totalAmenities - $displayCount }}<i class="fas fa-ellipsis-h"></i>)
                                    <div class="more_items_icons">
                                      @foreach ($amenities as $index => $amenitie)
                                        @php
                                          if ($index < $displayCount) {
                                              continue;
                                          }
                                          $amin = App\Models\Amenitie::find($amenitie);
                                        @endphp
                                        <a data-tooltip="tooltip" data-bs-placement="bottom"
                                          aria-label="{{ $amin->title }}"
                                          data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip"
                                          href="#"><i class="{{ $amin->icon }}"
                                            title="{{ $amin->title }}"></i></a>
                                      @endforeach
                                    </div>
                                  </li>
                                @endif
                              </ul>
                            </div>
                          </div>
                          <div class="product_details p-20 border-top radius-md">
                            <div class="btn-groups justify-content-center">
                              <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                                class="btn btn-md btn-primary radius-sm" title="{{ __('Details') }}"
                                target="_self">{{ __('Details') }}</a>
                            </div>
                          </div>
                        </div>
                        <!-- product-default -->
                      </div>
                    @endif
                  @endforeach
                @else
                  <h4 class="text-center mt-4 mb-4">{{ __('NO HOTEL FOUND') }}</h4>
                @endif
              </div>
            </div>
            @foreach ($categories as $category)
              @php
                $category_id = $category->id;
                $hotels = App\Models\Hotel::join('hotel_contents', 'hotel_contents.hotel_id', 'hotels.id')
                    ->where([['vendor_id', $vendor_id], ['hotels.status', '=', '1']])
                    ->where('hotel_contents.language_id', $language->id)
                    ->where('hotel_contents.category_id', $category_id)
                    ->select('hotels.*', 'hotel_contents.slug', 'hotel_contents.title')
                    ->orderBy('id', 'desc')
                    ->get();
              @endphp

              @if (count($hotels) > 0)
                <div class="tab-pane fade" id="tab_{{ $category->id }}">
                  <div class="row">
                    @php
                      $hotel_contents = App\Models\Hotel::join(
                          'hotel_contents',
                          'hotel_contents.hotel_id',
                          '=',
                          'hotels.id',
                      )
                          ->join('hotel_categories', 'hotel_categories.id', '=', 'hotel_contents.category_id')
                          ->where('hotel_contents.language_id', $language->id)
                          ->where('hotel_contents.category_id', $category->id)
                          ->where('hotels.status', '=', '1')
                          ->where('hotel_categories.status', 1)
                          ->where('hotels.vendor_id', $vendor_id)
                          ->has('room')
                          ->when('hotels.vendor_id' != '0', function ($query) {
                              return $query
                                  ->leftJoin('memberships', 'hotels.vendor_id', '=', 'memberships.vendor_id')
                                  ->where(function ($query) {
                                      $query
                                          ->where([
                                              ['memberships.status', '=', 1],
                                              ['memberships.start_date', '<=', now()->format('Y-m-d')],
                                              ['memberships.expire_date', '>=', now()->format('Y-m-d')],
                                          ])
                                          ->orWhere('hotels.vendor_id', '=', 0);
                                  });
                          })
                          ->when('hotels.vendor_id' != '0', function ($query) {
                              return $query
                                  ->leftJoin('vendors', 'hotels.vendor_id', '=', 'vendors.id')
                                  ->where(function ($query) {
                                      $query->where([['vendors.status', '=', 1]])->orWhere('hotels.vendor_id', '=', 0);
                                  });
                          })
                          ->select(
                              'hotels.*',
                              'hotel_contents.title',
                              'hotel_contents.slug',
                              'hotel_contents.city_id',
                              'hotel_contents.state_id',
                              'hotel_contents.country_id',
                              'hotel_contents.amenities',
                              'hotel_categories.name as categoryName',
                              'hotel_categories.slug as categorySlug',
                          )
                          ->orderBy('hotels.id', 'desc')
                          ->get();
                    @endphp

                    @if (count($hotel_contents) > 0)
                      @foreach ($hotel_contents as $hotel_content)
                        @php
                          $total_review = App\Models\RoomReview::where('hotel_id', $hotel_content->id)->count();
                          $today_date = now()->format('Y-m-d');
                          $feature = App\Models\HotelFeature::where('order_status', '=', 'completed')
                              ->where('hotel_id', $hotel_content->id)
                              ->whereDate('end_date', '>=', $today_date)
                              ->first();
                        @endphp
                        @if (!empty($hotel_content))
                          <div class="col-xl-4 col-lg-4 col-md-6">
                            <div class="product-default product-default-style2 border radius-md mb-25 border-primary">
                              <div class="product_top text-center">
                                <div class="p-20">
                                  <figure class="product_img mx-auto mb-15">
                                    <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                                      target="_self" title="{{ __('Link') }}">
                                      <img class="lazyload rounded-circle"
                                        data-src="{{ asset('assets/img/hotel/logo/' . $hotel_content->logo) }}"
                                        alt="Hotel">
                                    </a>
                                  </figure>
                                  @if (Auth::guard('web')->check())
                                    @php
                                      $user_id = Auth::guard('web')->user()->id;
                                      $checkWishList = checkHotelWishList($hotel_content->id, $user_id);
                                    @endphp
                                  @else
                                    @php
                                      $checkWishList = false;
                                    @endphp
                                  @endif

                                  <a href="{{ $checkWishList == false ? route('addto.wishlist.hotel', $hotel_content->id) : route('remove.wishlist.hotel', $hotel_content->id) }}"
                                    class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }} "
                                    data-tooltip="tooltip" data-bs-placement="top"
                                    title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
                                    <i class="fal fa-bookmark"></i>
                                  </a>
                                  <div class="rank-star">
                                    @for ($i = 0; $i < $hotel_content->stars; $i++)
                                      <i class="fas fa-star"></i>
                                    @endfor
                                  </div>

                                  <span class="product_subtitle">
                                    <a href="{{ route('frontend.hotels', ['category' => $hotel_content->categorySlug]) }}"
                                      target="_self"
                                      title="{{ __('Link') }}">{{ $hotel_content->categoryName }}</a>
                                  </span>
                                  <div class="title lc-1">
                                    <h4 title mb-0>
                                      <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                                        target="_self" title="{{ __('Link') }}">
                                        {{ $hotel_content->title }}
                                      </a>
                                    </h4>
                                  </div>

                                  @php
                                    $city = null;
                                    $State = null;
                                    $country = null;

                                    if ($hotel_content->city_id) {
                                        $city = App\Models\Location\City::Where('id', $hotel_content->city_id)->first()
                                            ->name;
                                    }
                                    if ($hotel_content->state_id) {
                                        $State = App\Models\Location\State::Where(
                                            'id',
                                            $hotel_content->state_id,
                                        )->first()->name;
                                    }
                                    if ($hotel_content->country_id) {
                                        $country = App\Models\Location\Country::Where(
                                            'id',
                                            $hotel_content->country_id,
                                        )->first()->name;
                                    }

                                  @endphp
                                  <div class="rome-count">
                                    <p>{{ __('Total Room') . ':' }}
                                      {{ totalHotelRoom($hotel_content->id) }}</p>
                                  </div>
                                  <ul class="product-info_list list-unstyled flex-column justify-content-center">
                                    <li>
                                      <i class="fal fa-map-marker-alt"></i>
                                      <span>{{ @$city }}@if (@$State)
                                          ,{{ $State }}
                                          @endif @if (@$country)
                                            ,{{ @$country }}
                                          @endif
                                      </span>
                                    </li>
                                    <li>
                                      <div class="ratings"dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">
                                        <div class="product-ratings rate text-xsm">
                                          <div class="rating"
                                            style="width: {{ $hotel_content->average_rating * 20 }}%;">
                                          </div>
                                        </div>
                                        <span>{{ number_format($hotel_content->average_rating, 2) }}
                                          ({{ totalHotelReview($hotel_content->id) }}
                                          {{ __('Reviews') }})
                                        </span>
                                      </div>
                                    </li>
                                  </ul>
                                  @php
                                    $amenities = json_decode($hotel_content->amenities);
                                    $totalAmenities = count($amenities);
                                    $displayCount = 5;
                                  @endphp
                                  <ul class="product-icon_list justify-content-center mt-14 list-unstyled">

                                    @foreach ($amenities as $index => $amenitie)
                                      @php
                                        if ($index >= $displayCount) {
                                            break;
                                        }
                                        $amin = App\Models\Amenitie::find($amenitie);
                                      @endphp
                                      <li class="list-item" data-tooltip="tooltip" data-bs-placement="bottom"
                                        aria-label="{{ $amin->title }}" data-bs-original-title="{{ $amin->title }}"
                                        aria-describedby="tooltip"><i class="{{ $amin->icon }}"></i></li>
                                    @endforeach

                                    @if ($totalAmenities > $displayCount)
                                      <li class="more_item_show_btn">
                                        (+{{ $totalAmenities - $displayCount }}<i class="fas fa-ellipsis-h"></i>)
                                        <div class="more_items_icons">
                                          @foreach ($amenities as $index => $amenitie)
                                            @php
                                              if ($index < $displayCount) {
                                                  continue;
                                              }
                                              $amin = App\Models\Amenitie::find($amenitie);
                                            @endphp
                                            <a data-tooltip="tooltip" data-bs-placement="bottom"
                                              aria-label="{{ $amin->title }}"
                                              data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip"
                                              href="#"><i class="{{ $amin->icon }}"
                                                title="{{ $amin->title }}"></i></a>
                                          @endforeach
                                        </div>
                                      </li>
                                    @endif
                                  </ul>
                                </div>
                              </div>
                              <div class="product_details p-20 border-top radius-md">
                                <div class="btn-groups justify-content-center">
                                  <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                                    class="btn btn-md btn-primary radius-sm" title="{{ __('Details') }}"
                                    target="_self">{{ __('Details') }}</a>
                                </div>
                              </div>
                            </div>
                            <!-- product-default -->
                          </div>
                        @endif
                      @endforeach
                    @else
                      <h4 class="text-center mt-4 mb-4">{{ __('NO HOTEL FOUND') }}</h4>
                    @endif
                  </div>
                </div>
              @endif
            @endforeach
          </div>
          @if (!empty(showAd(3)))
            <div class="text-center mb-4">
              {!! showAd(3) !!}
            </div>
          @endif
        </div>
        <div class="col-lg-3">
          <aside class="widget-area" data-aos="fade-up">
            <div class="widget-vendor mb-40 border p-3">
              <div class="vendor mb-10 text-center">
                <figure class="vendor-img mx-auto mb-15">
                  <div class="lazy-container ratio ratio-1-1 radius-md">

                    @if ($vendor_id == 0)
                      <img class="lazyload" src="assets/images/placeholder.png"
                        data-src="{{ asset('assets/img/admins/' . $vendor->image) }}" alt="Vendor">
                    @else
                      @if ($vendor->photo != null)
                        <img class="lazyload" data-src="{{ asset('assets/admin/img/vendor-photo/' . $vendor->photo) }}"
                          alt="Vendor">
                      @else
                        <img class="lazyload" data-src="{{ asset('assets/img/blank-user.jpg') }}" alt="Vendor">
                      @endif
                    @endif
                  </div>
                </figure>
                <div class="vendor-info">
                  <h5 class="mb-1">{{ $vendor->username }}</h5>
                  <span class="verification">
                    {{ $vendor->first_name ? @$vendor->first_name : @$vendorInfo->name }}
                  </span>
                </div>
              </div>

              <!-- about text -->
              @if (request()->input('admin') == true)
                @if (!is_null($vendor->details))
                  <div class="font-sm">
                    <div class="click-show">
                      <p class="text mb-0">
                        <span class="color-dark"><b>{{ __('About') . ':' }}</b></span>
                        {{ $vendor->details }}
                      </p>
                    </div>
                    <div class="read-more-btn"><span>{{ __('Read more') }}</span></div>
                  </div>
                @endif
              @else
                @if (!is_null(@$vendorInfo->details))
                  <div class="font-sm">
                    <div class="click-show">
                      <p class="text mb-0">
                        <span class="color-dark"><b>{{ __('About') . ':' }}</b></span>
                        {{ @$vendorInfo->details }}
                      </p>
                    </div>
                    <div class="read-more-btn"><span>{{ __('Read more') }}</span></div>
                  </div>
                @endif
              @endif
              <hr>
              <!-- Toggle list start -->
              <ul class="toggle-list list-unstyled mt-15" data-toggle-list="toggle-list" data-toggle-show="5">
                <li>
                  <span class="first">{{ __('Total Hotels') . ':' }}</span>
                  <span class="last">{{ $total_vendor_hotel }} </span>
                </li>

                @if ($vendor->show_email_addresss == 1)
                  <li>
                    <span class="first">{{ __('Email') . ':' }}</span>
                    <span class="last"><a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a></span>
                  </li>
                @endif

                @if ($vendor->show_phone_number == 1)
                  <li>
                    <span class="first">{{ __('Phone') }}</span>
                    <span class="last"><a
                        href="tel:{{ $vendor->phone }}">{{ $vendor->phone != null ? $vendor->phone : '-' }}</a></span>
                  </li>
                @endif

                @if (request()->input('admin') != true)
                  @if (!is_null(@$vendorInfo->city))
                    <li>
                      <span class="first">{{ __('City') . ':' }}</span>
                      <span class="last">{{ @$vendorInfo->city }}</span>
                    </li>
                  @endif

                  @if (!is_null(@$vendorInfo->state))
                    <li>
                      <span class="first">{{ __('State') . ':' }}</span>
                      <span class="last">{{ @$vendorInfo->state }}</span>
                    </li>
                  @endif

                  @if (!is_null(@$vendorInfo->country))
                    <li>
                      <span class="first">{{ __('Country') . ':' }}</span>
                      <span class="last">{{ @$vendorInfo->country }}</span>
                    </li>
                  @endif
                @endif

                @if (request()->input('admin') == true)
                  <li>
                    <span class="first">{{ __('Address') . ' : ' }}</span>
                    <span class="last">{{ $vendor->address != null ? $vendor->address : '-' }}</span>
                  </li>
                @else
                  <li>
                    <span class="first">{{ __('Address') . ' : ' }}</span>
                    <span class="last">{{ @$vendorInfo->address != null ? @$vendorInfo->address : '-' }}</span>
                  </li>
                @endif

                @if (request()->input('admin') != true)
                  @if (!is_null(@$vendorInfo->zip_code))
                    <li>
                      <span class="first">{{ __('Zip Code') . ':' }}</span>
                      <span class="last">{{ @$vendorInfo->zip_code }}</span>
                    </li>
                  @endif
                @endif


                @if (request()->input('admin') != true)
                  <li>
                    <span class="first">{{ __('Member since') . ':' }}</span>
                    <span class="last font-sm">{{ \Carbon\Carbon::parse($vendor->created_at)->format('F Y') }}</span>
                  </li>
                @endif

              </ul>
              <span class="show-more-btn" data-toggle-btn="toggleListBtn">{{ __('Show More') . '+' }} </span>
              <hr>
              <!-- Toggle list end -->
              @if ($vendor->show_contact_form == 1)
                <div class="cta-btn mt-20">
                  <button class="btn btn-lg btn-primary radius-sm w-100" data-bs-toggle="modal" data-bs-target="#contactModal"
                    type="button" aria-label="button">{{ __('Contact Now') }}</button>
                </div>
              @endif
            </div>

            @if (!empty(showAd(1)))
              <div class="text-center mb-40">
                {!! showAd(1) !!}
              </div>
            @endif
          </aside>
        </div>
      </div>
    </div>
  </div>
  <!-- Vendor-area end -->

  <!-- Contact Modal -->
  <div class="modal contact-modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title mb-0" id="contactModalLabel">{{ __('Contact Now') }}</h1>
            <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('vendor.contact.message') }}" method="POST" id="vendorContactForm">
            @csrf
            <input type="hidden" name="vendor_email" value="{{ $vendor->email }}">
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group mb-20">
                  <input type="text" class="form-control" placeholder="{{ __('Enter Your Full Name') }}"
                    name="name" required>
                  <p class="text-danger em" id="err_name"></p>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group mb-20">
                  <input type="email" class="form-control" placeholder="{{ __('Enter Your Email') }}"
                    name="email" required>
                  <p class="text-danger em" id="err_email"></p>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group mb-20">
                  <input type="text" class="form-control" placeholder="{{ __('Enter Subject') }}" name="subject"
                    required>
                  <p class="text-danger em" id="err_subject"></p>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group mb-20">
                  <textarea name="message" class="form-control"required placeholder="{{ __('Message') }}"></textarea>
                  <p class="text-danger em" id="err_message"></p>
                </div>
              </div>
              @if ($info->google_recaptcha_status == 1)
                <div class="col-md-12">
                  <div class="form-group mb-20">
                    {!! NoCaptcha::renderJs() !!}
                    {!! NoCaptcha::display() !!}
                    <p class="text-danger em" id="err_g-recaptcha-response"></p>
                  </div>
                </div>
              @endif
              <div class="col-lg-12 text-center">
                <button class="btn btn-lg btn-primary radius-sm" id="vendorSubmitBtn" type="submit"
                  aria-label="button">{{ __('Send message') }}</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
