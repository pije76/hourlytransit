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
  <!-- Hero section start-->
  <section class="hero-banner hero-banner_v2 header-next">
    <div class="container-fluid">
      <div class="swiper home-slider" id="home-slider-1" data-aos="fade-up">
        <div class="swiper-wrapper">
          @foreach ($sliderInfos as $slider)
            <div class="swiper-slide text-center">
              <div class="banner-content" data-animation="animate__slideInUp" data-delay=".1s">
                <h1 class="title color-white lc-2 mb-20">{{ $slider->title }}</h1>
                <p class="text color-white lc-2">
                  {{ $slider->text }}
                </p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="banner-filter-form mt-40 mx-auto">
        <div class="form-wrapper py-20 px-30 border">
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
                      placeholder="MM/DD/YYYY" readonly />
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
                    <input type="text" class="form-control " id="checkInTime" name="checkInTimes" placeholder="HH:MM:A"
                      readonly />
                  </div>
                </div>
              </div>
              <div class="item text-md-end">
                <button class="btn-icon" id="searchBtn2" type="button" aria-label="button">
                  <i class="far fa-search"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="swiper home-img-slider" id="home-img-slider-1">
      <div class="swiper-wrapper">

        @foreach ($sliderInfos as $slider)
          <div class="swiper-slide">
            <div class="lazyload bg-img" data-bg-img="{{ asset('assets/img/hero/sliders/' . $slider->image) }}"></div>
          </div>
        @endforeach

      </div>
    </div>
    @if (count($sliderInfos) > 1)
      <div class="swiper-pagination" id="home-slider-1-pagination"></div>
    @endif
  </section>
  <!-- Hero section end -->
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

  <!-- city-area start -->
  @if ($secInfo->city_section_status == 1)
    <section class="category-area category-area_v2 pt-100 pb-70">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="section-title title-inline mb-50" data-aos="fade-up">
              <h2 class="title">
                {{ !empty($sectionContent->city_section_title) ? $sectionContent->city_section_title : '' }}</h2>
            </div>
          </div>
          <div class="col-12">
            <div class="swiper category-slider" id="category-slider-2" data-slides-per-view="4" data-swiper-loop="true"
              data-swiper-space="25">
              <div class="swiper-wrapper">
                @foreach ($cities as $city)
                  <div class="swiper-slide">
                    <div class="card mb-30 rounded-pill">
                      <div class="card_img">
                        <a href="{{ route('frontend.hotels', ['city' => $city->id]) }}" title="{{ $city->name }}"
                          target="_self" class="lazy-container ratio ratio-1-3">
                          <img class="lazyload"
                            data-src="{{ asset('assets/img/location/city/' . $city->feature_image) }}" alt="Image">
                        </a>
                      </div>
                      <div class="card_content">
                        <h4 class="color-white mb-30 lc-1">
                          <a href="{{ route('frontend.hotels', ['city' => $city->id]) }}" target="_self"
                            title="{{ __('Link') }}">{{ $city->name }}</a>
                        </h4>
                        <a href="{{ route('frontend.hotels', ['city' => $city->id]) }}"
                          class="btn-icon bg-white rounded-circle shadow-md" title="{{ $city->name }}"
                          target="_self">
                          <i
                            class="fal {{ $currentLanguageInfo->direction == 1 ? 'fa-long-arrow-left' : 'fa-long-arrow-right' }}"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                @endforeach

              </div>
              <!-- If we need pagination -->
              <div class="swiper-pagination position-static mb-30" id="category-slider-2-pagination"></div>
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
    <section class="about-area about-area_v2 pb-60">
      <div class="container-fluid ps-0">
        <div class="row align-items-center gx-xl-5" data-aos="fade-up">
          <div class="col-lg-6">
            <div class="content-title fluid-left pb-20">
              <h2 class="title mb-20">
                {{ $sectionContent ? $sectionContent->featured_section_title : __('Your Ultimate Hourly Hotel Booking Solution') }}
              </h2>
              <p>
                {{ $sectionContent ? $sectionContent->featured_section_text : '' }}
              </p>
              <div class="info-list mt-40">
                <div class="row">
                  @foreach ($features as $feature)
                    <div class="col-sm-6">
                      <div class="card mb-20 p-20 radius-md bg-primary-light">
                        <div class="card_top">
                          <div class="card_icon bg-white radius-sm">
                            <img class="lazyload" data-src="{{ asset('assets/img/feature/' . $feature->image) }}"
                              alt="Image">
                          </div>
                          <span class="h3 lh-1 mb-1">{{ $feature->text }}</span>
                        </div>
                        <div class="card_content mt-20">
                          <p class="card_text">{{ $feature->title }}</p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="image mb-40">
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

  <!-- room-area start -->
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
                <a href="{{ route('frontend.rooms') }}" class="btn btn-lg btn-primary rounded-pill"
                  title="{{ !empty($sectionContent->featured_room_section_button_text) ? $sectionContent->featured_room_section_button_text : __('View All Rooms') }}"
                  target="_self">{{ !empty($sectionContent->featured_room_section_button_text) ? $sectionContent->featured_room_section_button_text : __('View All Rooms') }}</a>
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
  <!-- room-area end -->
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

  <!-- Counter-area start -->
  @if ($secInfo->counter_section_status == 1)
    <div class="counter-area counter-area_v2 pb-70" data-aos="fade-up">
      <div class="counter-inner bg-img bg-cover z-1"
        data-bg-img="{{ asset('assets/img/homepage/' . $images->counter_section_image) }}">
        <div class="overlay opacity-60"></div>
        <div class="video-btn-parent z-2 h-100">
          @if (@$sectionContent->counter_section_video_link)
            <a href="{{ $sectionContent->counter_section_video_link }}"
              class="video-btn video-btn-white youtube-popup mx-auto" title="{{ __('Play Video') }}">
              <i class="fas fa-play"></i>
            </a>
          @endif
        </div>
        <!-- Shapes -->
        <div class="shapes shapes-1">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <div class="shapes shapes-2">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
      <div class="counter-blocks">
        <div class="container">
          <div class="row">
            @foreach ($counters as $counter)
              <div class="col-sm-6 col-lg-3 item">
                <div class="card radius-md border text-center p-30 mb-30">
                  <div class="card_icon mb-20">
                    <img class="lazyload" data-src="{{ asset('assets/img/counter/' . $counter->image) }}"
                      alt="Image">
                  </div>
                  <div class="card_content">
                    <h2 class="card_title mb-15">
                      <span class="counter">{{ $counter->amount }}</span>+
                    </h2>
                    <p class="card_text font-lg lh-1">
                      {{ $counter->title }}
                    </p>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endif
  <!-- Counter-area end -->
  @if (count($after_counter) > 0)
    @foreach ($after_counter as $cusCounter)
      @if (isset($homecusSec[$cusCounter->id]))
        @if ($homecusSec[$cusCounter->id] == 1)
          @php
            $cusCounterContent = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $cusCounter->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          @include('frontend.home.custom-section', ['data' => $cusCounterContent])
        @endif
      @endif
    @endforeach
  @endif

  <!-- Testimonial-area start -->
  @if ($secInfo->testimonial_section_status == 1)
    <section class="testimonial-area testimonial-area_v2 pb-60" data-aos="fade-up">
      <div class="container">
        <div class="row flex-lg-nowrap gx-xl-5 align-items-center">
          <div class="col-lg-5">
            <div class="content-title mb-40 mb-lg-0">
              <h2 class="title mb-25">
                {{ !empty($sectionContent->testimonial_section_title) ? $sectionContent->testimonial_section_title : '' }}
              </h2>
              <p class="text">
                {{ !empty($sectionContent->testimonial_section_subtitle) ? $sectionContent->testimonial_section_subtitle : '' }}
              </p>
              <div class="clients-avatar mt-30">
                <div class="client-img">
                  @foreach ($testimonials as $testimonial)
                    <img class="blur-up lazyload" src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                      alt="Client Image">
                  @endforeach
                  <span>
                    {{ !empty($sectionContent->testimonial_section_clients) ? $sectionContent->testimonial_section_clients : '' }}</span>
                </div>
              </div>
              <!-- Slider navigation buttons -->
              <div class="slider-navigation mt-30">
                <button type="button" title="{{ __('Slide prev') }}" class="slider-btn rounded-circle btn-outline"
                  id="testimonial-slider-2-prev">
                  <i class="fal fa-angle-left"></i>
                </button>
                <button type="button" title="{{ __('Slide next') }}" class="slider-btn rounded-circle btn-outline"
                  id="testimonial-slider-2-next">
                  <i class="fal fa-angle-right"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="col-lg-10">
            <div class="swiper mb-40" id="testimonial-slider-2">
              <div class="swiper-wrapper row">

                @foreach ($testimonials as $testimonial)
                  <div class="swiper-slide col-lg-6">
                    <div class="slider-item border radius-md">
                      <div class="client mb-25">
                        <div class="client-info d-flex align-items-center">
                          <div class="client-img">
                            <div class="lazy-container rounded-pill ratio ratio-1-1">
                              <img class="lazyload" data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                                alt="Person Image">
                            </div>
                          </div>
                          <div class="content">
                            <h6 class="name">{{ $testimonial->name }}</h6>
                            <span class="designation">{{ $testimonial->occupation }}</span>
                          </div>
                        </div>
                        <span class="icon"><i class="fal fa-quote-right"></i></span>
                      </div>
                      <div class="quote">
                        <p class="text mb-0">
                          {{ $testimonial->comment }}
                        </p>
                      </div>
                    </div>
                  </div>
                @endforeach
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
    <section class="blog-area blog-area_v2 pb-70">
      <div class="container">
        <div class="section-title title-inline mb-50" data-aos="fade-up">
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
                  <div class="card_img">
                    <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}" target="_self"
                      title="{{ __('Link') }}" class="lazy-container radius-sm ratio ratio-2-3">
                      <img class="lazyload" src="{{ asset('assets/img/blogs/' . $blog->image) }}" alt="Blog Image">
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
                        class="btn btn-md btn-secondary rounded-pill border shadow-md icon-end"
                        title="{{ $blog->title }}" target="_self">
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

  <!-- cal to action-area start -->
  @if ($secInfo->call_to_action_section_status == 1)
    <section class="newsletter-area newsletter-area_v1 pb-100" data-aos="fade-up">
      <div class="container">
        <div class="newsletter-inner position-relative overflow-hidden z-1 px-3 ptb-70 radius-md bg-img"
          data-bg-img="{{ asset('assets/img/homepage/' . $images->call_to_action_section_image) }}">
          <div class="overlay opacity-60"></div>
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <div class="content-title text-center">
                <h2 class="title mb-30 color-white">
                  {{ $sectionContent ? $sectionContent->call_to_action_section_title : '' }}
                </h2>
                @if ($sectionContent && $sectionContent->call_to_action_button_url && $sectionContent->call_to_action_section_btn)
                  <a href="{{ $sectionContent->call_to_action_button_url }}" class="btn btn-lg btn-primary radius-sm"
                    title="{{ $sectionContent->call_to_action_section_btn }}"
                    target="_self">{{ $sectionContent->call_to_action_section_btn }}</a>
                @endif
              </div>
            </div>
          </div>
          <!-- Shapes -->
          <div class="shapes shapes-1">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <div class="shapes shapes-2">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- Call to action section- end -->
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
