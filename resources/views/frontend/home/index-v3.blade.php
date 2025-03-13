@extends('frontend.layout')

@section('pageHeading')
  {{ __('Home') }}
@endsection
@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_home }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_home }}
  @endif
@endsection

@section('content')
  <!--  Hero section start-->
  <section class="hero-banner hero-banner_v3 header-next">
    <div class="hero-wrapper bg-img bg-cover"
      data-bg-img="{{ asset('assets/img/homepage/' . $images->hero_section_image) }}">
      <div class="container">
        <div class="banner-content">
          <h1 class="title color-white mb-20">
            {{ !empty($sectionContent->hero_section_title) ? $sectionContent->hero_section_title : __('Discover the Freedom of Hourly Hotel Bookings for Relax') }}
          </h1>
          <p class="text color-white">
            {{ !empty($sectionContent->hero_section_subtitle) ? $sectionContent->hero_section_subtitle : '' }}
          </p>
        </div>
      </div>
    </div>
    <div class="hero-bottom">
      <div class="container">
        <div class="banner-filter-form mx-auto">
          <div class="form-wrapper py-20 px-30 border radius-md">
            <form action="{{ route('frontend.rooms') }}" id="searchForm2" method="GET">
              <div class="grid">
                <div class="item">
                  <div class="form-group">
                    <label for="place">{{ __('Where') }}?</label>
                    <div class="form-block">
                      <div class="icon">
                        <i class="far fa-map-marker-alt"></i>
                      </div>
                      <input type="text" name="location" class="form-control" placeholder="{{ __('Enter Address') }}"
                        id="search-address">
                      @if ($basicInfo->google_map_api_key_status == 1)
                        <button type="button" class="btn btn-primary current-location-btn mt-2 btn-sm float-right"
                          onclick="getCurrentLocationHome()">
                          <i class="fas fa-location"></i>
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="item">
                  <div class="form-group">
                    <label for="date">{{ __('When') }}?</label>
                    <div class="form-block">
                      <div class="icon">
                        <i class="far fa-calendar-alt"></i>
                      </div>
                      <input type="text" class="form-control " id="checkInDate" name="checkInDates"
                        placeholder="MM/DD/YYYY"readonly />
                    </div>
                  </div>
                </div>
                <div class="item">
                  <div class="form-group">
                    <label for="date">{{ __('Time') }}?</label>
                    <div class="form-block">
                      <div class="icon">
                        <i class="far fa-clock"></i>
                      </div>
                      <input type="text" class="form-control " id="checkInTime" name="checkInTimes"
                        placeholder="HH:MM:A"readonly />
                    </div>
                  </div>
                </div>
                <div class="item text-md-end">
                  <button class="btn-icon radius-sm" id="searchBtn2" type="button" aria-label="button">
                    <i class="far fa-search"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!--  Hero section end -->
  @if (count($after_hero) > 0)
    @foreach ($after_hero as $cusHero)
      @if (isset($homecusSec[$cusHero->id]))
        @if ($homecusSec[$cusHero->id] == 1)
          @php
            $cusHeroContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusHero->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusHeroContent])
        @endif
      @endif
    @endforeach
  @endif

  <!--city-area  start -->
  @if ($secInfo->city_section_status == 1)
    <section class="category-area category-area_v3 pt-100 pb-70">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="section-title title-inline mb-50" data-aos="fade-up">
              <h2 class="title">
                {{ !empty($sectionContent->city_section_title) ? $sectionContent->city_section_title : '' }}</h2>
            </div>
          </div>
          <div class="col-12" data-aos="fade-up">
            <div class="swiper category-slider" id="category-slider-3" data-slides-per-view="6" data-swiper-loop="true"
              data-swiper-space="25">
              <div class="swiper-wrapper">
                @foreach ($cities as $city)
                  <div class="swiper-slide">
                    <div class="card mb-30">
                      <div class="card_img mb-15">
                        <a href="{{ route('frontend.hotels', ['city' => $city->id]) }}" title="{{ $city->name }}"
                          target="_self" class="lazy-container ratio ratio-1-1 radius-md">
                          <img class="lazyload"
                            data-src="{{ asset('assets/img/location/city/' . $city->feature_image) }}" alt="Image">
                        </a>
                      </div>
                      <div class="card_content text-center">
                        <h4 class="card_title mb-0 lc-1">
                          <a href="{{ route('frontend.hotels', ['city' => $city->id]) }}" title="{{ $city->name }}"
                            target="_self">
                            {{ $city->name }}
                          </a>
                        </h4>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
              <!-- If we need pagination -->
              <div class="swiper-pagination position-static mb-30" id="category-slider-3-pagination"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- city-area end -->
  @if (count($after_city) > 0)
    @foreach ($after_city as $cusCity)
      @if (isset($homecusSec[$cusCity->id]))
        @if ($homecusSec[$cusCity->id] == 1)
          @php
            $cusCityContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusCity->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusCityContent])
        @endif
      @endif
    @endforeach
  @endif


  <!-- feature-area start -->
  @if ($secInfo->featured_section_status == 1)
    <section class="about-area about-area_v3 bg-primary-light">
      <div class="container-fluid">
        <div class="row align-items-center gx-xl-5" data-aos="fade-up">
          <div class="col-lg-5">
            <div class="content-title fluid-left ptb-70">
              <h2 class="title mb-20">
                {{ $sectionContent ? $sectionContent->featured_section_title : __('Your Ultimate Hourly Hotel Booking Solution') }}
              </h2>
              <div class="info-list pt-10">
                @foreach ($features as $feature)
                  <div class="card mt-20">
                    <div class="card_icon">
                      <img class="lazyload" data-src="{{ asset('assets/img/feature/' . $feature->image) }}"
                        alt="Image">
                    </div>
                    <div class="card_content">
                      <span class="h4 lh-1 mb-1">{{ $feature->title }}</span>
                      <p class="card_text">{{ $feature->subtitle }}</p>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="image">
              <img class="lazyload blur-up"
                data-src="{{ asset('assets/img/homepage/' . $images->feature_section_image) }}" alt="Image">
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- feature-area end -->
  @if (count($after_featured) > 0)
    @foreach ($after_featured as $cusFeature)
      @if (isset($homecusSec[$cusFeature->id]))
        @if ($homecusSec[$cusFeature->id] == 1)
          @php
            $cusFeatureContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusFeature->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusFeatureContent])
        @endif
      @endif
    @endforeach
  @endif

  <!-- Benifit start-->
  @if ($secInfo->benifit_section_status == 1)
    <section class="gallery-area gallery-area_v1 pt-100 pb-70">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="section-title title-inline mb-50" data-aos="fade-up">
              <h2 class="title">
                {{ !empty($sectionContent->benifit_section_title) ? $sectionContent->benifit_section_title : __('Benefit of Hourly Stay') }}
              </h2>
            </div>
          </div>
          <div class="col-12">
            <div class="row">
              @if (count($benifits) < 1)
                <div class="p-3 text-center bg-light radius-md">
                  <h6 class="mb-0">{{ __('NO BENIFIT FOUND') }}</h6>
                </div>
              @else
                @foreach ($benifits as $benifit)
                  <div class="col-xl-3 col-md-6">
                    <div class="card mb-30 radius-md">
                      <div class="card_img">
                        <a href="#" title="{{ __('Title') }}" target="_self"
                          class="lazy-container ratio ratio-1-3">
                          <img class="lazyload"
                            data-src="{{ asset('assets/img/benifits/' . $benifit->background_image) }}" alt="Image">
                        </a>
                      </div>
                      <div class="card_content p-20">
                        <h4 class="card_title mb-10 color-white">
                          <a href="#" target="_self" title="{{ $benifit->title }}"
                            rel="noopener noreferrer">{{ $benifit->title }}</a>
                        </h4>
                        <p class="card_text color-light">{{ $benifit->text }}</p>
                      </div>
                    </div>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- Benifit-area end -->
  @if (count($after_benifit) > 0)
    @foreach ($after_benifit as $cusBenifit)
      @if (isset($homecusSec[$cusBenifit->id]))
        @if ($homecusSec[$cusBenifit->id] == 1)
          @php
            $cusBenifitContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusBenifit->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusBenifitContent])
        @endif
      @endif
    @endforeach
  @endif

  <!-- room-area  start -->
  @if ($secInfo->featured_room_section_status == 1)
    <section class="product-area featured-product pb-75">
      <div class="container">
        <div class="row">
          <div class="col-12" data-aos="fade-up">
            <div class="section-title title-inline mb-50">
              <div>
                <h2 class="title">
                  {{ !empty($sectionContent->featured_room_section_title) ? $sectionContent->featured_room_section_title : __('Popular Hotel Room') }}
                </h2>
              </div>
              @if (count($room_contents_count) > count($room_contents))
                <a href="javaScript:void(0)" class="btn btn-lg btn-primary rounded-pill"
                  title="{{ __('View All Room') }}" target="_self">{{ __('View All Room<') }}/a>
              @endif

            </div>
          </div>
          <div class="col-12" data-aos="fade-up">
            <div class="row">
              @if (count($room_contents) < 1)
                <div class="p-3 text-center bg-light radius-md">
                  <h6 class="mb-0">{{ __('NO FEATURED ROOM FOUND') }}</h6>
                </div>
              @else
                @foreach ($room_contents as $room)
                  <div class="col-xl-4 col-md-6">
                    <div class="product-default border radius-md mb-25">
                      <!-- product image -->
                      <figure class="product_img">
                        <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}"
                          target="_self" title="{{ __('Link') }}" class="lazy-container ratio ratio-2-3 radius-sm">
                          <img class="lazyload"
                            src="{{ asset('assets/img/room/featureImage/' . $room->feature_image) }}" alt="Room">
                        </a>
                        @if (Auth::guard('web')->check())
                          @php
                            $user_id = Auth::guard('web')->user()->id;
                            $checkWishList = checkroomWishList($room->id, $user_id);
                          @endphp
                        @else
                          @php
                            $checkWishList = false;
                          @endphp
                        @endif

                        <a href="{{ $checkWishList == false ? route('addto.wishlist.room', $room->id) : route('remove.wishlist.room', $room->id) }}"
                          class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }}"
                          data-tooltip="tooltip" data-bs-placement="top"
                          title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
                          <i class="fal fa-heart"></i>
                        </a>
                        <div class="rank-star">
                          @for ($i = 0; $i < $room->stars; $i++)
                            <i class="fas fa-star"></i>
                          @endfor
                        </div>
                      </figure>

                      <div class="product_details">
                        <div class="p-20">
                          <div class="product_title">
                            <h4 class="title lc-1 mb-0">
                              <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}"
                                target="_self" title="{{ __('Link') }}">{{ $room->title }}</a>
                            </h4>
                          </div>
                          @php
                            $city = null;
                            $State = null;
                            $country = null;

                            if ($room->city_id) {
                                $city = App\Models\Location\City::Where('id', $room->city_id)->first()->name;
                            }
                            if ($room->state_id) {
                                $State = App\Models\Location\State::Where('id', $room->state_id)->first()->name;
                            }
                            if ($room->country_id) {
                                $country = App\Models\Location\Country::Where('id', $room->country_id)->first()->name;
                            }
                          @endphp
                          <ul class="list-unstyled mt-15">
                            <li class="icon-start location mb-2">
                              <i class="fal fa-map-marker-alt"></i>
                              <span>
                                {{ @$city }}@if (@$State)
                                  , {{ $State }}
                                  @endif @if (@$country)
                                    , {{ $country }}
                                  @endif
                              </span>
                            </li>
                            <li>
                              <div class="ratings"dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">
                                <div class="product-ratings rate text-xsm">
                                  <div class="rating" style="width: {{ $room->average_rating * 20 }}%;"></div>
                                </div>
                                <span>
                                  {{ number_format($room->average_rating, 2) }}
                                  ({{ totalRoomReview($room->id) }}
                                  {{ totalRoomReview($room->id) > 1 ? __('Reviews') : __('Review') }})
                                </span>
                              </div>
                            </li>
                          </ul>
                          <div class="product_author mt-15">
                            <a class="d-flex align-items-center gap-1"
                              href="{{ route('frontend.hotel.details', ['slug' => $room->hotelSlug, 'id' => $room->hotelId]) }}"
                              target="_self" title="{{ __('Link') }}">
                              <img class="lazyload blur-up"
                                src="{{ asset('assets/img/hotel/logo/' . $room->hotelImage) }}"alt="{{ __('Image') }}">
                              <span class="underline lc-1" data-tooltip="tooltip" data-bs-placement="bottom"
                                aria-label="{{ $room->hotelName }}" data-bs-original-title="{{ $room->hotelName }}"
                                aria-describedby="tooltip">
                                {{ $room->hotelName }}
                              </span>
                            </a>
                          </div>
                          @php
                            $amenities = json_decode($room->amenities);
                            $totalAmenities = count($amenities);
                            $displayCount = 5;
                          @endphp

                          <ul class="product-icon_list mt-15 list-unstyled">
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
                                      aria-label="{{ $amin->title }}" data-bs-original-title="{{ $amin->title }}"
                                      aria-describedby="tooltip" href="#"><i class="{{ $amin->icon }}"
                                        title="{{ $amin->title }}"></i></a>
                                  @endforeach
                                </div>
                              </li>
                            @endif
                          </ul>
                        </div>
                        <div class="product_bottom p-20 border-top text-center">
                          <ul class="product-price_list list-unstyled">
                            @php
                              $prices = App\Models\HourlyRoomPrice::where('room_id', $room->id)
                                  ->where('hourly_room_prices.price', '!=', null)
                                  ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                                  ->orderBy('booking_hours.serial_number')
                                  ->select('hourly_room_prices.*', 'booking_hours.serial_number')
                                  ->get();
                            @endphp
                            @foreach ($prices as $price)
                              <li class="radius-sm">
                                <span class="h6 mb-1">{{ symbolPrice($price->price) }}</span>
                                <span>{{ $price->hour }} {{ __('Hrs') }}</span>
                              </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                    <!-- product-default -->
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- room-area  end -->
  @if (count($after_featured_room) > 0)
    @foreach ($after_featured_room as $cusFeatureRoom)
      @if (isset($homecusSec[$cusFeatureRoom->id]))
        @if ($homecusSec[$cusFeatureRoom->id] == 1)
          @php
            $cusFeatureRoomContent = App\Models\HomePage\CustomSectionContent::where(
                'custom_section_id',
                $cusFeatureRoom->id,
            )
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusFeatureRoomContent])
        @endif
      @endif
    @endforeach
  @endif

  <!-- Testimonial-area start -->
  @if ($secInfo->testimonial_section_status == 1)
    <section class="testimonial-area testimonial-area_v3 ptb-100 bg-img bg-cover"
      data-bg-img="{{ asset('assets/img/homepage/' . $images->testimonial_section_image) }}">
      <div class="overlay opacity-80"></div>
      <div class="container">
        <div class="wrapper">
          <div class="row justify-content-center">
            <div class="col-lg-10">
              <div class="swiper" id="testimonial-slider-1">
                <div class="swiper-wrapper">
                  @foreach ($testimonials as $testimonial)
                    <div class="swiper-slide">
                      <div class="slider-item text-center">
                        <div class="quote mb-20">
                          <p class="text color-white">
                            {{ $testimonial->comment }}
                          </p>
                        </div>
                        <div class="client-img mb-20 mx-auto">
                          <div class="lazy-container rounded-circle ratio ratio-1-1">
                            <img class="lazyload" data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                              alt="Person Image">
                          </div>
                        </div>
                        <div class="client-info">
                          <h6 class="name color-white">{{ $testimonial->name }}</h6>
                          <span class="designation color-light">{{ $testimonial->occupation }}</span>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div class="swiper-pagination swiper-pagination_white position-static mt-20"
                  id="testimonial-slider-1-pagination"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- Testimonial-area end -->

  @if (count($after_testimonial) > 0)
    @foreach ($after_testimonial as $cusTestimonial)
      @if (isset($homecusSec[$cusTestimonial->id]))
        @if ($homecusSec[$cusTestimonial->id] == 1)
          @php
            $cusTestimonialContent = App\Models\HomePage\CustomSectionContent::where(
                'custom_section_id',
                $cusTestimonial->id,
            )
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusTestimonialContent])
        @endif
      @endif
    @endforeach
  @endif

  <!-- Blog-area start -->
  @if ($secInfo->blog_section_status == 1)
    <section class="blog-area blog-area_v3 pt-100 pb-70">
      <div class="container">
        <div class="section-title title-inline mb-20" data-aos="fade-up">
          <h2 class="title">
            {{ !empty($sectionContent->blog_section_title) ? $sectionContent->blog_section_title : __('Read Our Latest Blog') }}
          </h2>
          @if (count($blog_count) > count($blogs))
            <a href="{{ route('blog') }}" class="btn btn-lg btn-primary radius-sm"
              title="{{ $sectionContent ? $sectionContent->blog_section_button_text : __('Read All Post') }}"
              target="_self">
              {{ $sectionContent ? $sectionContent->blog_section_button_text : __('Read All Post') }}
            </a>
          @endif
        </div>
        <div class="row" data-aos="fade-up">
          @if (count($blogs) < 1)
            <div class="p-3 text-center bg-light radius-md">
              <h6 class="mb-0">{{ __('NO POST FOUND') }}</h6>
            </div>
          @else
            @foreach ($blogs as $blog)
              <div class="col-md-6 col-xl-4">
                <article class="card border radius-md mb-30">
                  <div class="card_img bg-primary-light radius-md">
                    <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}" target="_self"
                      title="{{ __('Link') }}" class="lazy-container radius-sm ratio ratio-2-3">
                      <img class="lazyload" data-src="{{ asset('assets/img/blogs/' . $blog->image) }}"
                        alt="Blog Image">
                    </a>
                  </div>
                  <div class="card_content p-20">
                    <h4 class="card_title lc-2 mb-15">
                      <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}" target="_self"
                        title="{{ __('Link') }}">
                        {{ $blog->title }}
                      </a>
                    </h4>
                    <p class="card_text lc-2">
                      {{ strlen(strip_tags(convertUtf8($blog->content))) > 100 ? substr(strip_tags(convertUtf8($blog->content)), 0, 100) . '...' : strip_tags(convertUtf8($blog->content)) }}
                    </p>
                    <div class="cta-btn mt-20">
                      <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}"
                        class="btn btn-lg btn-secondary radius-sm shadow-md icon-end" title=" {{ $blog->title }}"
                        target="_self">
                        <span>{{ __('Read More') }}</span>
                        <i
                          class="fal {{ $currentLanguageInfo->direction == 1 ? 'fa-long-arrow-left' : 'fa-long-arrow-right' }}"></i>
                      </a>
                    </div>
                  </div>
                </article>
              </div>
            @endforeach
          @endif
        </div>
      </div>
    </section>
  @endif
  <!-- Blog-area end -->
  @if (count($after_blog) > 0)
    @foreach ($after_blog as $cusBlog)
      @if (isset($homecusSec[$cusBlog->id]))
        @if ($homecusSec[$cusBlog->id] == 1)
          @php
            $cusBlogContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusBlog->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusBlogContent])
        @endif
      @endif
    @endforeach
  @endif

  <!--cal to action-area start -->
  @if ($secInfo->call_to_action_section_status == 1)
    <section class="newsletter-area newsletter-area_v2 pb-100" data-aos="fade-up">
      <div class="container">
        <div class="newsletter-inner position-relative overflow-hidden z-1 px-3 ptb-30 radius-md bg-img bg-cover"
          data-bg-img="{{ asset('assets/img/homepage/' . $images->call_to_action_section_image) }}">
          <div class="overlay"></div>
          <div class="row align-items-center">
            <div class="col-lg-6">
              <div class="content-title ps-3">
                <h2 class="title mb-30 color-white">
                  {{ $sectionContent ? $sectionContent->call_to_action_section_title : '' }}
                </h2>
                @if ($sectionContent && $sectionContent->call_to_action_button_url && $sectionContent->call_to_action_section_btn)
                  <a href="{{ $sectionContent->call_to_action_button_url }}" class="btn btn-lg btn-outline radius-sm"
                    title="{{ $sectionContent->call_to_action_section_btn }}"
                    target="_self">{{ $sectionContent->call_to_action_section_btn }}</a>
                @endif
              </div>
            </div>
            <div class="col-lg-6">
              <div class="image mt-lg-0 text-lg-end">
                <img class="lazyload"
                  data-src="{{ asset('assets/img/homepage/' . $images->call_to_action_section_inner_image) }}"
                  alt="Image">
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- cal to action-area end -->
  @if (count($after_call_to_action) > 0)
    @foreach ($after_call_to_action as $cusAction)
      @if (isset($homecusSec[$cusAction->id]))
        @if ($homecusSec[$cusAction->id] == 1)
          @php
            $cusActionContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusAction->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusActionContent])
        @endif
      @endif
    @endforeach
  @endif

  <!-- Modal -->
  @include('frontend.partials.map-modal')

@endsection
@section('script')
  @if ($basicInfo->google_map_api_key_status == 1)
    <script src="{{ asset('assets/front/js/map-init.js') }}"></script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key={{ $basicInfo->google_map_api_key }}&libraries=places&callback=initMap"
      async defer></script>
  @endif
  <script src="{{ asset('assets/front/js/search-home.js') }}"></script>
@endsection
