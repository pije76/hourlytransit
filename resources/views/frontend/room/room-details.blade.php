@extends('frontend.layout')

@section('pageHeading')
  {{ $roomContent->title }}
@endsection

@section('metaKeywords')
  @if (!empty($roomContent))
    {{ $roomContent->meta_keyword }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($roomContent))
    {{ $roomContent->meta_description }}
  @endif
@endsection

@section('ogTitle')
  @if (!empty($roomContent))
    {{ $roomContent->title }}
  @endif
@endsection

@section('content')
  <!-- Breadcrumb start -->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' =>
          !strlen(@$roomContent->title) > 35
              ? mb_substr(@$roomContent->title, 0, 35, 'utf-8') . '...'
              : @$roomContent->title,
  ])
  <!-- Breadcrumb end -->

  <!-- listing-details-area start -->
  <div class="listing-details-area pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        <div class="col-lg-8">
          <div class="product-single-gallery radius-md gallery-popup mb-40" data-aos="fade-up">
            <div class="swiper product-single-slider">
              <div class="swiper-wrapper">
                @foreach ($roomImages as $gallery)
                  <div class="swiper-slide">
                    <figure class="lazy-container ratio ratio-5-3">
                      <a href="{{ asset('assets/img/room/room-gallery/' . $gallery->image) }}" class="lightbox-single">
                        <img class="lazyload" src="{{ asset('assets/img/room/room-gallery/' . $gallery->image) }}"
                          data-src="{{ asset('assets/img/room/room-gallery/' . $gallery->image) }}"
                          alt="{{ __('room image') }}">
                      </a>
                    </figure>
                  </div>
                @endforeach
              </div>

              <!-- Slider navigation buttons -->
              <div class="slider-navigation">
                <button type="button" title="{{ __('Slide prev') }}" class="slider-btn slider-btn-prev rounded-circle">
                  <i class="fal fa-angle-left"></i>
                </button>
                <button type="button" title="{{ __('Slide next') }}" class="slider-btn slider-btn-next rounded-circle">
                  <i class="fal fa-angle-right"></i>
                </button>
              </div>

            </div>

            <div class="product-thumb">
              <div class="swiper slider-thumbnails">
                <div class="swiper-wrapper">

                  @foreach ($roomImages as $gallery)
                    <div class="swiper-slide">
                      <div class="thumbnail-img lazy-container ratio ratio-5-3 radius-sm">
                        <img class="lazyload radius-sm"
                          src="{{ asset('assets/img/room/room-gallery/' . $gallery->image) }}"
                          data-src="{{ asset('assets/img/room/room-gallery/' . $gallery->image) }}" alt="room image">
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
          <div class="product-single-details mb-40" data-aos="fade-up">
            <div class="product-info">
              <span class="product_subtitle color-primary">
                <a href="{{ route('frontend.rooms', ['category' => $roomContent->categorySlug]) }}" target="_self"
                  title="{{ __('Link') }}">{{ $roomContent->categoryName }}</a>
              </span>
              <div class="d-flex align-items-center mb-10 gap-3">
                <h3 class="product-title mb-0">{{ $roomContent->title }} </h3>
                <div class="rank-star d-flex align-items-center flex-wrap gap-2 mb-0 mt-1">
                  <div class="icons fs-6">
                    <i class="fas fa-star"></i>.
                  </div>
                  <span class="fw-semibold fs-5 text-nowrap">{{ $roomContent->stars }} {{ __('Star') }}</span>
                </div>
              </div>

              <div class="d-flex row-gap-2 column-gap-2 flex-wrap mb-15">

                <div class="hotel_author pe-2 border-end">
                  <a class="d-flex align-items-center gap-1"
                    href="{{ route('frontend.hotel.details', ['slug' => $roomContent->hotelSlug, 'id' => $roomContent->hotelId]) }}"
                    target="_self" title="{{ __('Link') }}">
                    <img class="blur-up ls-is-cached lazyloaded"
                      src="{{ asset('assets/img/hotel/logo/' . $roomContent->hotellogo) }}" alt="{{ __('Image') }}">
                    <span class="lc-1 font-sm">{{ $roomContent->hoteltitle }}</span>
                  </a>
                </div>

                <div class="vendore_author">
                  <a href="{{ route('frontend.vendor.details', ['username' => $userName]) }}" target="_self"
                    title="{{ $userName }}">
                    @if ($roomContent->vendor_id == 0)
                      <img class="ls-is-cached lazyloaded" src="{{ asset('assets/img/admins/' . $vendor->image) }}"
                        alt="{{ __('Vendor') }}">
                    @else
                      @if ($vendor->photo)
                        <img class="ls-is-cached lazyloaded"
                          src="{{ asset('assets/admin/img/vendor-photo/' . $vendor->photo) }}"
                          alt="{{ __('Vendor') }}">
                      @else
                        <img class="ls-is-cached lazyloaded" src="{{ asset('assets/front/images/avatar-1.jpg') }}"
                          alt="{{ __('Vendor') }}">
                      @endif
                    @endif
                    <span class="font-sm">{{ __('By') }} {{ $userName }}</span>
                  </a>
                </div>
              </div>

              <div class="product-info_list list-unstyled">
                <li class="location">
                  <i class="fal fa-map-marker-alt"></i>
                  <span>
                    {{ $roomContent->address }}
                  </span>
                </li>
                <li>
                  <div class="ratings"dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">
                    <div class="product-ratings rate text-xsm">
                      <div class="rating" style="width: {{ $roomContent->average_rating * 20 }}%;"></div>
                    </div>
                    <span>{{ number_format($roomContent->average_rating, 2) }}
                      ({{ $numOfReview }}
                      {{ __('Reviews') }})
                    </span>
                  </div>
                </li>
                <li>
                  <ul class="list-unstyled">
                    <li>
                      <i class="fal fa-square"></i>
                      <span>{{ $roomContent->area }}m<sup>2</sup></span>
                    </li>
                    <li>
                      <i class="fas fa-user-friends"></i>
                      <span>
                        {{ $roomContent->adult }} {{ $roomContent->adult == 1 ? __('Adult') : __('Adults') }}
                      </span>
                    </li>
                    <li>
                      <i class="fas fa-baby"></i>
                      <span>
                        {{ $roomContent->children }} {{ $roomContent->children == 1 ? __('Child') : __('Children') }}
                      </span>
                    </li>
                    <li>
                      <i class="fal fa-bed"></i>
                      <span>
                        {{ $roomContent->bed }} {{ $roomContent->bed == 1 ? __('Bed') : __('Beds') }}
                      </span>
                    </li>
                    <li>
                      <i class="fal fa-bath"></i>
                      <span>
                        {{ $roomContent->bathroom }}
                        {{ $roomContent->bathroom == 1 ? __('Bathroom') : __('Bathrooms') }}
                      </span>
                    </li>
                  </ul>
                </li>
              </div>
            </div>

            <div class="tabs-navigation tabs-navigation_v2 mt-40">
              <ul class="nav nav-tabs" data-hover="fancyHover">
                <li class="nav-item active">
                  <button class="nav-link hover-effect btn-lg active" data-bs-toggle="tab" data-bs-target="#tab1"
                    type="button">{{ __('Overview') }}</button>
                </li>
                @if ($roomContent->amenities != '[]')
                  <li class="nav-item">
                    <button class="nav-link hover-effect btn-lg" data-bs-toggle="tab" data-bs-target="#tab2"
                      type="button">{{ __('Amenities') }}</button>
                  </li>
                @endif
                <li class="nav-item">
                  <button class="nav-link hover-effect btn-lg" id="locationBtn" data-bs-toggle="tab"
                    data-bs-target="#tab3" type="button">{{ __('Location') }}</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link hover-effect btn-lg" data-bs-toggle="tab" data-bs-target="#tab4"
                    type="button">{{ __('Review') }}</button>
                </li>
              </ul>
            </div>

            <div class="tab-content mt-40">
              <!-- Product Overview -->
              <div class="tab-pane slide show active" id="tab1">
                <!-- Product description -->
                <div class="tinymce-content">
                  {!! optional($roomContent)->description !!}
                </div>
              </div>
              @if ($roomContent->amenities != '[]')
                <div class="tab-pane slide" id="tab2">
                  <div class="product-amenities" data-aos="fade-up">
                    <h4 class="title mb-20">{{ __('Room Amenities') }}</h4>
                    <ul class="amenities-list list-unstyled p-20 radius-md border">
                      @php
                        $amenities = json_decode($roomContent->amenities);
                      @endphp

                      @foreach ($amenities as $amenitie)
                        @php
                          $amin = App\Models\Amenitie::find($amenitie);
                        @endphp
                        <li class="icon-start">
                          <i class="{{ $amin->icon }}"></i>
                          <span>{{ $amin->title }}</span>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              @endif

              <!-- Product location -->
              <div class="tab-pane slide" id="tab3">
                <div class="product-location">
                  <h4 class="title mb-20">{{ __('Location') }}</h4>
                  <div class="mb-20">
                    <i class="fal fa-map-marker-alt"></i>
                    <span>{{ $roomContent->address }}</span>
                  </div>
                  <div class="lazy-container radius-md ratio">
                    <div id="map"></div>
                  </div>
                </div>
              </div>

              <!-- Product Review -->
              <div class="tab-pane slide" id="tab4">


                <div class="product-review" data-aos="fade-up">
                  <div class="review-progresses p-30 radius-md border mb-40">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-30">
                      <h4 class="mb-0">{{ __('Average rating') }}</h4>
                      <div class="ratings size-md">
                        <div class="rate bg-img" data-bg-image="{{ asset('assets/img/rate-star-md.png') }}">
                          <div class="rating-icon bg-img" data-bg-image="{{ asset('assets/img/rate-star-md.png') }}">
                          </div>
                        </div>
                        <span class="ratings-total"><b
                            class="bold-text">{{ number_format($roomContent->average_rating, 2) }}</b></span>
                      </div>
                    </div>
                    @php
                      $total_review = App\Models\RoomReview::where('room_id', $roomContent->id)->count();
                      $ratings = [
                          5 => 'Excellent',
                          4 => 'Good',
                          3 => 'Average',
                          2 => 'Poor',
                          1 => 'Bad',
                      ];
                    @endphp
                    @foreach ($ratings as $rating => $label)
                      @php
                        $totalReviewForRating = App\Models\RoomReview::where('room_id', $roomContent->id)
                            ->where('rating', $rating)
                            ->count();
                        $percentage = $total_review > 0 ? round(($totalReviewForRating / $total_review) * 100) : 0;
                      @endphp

                      <!-- percentage grid start-->
                      <div class="review-progress mb-10 review-progress-grid">
                        <div class="rating-icon-area">
                          <div class="review-ratings rate">
                            <div class="rating" style="width: {{ 20 * $rating }}%;"></div>
                          </div>
                          <p class="mb-0">
                            {{ $rating }} {{ $rating == 1 ? __('Star') : __('Stars') }}
                          </p>
                        </div>
                        <div class="progress-line">
                          <div class="progress">
                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"
                              role="progressbar" aria-label="Basic example" aria-valuenow="{{ $percentage }}"
                              aria-valuemin="0" aria-valuemax="100">
                            </div>
                          </div>
                        </div>

                        <div class="percentage-area">
                          <div class="percentage">{{ $percentage }}%</div>
                        </div>
                      </div>
                      <!-- percentage grid end-->
                    @endforeach
                  </div>
                  <div class="review-box pb-10">
                    @foreach ($reviews as $review)
                      <div class="review-list mb-30 border radius-md">
                        <div class="review-item p-30">
                          <div class="review-header flex-wrap mb-20">
                            <div class="author d-flex align-items-center justify-content-between gap-3">
                              <div class="author-img">
                                @if (empty($review->user->image))
                                  <img class="lazyload  ratio ratio-1-1 rounded-circle"
                                    data-src="{{ asset('assets/img/user.png') }}" alt="Avatar">
                                @else
                                  <img class="lazyload  ratio ratio-1-1 rounded-circle"
                                    data-src="{{ asset('assets/img/users/' . $review->user->image) }}" alt="Avatar">
                                @endif
                              </div>
                              <div class="author-info">
                                <h6 class="mb-1">
                                  <a href="#" target="_self"
                                    title="{{ __('Link') }}">{{ $review->user->username }}</a>
                                </h6>
                                <div class="ratings mb-1"dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">
                                  <div class="rate" style="background-image: url('{{ asset($rateStar) }}')">
                                    <div class="rating-icon"
                                      style="background-image:url('{{ asset($rateStar) }}'); width: {{ $review->rating * 20 . '%;' }}">
                                    </div>
                                  </div>
                                  <span class="ratings-total">({{ $review->rating }})</span>
                                </div>
                                <span class="font-xsm icon-start">
                                  <span class="color-green">
                                    <i class="fas fa-badge-check"></i>
                                  </span>
                                  {{ __('Verified User') }}
                                </span>
                              </div>
                            </div>
                            <div class="more-info font-sm">
                              @if ($review->user->address)
                                <div class="icon-start">
                                  <i class="fal fa-map-marker-alt"></i>
                                  {{ $review->user->address }}
                                </div>
                              @endif

                              <div class="icon-start">
                                <i class="fal fa-clock"></i>
                                {{ $review->updated_at->diffForHumans() }}
                              </div>
                            </div>
                          </div>
                          <p>{{ $review->review }}
                          </p>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>

                @auth('web')
                  <div class="review-form radius-lg mb-40">
                    <h3 class="mb-10">{{ __('Write a Review') }}</h3>
                    <form action="{{ route('frontend.room.room_details.store_review', ['id' => $roomContent->id]) }}"
                      method="POST" id="reviewSubmitForm">
                      @csrf
                      <div class="row">
                        <div class="col-12">
                          <div class="form-group mb-20">
                            <textarea class="form-control" name="review" id="review" cols="30" rows="9"
                              placeholder="{{ __('Write your review') }}"></textarea>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="mb-1">{{ __('Rating') . '*' }}</label>
                        <ul class="list-unstyled mb-20">
                          <li class="review-value review-1">
                            <span class="fas fa-star" data-ratingVal="1"></span>
                          </li>
                          <li class="review-value review-2">
                            <span class="fas fa-star" data-ratingVal="2"></span>
                            <span class="fas fa-star" data-ratingVal="2"></span>
                          </li>
                          <li class="review-value review-3">
                            <span class="fas fa-star" data-ratingVal="3"></span>
                            <span class="fas fa-star" data-ratingVal="3"></span>
                            <span class="fas fa-star" data-ratingVal="3"></span>
                          </li>
                          <li class="review-value review-4">
                            <span class="fas fa-star" data-ratingVal="4"></span>
                            <span class="fas fa-star" data-ratingVal="4"></span>
                            <span class="fas fa-star" data-ratingVal="4"></span>
                            <span class="fas fa-star" data-ratingVal="4"></span>
                          </li>
                          <li class="review-value review-5">
                            <span class="fas fa-star" data-ratingVal="5"></span>
                            <span class="fas fa-star" data-ratingVal="5"></span>
                            <span class="fas fa-star" data-ratingVal="5"></span>
                            <span class="fas fa-star" data-ratingVal="5"></span>
                            <span class="fas fa-star" data-ratingVal="5"></span>
                          </li>
                        </ul>
                      </div>
                      <input type="hidden" id="rating-id" name="rating">

                      <div class="form-group mt-10">
                        <button type="submit" class="btn btn-lg btn-primary">{{ __('Submit Review') }}</button>
                      </div>
                    </form>

                  </div>
                @endauth
                @guest('web')
                  <div class="login-text mb-40">
                    <span>{{ __('Please') }} <a href="{{ route('user.login', ['redirectPath' => 'roomDetails']) }}"
                        title="{{ __('Login') }}">{{ __('Login') }}</a>
                      {{ __('To Give Your Review') }}
                      .</span>
                  </div>
                @endguest
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <aside class="widget-area" data-aos="fade-up">
            <div class="widget widget-booking border radius-md mb-40">
              <div class="p-20 border-bottom">
                <h4 class="title">
                  {{ __('Book Here') }}
                </h4>
              </div>
              <form id="roomCheckoutForm" class="subscription" action="{{ route('frontend.room.go.checkout') }}"
                method="POST">
                @csrf
                <div class="date-form">
                  <div class="form-group">
                    <label for="checkInDate">{{ __('Check-in Date') }}</label>
                    <input type="text" class="form-control" id="checkInDate" name="checkInDate"
                      value="{{ old('checkInDate', \Carbon\Carbon::parse($checkinDate)->format('m/d/Y')) }}"
                      placeholder="MM/DD/YYYY"autocomplete="off"readonly/>
                  </div>
                  <div class="form-group">
                    <label for="checkInTime">{{ __('Check-in Time') }}</label>
                    <input type="text" class="form-control " id="checkInTime" name="checkInTime"
                      value="{{ old('checkInTime') }}" placeholder="HH:MM:A" autocomplete="off"readonly/>
                  </div>

                </div>
                <div class="type-form">
                  <div class="search-container">
                    @if (count($hourlyPrices) > 0)
                      <ul class="list-group custom-radio">
                        @foreach ($hourlyPrices as $hourlyPrice)
                          @php
                            $price = App\Models\BookingHour::find($hourlyPrice->hour_id);
                          @endphp
                          <li>
                            <input class="input-radio" type="radio" name="price"
                              id="radio_{{ $hourlyPrice->id }}" value="{{ $hourlyPrice->id }}">
                            <label class="form-radio-label" for="radio_{{ $hourlyPrice->id }}">
                              <span> {{ $price->hour }} {{ __('Hrs') }}</span>
                              <span class="qty"> {{ symbolPrice($hourlyPrice->price) }}</span>
                            </label>
                          </li>
                        @endforeach
                      </ul>
                    @else
                      <h6 class="mt-2 text-warning ps-3 pb-2">{{ __('No booking slot available') }}</h6>
                    @endif
                  </div>
                </div>
                <div class="p-20 bg-primary-light">
                  <div class="row mb-20">
                    <div class="col-md-6 col-lg-12 col-xl-6">
                      <div class="form-group">
                        <label for="adult">{{ __('Total Adults') }}</label>
                        <select class="form-select" id="adult"name="adult">
                          <option value="" disabled>{{ __('Adult') }}</option>
                          @for ($i = 1; $i <= $roomContent->adult; $i++)
                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}
                            </option>
                          @endfor
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6 col-lg-12 col-xl-6">
                      <div class="form-group">
                        <label for="child">{{ __('Total Children') }}</label>
                        <select class="form-select" id="child"name="children">
                          <option value="" disabled>{{ __('Child') }}</option>
                          @for ($i = 0; $i <= $roomContent->children; $i++)
                            <option value="{{ $i }}"{{ $i == 0 ? 'selected' : '' }}>{{ $i }}
                            </option>
                          @endfor
                        </select>
                      </div>
                    </div>
                  </div>
                  @if (vendorTotalBooking($roomContent->vendor_id) >= vendorTotalBookingInPackage($roomContent->vendor_id))
                    <button class="btn btn-lg btn-primary radius-sm w-100 icon-end limitExpire"type="button"
                      aria-label="button">
                      <i class="fal fa-calendar-check"></i> <span>{{ __('Book Now') }}</span>
                    </button>
                  @else
                    <button class="btn btn-lg btn-primary radius-sm w-100 icon-end " type="submit"
                      aria-label="button">
                      <i class="fal fa-calendar-check"></i> <span>{{ __('Book Now') }}</span>
                    </button>
                  @endif
                </div>
              </form>
            </div>
            <div class="widget widget-add-banner radius-md mb-40">
              @if (!empty(showAd(2)))
                <div class="text-center">
                  {!! showAd(2) !!}
                </div>
              @endif
            </div>
          </aside>
        </div>
      </div>
    </div>
  </div>
  <!-- listing-details-area end -->

  <!-- Product-area start -->
  @if (count($rooms) > 0)
    <section class="product-area similar-product pb-75">
      <div class="container">
        <div class="section-title title-inline mb-50" data-aos="fade-up">
          <h2 class="title mt-0">{{ __('You may also like') }}</h2>
          <!-- Slider navigation buttons -->
          <div class="slider-navigation">
            <button type="button" title="{{ __('Slide prev') }}" class="slider-btn rounded-circle"
              id="product-slider-1-prev">
              <i class="fal  {{ $currentLanguageInfo->direction == 1 ? 'fa-angle-right' : 'fa-angle-left' }} "></i>
            </button>
            <button type="button" title="{{ __('Slide next') }}" class="slider-btn rounded-circle"
              id="product-slider-1-next">
              <i class="fal {{ $currentLanguageInfo->direction == 1 ? 'fa-angle-left' : 'fa-angle-right' }}"></i>
            </button>
          </div>
        </div>
        <div class="swiper product-slider" id="product-slider-1" data-aos="fade-up">
          <div class="swiper-wrapper">
            @foreach ($rooms as $room)
              <div class="swiper-slide">
                <div class="product-default border radius-md mb-25">
                  <figure class="product_img">
                    <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}"
                      target="_self" title="{{ __('Link') }}" class="lazy-container ratio ratio-2-3 radius-sm">
                      <img class="lazyload" src="{{ asset('assets/img/room/featureImage/' . $room->feature_image) }}"
                        alt="{{ __('Room Image') }}">
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
                      <div class="product-info_list list-unstyled mt-15">
                        <li class="icon-start location">
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
                            <span>{{ number_format($room->average_rating, 2) }}
                              ({{ totalRoomReview($room->id) }}
                              {{ __('Reviews') }})
                            </span>
                          </div>
                        </li>
                      </div>
                      <div class="product_author mt-15">
                        <a href="{{ route('frontend.hotel.details', ['slug' => $room->hotelSlug, 'id' => $room->hotelId]) }}"
                          target="_self" title="{{ __('Link') }}">
                          <img class="lazyload blur-up"
                            src="{{ asset('assets/img/hotel/logo/' . $room->hotelImage) }}"
                            alt="{{ __('Image') }}">
                          <span class="underline" data-tooltip="tooltip" data-bs-placement="bottom"
                            aria-label="{{ $room->hotelName }}" data-bs-original-title="{{ $room->hotelName }}"
                            aria-describedby="tooltip">{{ $room->hotelName }}</span>
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
                            (+{{ $totalAmenities - $displayCount }}<i class="fas fa-ellipsis-v"></i>)
                            <div class="more_items_icons">
                              @foreach ($amenities as $index => $amenitie)
                                @php
                                  if ($index < $displayCount) {
                                      continue;
                                  }
                                  $amin = App\Models\Amenitie::find($amenitie);
                                @endphp
                                <a data-tooltip="tooltip" data-bs-placement="bottom" aria-label="{{ $amin->title }}"
                                  data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip"
                                  href="#"><i class="{{ $amin->icon }}"
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
          </div>
          <!-- If we need pagination -->
          <div class="swiper-pagination position-static mb-25" id="product-slider-1-pagination"></div>
        </div>
      </div>
    </section>
  @endif

  <!-- Product-area end -->

  <form action="{{ route('frontend.room.details', ['slug' => $roomContent->title, 'id' => $roomContent->id]) }}"
    id="searchForm" method="GET">
    <input type="hidden" name="checkInDates"
      id="checkInDates"value="{{ \Carbon\Carbon::parse($checkinDate)->format('m/d/Y') }}">
    <input type="hidden" name="checkInTime" id="checkInTimes"value="{{ request()->input('checkInTime') }}">
  </form>
@endsection
@section('script')
  <script>
    var room_id = "{{ $roomContent->id }}";
    var latitude = "{{ $roomContent->latitude }}";
    var longitude = "{{ $roomContent->longitude }}";
    var visitor_store_url = "{{ route('frontend.store_visitor') }}";
    var searchUrl = "{{ route('frontend.room.details.get_hourly_price', ['slug' => ':slug', 'id' => ':id']) }}";
    searchUrl = searchUrl.replace(':slug', '{{ $roomContent->title }}');
    searchUrl = searchUrl.replace(':id', '{{ $roomContent->id }}');
    var holidays = @json($holidayDates);
  </script>
  <script src="{{ asset('assets/front/js/reserve-room.js') }}"></script>
  <script src="{{ asset('assets/front/js/room-review.js') }}"></script>
  <script src="{{ asset('assets/front/js/store-visitor.js') }}"></script>
  <script src="{{ asset('assets/front/js/room-single-map.js') }}"></script>
@endsection
