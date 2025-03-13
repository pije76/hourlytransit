@extends('frontend.layout')

@section('pageHeading')
  {{ $hotel->title }}
@endsection

@section('metaKeywords')
  @if (!empty($hotel))
    {{ $hotel->meta_keyword }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($room))
    {{ $hotel->meta_description }}
  @endif
@endsection

@section('ogTitle')
  @if (!empty($hotel))
    {{ $hotel->title }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => strlen(@$hotel->title) > 35 ? mb_substr(@$hotel->title, 0, 35, 'utf-8') . '...' : @$hotel->title,
  ])
  <!-- Page title end-->

  <!-- Hotel-details-area start -->
  <div class="hotel-details-area pt-100 pb-60">
    <div class="container">
      <!-- Hotel Info -->
      <div class="row gx-xl-5 mb-30" data-aos="fade-up">

        <div class="col-lg-6">
          <div class="hotel-info mb-30">
            <figure class="hotel_img">

              <img class="lazyload rounded-circle" src="assets/images/placeholder.png"
                data-src="{{ asset('assets/img/hotel/logo/' . $hotel->logo) }}
                " alt="{{ __('Hotel') }}">
            </figure>
            <div class="hotel-info_details">
              <span class="hotel_subtitle d-inline-block fw-medium">
                <a href="{{ route('frontend.hotels', ['category' => $hotel->categorySlug]) }}" target="_self"
                  title="{{ __('Link') }}">{{ $hotel->categoryName }}</a>
              </span>
              <h3 class="hotel_title mb-10">
                {{ $hotel->title }}
              </h3>
              <div class="d-flex flex-wrap row-gap-2 column-gap-2">
                <div class="vendore_author pe-2 border-end">
                  <a href="{{ route('frontend.vendor.details', ['username' => $userName]) }}" target="_self"
                    title="{{ __('Link') }}">

                    @if ($hotel->vendor_id == 0)
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
                <div class="rank-star d-flex align-items-center flex-wrap gap-2 mb-0 ">
                  <div class="icons">
                    <i class="fas fa-star"></i>
                  </div>
                  <span class="fw-semibold text-nowrap">{{ $hotel->stars }} {{ __('Star') }}</span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="col-lg-6">
          <div class="hotel-info_right mb-lg-0 mb-lg-30">
            @if (count($hotelCounters) > 0)
              <ul class="hotel-info_list list-unstyled p-20 border radius-md">
                @foreach ($hotelCounters as $hotelCounter)
                  <li>
                    <span class="h3 mb-1">{{ $hotelCounter->value }}</span>
                    <span>{{ $hotelCounter->label }}</span>
                  </li>
                @endforeach
              </ul>
            @endif

            <ul class="hotel-share_list list-unstyled mt-20">

              @if (Auth::guard('web')->check())
                @php
                  $user_id = Auth::guard('web')->user()->id;
                  $checkWishList = checkHotelWishList($hotel->id, $user_id);
                @endphp
              @else
                @php
                  $checkWishList = false;
                @endphp
              @endif


              <li class="ratings flex-nowrap" dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">
                <div class="product-ratings rate text-xsm">
                  <div class="rating" style="width: {{ $hotel->average_rating * 20 }}%;"></div>
                </div>
                <p class="text-nowrap">{{ number_format($hotel->average_rating, 2) }}
                  ({{ totalHotelReview($hotel->id) }}
                  {{ __('Reviews') }})
                </p>
              </li>


              <li>
                <a class="btn-icon-text radius-sm {{ $checkWishList == false ? '' : 'active' }}"
                  href="{{ $checkWishList == false ? route('addto.wishlist.hotel', $hotel->id) : route('remove.wishlist.hotel', $hotel->id) }}"
                  target="_self" title="{{ __('Link') }}">
                  <i class="fal fa-bookmark"></i>
                  <span>{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}</span>
                </a>
              </li>
              <li>
                <a class="btn-icon-text radius-sm" href="#" data-bs-toggle="modal"
                  data-bs-target="#socialMediaModal">
                  <i class="fal fa-share-alt"></i>
                  <span>{{ __('Share Hotel') }}</span>
                </a>
              </li>
            </ul>

          </div>
        </div>
      </div>
      <!-- Hotel Details -->
      <div class="hotel-single-details" data-aos="fade-up">
        <!-- Product description -->
        <div class="hotel-desc">

          <!-- Hotel gallery -->
          <div class="hotel-gallery mb-20">
            <h3 class="title mb-20">{{ __('Gallery') }}</h3>
            <div class="row gallery-popup">
              <div class="col-lg-10">
                <!-- Start product-slider wrapper -->
                <div class="product-slider-style2-wrapper">
                  <!-- Start product-slider -->
                  <div class="swiper product-slider-style2 radius-md">
                    <div class="swiper-wrapper">
                      @foreach ($hotelImages as $gallery)
                        <div class="swiper-slide product-slider-item">
                          <figure class="lazy-container ratio ratio-5-3">
                            <a href="{{ asset('assets/img/hotel/hotel-gallery/' . $gallery->image) }}"
                              class="lightbox-single">
                              <img class="lazyload"
                                src="{{ asset('assets/img/hotel/hotel-gallery/' . $gallery->image) }}"
                                data-src="{{ asset('assets/img/hotel/hotel-gallery/' . $gallery->image) }}"
                                alt="{{ __('hotel image') }}">
                            </a>
                          </figure>
                        </div>
                      @endforeach
                    </div>
                    <div class="product-slider-button-prev slider-btn"><i class="fal fa-angle-left"></i></div>
                    <div class="product-slider-button-next slider-btn"><i class="fal fa-angle-right"></i></div>
                  </div>

                  <!-- product-slider-style2-thumb -->
                  <div thumbsSlider="" class="swiper product-slider-style2-thumb">
                    <div class="swiper-wrapper">
                      @foreach ($hotelImages as $gallery)
                        <div class="swiper-slide product-slider-thumb-item">
                          <img class="lazyload" src="{{ asset('assets/img/hotel/hotel-gallery/' . $gallery->image) }}"
                            alt="Image">
                        </div>
                      @endforeach
                    </div>
                  </div>
                  <!-- End product-slider -->
                </div>
              </div>
            </div>
          </div>

          <div class="tinymce-content">
            {!! optional($hotel)->description !!}
          </div>
        </div>
        @if ($hotel->amenities != '[]')
          <div class="row">
            <div class="col-lg-10">
              <div class="pt-60 pb-60">
                <div class="product-amenities aos-init aos-animate" data-aos="fade-up">
                  <h3 class="title mb-20">{{ __('Amenities') }}</h3>
                  <ul class="amenities-list list-unstyled p-20 radius-md border">
                    @php
                      $amenities = json_decode($hotel->amenities);
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
            </div>
          </div>
        @endif

        <div class="row">
          <div class="col-lg-10">
            <div class="hotel-location pb-90 aos-init aos-animate" data-aos="fade-up">
              <h3 class="title mb-20">{{ __('Location') }}</h3>
              <div class="p-20 radius-md border">

                <p class=" mb-10">
                  <i class="fal fa-map-marker-alt"></i>
                  <span>
                    {{ $hotel->address }}
                  </span>
                </p>
                <p class=" mb-20">
                  <i class="fas fa-city"></i>
                  @php
                    $city = null;
                    $State = null;
                    $country = null;

                    if ($hotel->city_id) {
                        $city = App\Models\Location\City::Where('id', $hotel->city_id)->first()->name;
                    }
                    if ($hotel->state_id) {
                        $State = App\Models\Location\State::Where('id', $hotel->state_id)->first()->name;
                    }
                    if ($hotel->country_id) {
                        $country = App\Models\Location\Country::Where('id', $hotel->country_id)->first()->name;
                    }
                  @endphp
                  <span>
                    {{ @$city }}@if (@$State)
                      , {{ $State }}
                      @endif @if (@$country)
                        , {{ $country }}
                      @endif
                  </span>
                </p>
                <div class="lazy-container radius-md ratio ratio-21-8">
                    <div id="map"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Rooms -->
        @if (count($rooms) > 0)
          <div class="product-area similar-product pb-15" data-aos="fade-up">
            <div class="section-title title-inline mb-30">
              <h3 class="title">{{ __('Rooms') }}</h3>
              @if (count($totalRooms) > count($rooms))
                <a href="{{ route('frontend.rooms', ['hotelId' => $hotel->id]) }}"
                  class="btn btn-lg btn-primary radius-sm" title="{{ __('View All Rooms') }}"
                  target="_self">{{ __('View All Rooms') }}
                </a>
              @endif
            </div>
            <div class="swiper product-slider" id="product-slider-1">
              <div class="swiper-wrapper">
                @foreach ($rooms as $room)
                  <div class="swiper-slide">
                    <div class="product-default border radius-md mb-25">
                      <figure class="product_img">
                        <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}"
                          target="_self" title="{{ __('Link') }}" class="lazy-container ratio ratio-2-3 radius-sm">
                          <img class="lazyload"
                            src="{{ asset('assets/img/room/featureImage/' . $room->feature_image) }}"
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

                          <ul class="product-info_list list-unstyled mt-14">
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
                          </ul>
                          <div class="product_author mt-14">
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
                          <ul class="product-icon_list mt-14 list-unstyled">
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
                                <span class="h6 mb-0">{{ symbolPrice($price->price) }}</span>
                                <span class="small fw-medium">{{ $price->hour }} {{ __('Hrs') }}</span>
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
        @endif

        <!-- Hotel Review -->
        <div class="hotel-review pt-60" data-aos="fade-up">
          <div class="section-title title-inline mb-30">
            <h3 class="title">{{ __('Reviews') }}</h3>
          </div>
          <div class="review-progresses bg-primary-light p-30 radius-md mb-40">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-30">
              <div class="d-flex gap-3">
                <h4 class="mb-0">{{ __('Average Rating') }} </h4>
                <h4 class="mb-0">{{ $hotel->average_rating }}</h4>
              </div>

              <h5 class="mb-0">{{ __('Total') }}: {{ $numOfReview }} </h5>
            </div>
            @php
              $total_review = App\Models\RoomReview::where('hotel_id', $hotel->id)->count();
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
                $totalReviewForRating = App\Models\RoomReview::where('hotel_id', $hotel->id)
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
                  <p class="mb-0">{{ $rating }} {{ $rating == 1 ? __('Star') : __('Stars') }}</p>
                </div>

                <div class="progress-line">
                  <div class="progress">
                    <div class="progress-bar bg-primary" style="width: {{ $percentage }}%" role="progressbar"
                      aria-label="Basic example" aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                      aria-valuemax="100">
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
            <div class="row">
              @foreach ($reviews as $review)
                <div class="col-lg-6">
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
                            <div class="ratings mb-1">
                              <div class="rate" style="background-image: url('{{ asset($rateStar) }}')">
                                <div class="rating-icon"
                                  style="background-image:url('{{ asset($rateStar) }}'); width: {{ $review->rating * 20 . '%;' }}">
                                </div>
                              </div>
                              <span class="ratings-total">({{ $review->rating }})</span>
                            </div>
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
                </div>
              @endforeach
              @if (!empty(showAd(3)))
                <div class="text-center">
                  {!! showAd(3) !!}
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Hotel-details-area end -->
  @include('frontend.hotel.share')
@endsection
@section('script')
  <script>
    var latitude = "{{ $hotel->latitude }}";
    var longitude = "{{ $hotel->longitude }}"; 
  </script>
  <script src="{{ asset('assets/front/js/hotel-single-map.js') }}"></script>
@endsection
