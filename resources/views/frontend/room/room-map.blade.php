@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading->rooms_page_title))
    {{ $pageHeading->rooms_page_title }}
  @else
    {{ __('Rooms') }}
  @endif
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_rooms }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_rooms }}
  @endif
@endsection

@section('content')

  <!-- Page title start-->
  <div class="listing-map header-next2">
    <div class="container">
      <div class="main-map">
        <div id="main-map"></div>
      </div>
    </div>
  </div>
  <!-- Page title end-->

  <!-- Listing-area start -->
  <div class="listing-area pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        <div class="col-12 order-first">
          <div class="sort-area" data-aos="fade-up">
            <div class="row align-items-center">
              <div class="col-lg-4">
              </div>
              <div class="col-lg-8 col-sm-8">
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3" data-aos="fade-up">
          <div class="widget-offcanvas offcanvas-xl offcanvas-start sticky" tabindex="-1" id="widgetOffcanvas"
            aria-labelledby="widgetOffcanvas">
            <div class="sidebar-scroll">
              <div class="offcanvas-header px-20">
                <h4 class="offcanvas-title">{{ __('Filter') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#widgetOffcanvas"
                  aria-label="Close"></button>
              </div>
              @include('frontend.room.side-bar')
            </div>
          </div>
          <!-- Spacer -->
          <div class="pb-25 d-none d-xl-block"></div>
        </div>
        <div class="col-xl-9">
          <!-- Sort-Area -->
          <div class="sort-area" data-aos="fade-up">
            <!-- Filter Button -->
            <button class="btn btn-sm btn-outline icon-end  radius-sm d-block d-xl-none" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#widgetOffcanvas" aria-controls="widgetOffcanvas">
              {{ __('Filter') }} <i class="fal fa-filter"></i>
            </button>
            <!-- Sort List -->
            <ul class="sort-list list-unstyled w-100 justify-content-between">
              <li class="item">
                <div class="form-group icon-end location-group">
                  <input class="form-control" value="{{ request()->input('location') }}" type="text" autocomplete="off"
                    placeholder="{{ __('Enter location') }}" name="location" id="location">
                  @if ($basicInfo->google_map_api_key_status == 1)
                    <button type="button" class="btn btn-sm current-location" onclick="getCurrentLocation()">
                      <i class="fas fa-crosshairs"></i>
                    </button>
                  @endif
                </div>
              </li>

              <li class="item">
                <form>
                  <div class="sort-item d-flex align-items-center">
                    <label class="me-2 font-sm" for="select_sort">{{ __('Sort By') }}:</label>
                    <select name="select_sort" id="select_sort" class="sort nice-select right color-dark">

                      <option {{ request()->input('sort') == 'new' ? 'selected' : '' }} value="new">
                        {{ __('Newest on top') }}
                      </option>
                      <option {{ request()->input('sort') == 'old' ? 'selected' : '' }} value="old">
                        {{ __('Oldest on top') }}
                      </option>
                      <option {{ request()->input('sort') == 'starhigh' ? 'selected' : '' }} value="starhigh">
                        {{ __('Stars: High to Low') }}
                      </option>
                      <option {{ request()->input('sort') == 'starlow' ? 'selected' : '' }} value="starlow">
                        {{ __('Stars: Low to High') }}
                      </option>

                      <option {{ request()->input('sort') == 'reviewshigh' ? 'selected' : '' }} value="reviewshigh">
                        {{ __('Reviews: High to Low') }}
                      </option>
                      <option {{ request()->input('sort') == 'reviewslow' ? 'selected' : '' }} value="reviewslow">
                        {{ __('Reviews: Low to High') }}
                      </option>

                    </select>
                  </div>
                </form>
              </li>
            </ul>
          </div>

          <div class="search-container mb-40">
            @if (count($featured_contents) < 1 && count($currentPageData) < 1)
              <div class="p-3 text-center bg-light radius-md">
                <h6 class="mb-0">{{ __('NO ROOM FOUND') }}</h6>
              </div>
            @else
              <div class="row pb-15" data-aos="fade-up">
                @foreach ($featured_contents as $room)
                  <div class="col-lg-4 col-md-6">
                    <div class="product-default product-default-style-2 border radius-md mb-25  border-primary featured">
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
                          <div class="list-unstyled mt-10">
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
                          </div>
                          <div class="product_author mt-14">
                            <a class="d-flex align-items-center gap-1"
                              href="{{ route('frontend.hotel.details', ['slug' => $room->hotelSlug, 'id' => $room->hotelId]) }}"
                              target="_self" title="{{ __('Link') }}">
                              <img class="lazyload blur-up"
                                src="{{ asset('assets/img/hotel/logo/' . $room->hotelImage) }}"alt="{{ __('Image') }}">
                              <span class="underline lc-1 font-sm" data-tooltip="tooltip" data-bs-placement="bottom"
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
                        <div class="product_bottom pt-20 pb-20 px-10 border-top text-center">
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
                                <span class="time">{{ $price->hour }} {{ __('Hrs') }}</span>
                              </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                    <!-- product-default -->
                  </div>
                @endforeach
                @foreach ($currentPageData as $room)
                  <div class="col-lg-4 col-md-6">
                    <div class="product-default product-default-style-2 border radius-md mb-25">
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
                          <div class="list-unstyled mt-10">
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
                          </div>
                          <div class="product_author mt-14">
                            <a class="d-flex align-items-center gap-1"
                              href="{{ route('frontend.hotel.details', ['slug' => $room->hotelSlug, 'id' => $room->hotelId]) }}"
                              target="_self" title="{{ __('Link') }}">
                              <img class="lazyload blur-up"
                                src="{{ asset('assets/img/hotel/logo/' . $room->hotelImage) }}"
                                alt="{{ __('Image') }}">
                              <span class="underline lc-1 font-sm" data-tooltip="tooltip" data-bs-placement="bottom"
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
                        <div class="product_bottom pt-20 pb-20 px-10 border-top text-center">
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
                                <span class="time">{{ $price->hour }} {{ __('Hrs') }}</span>
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
              <nav class="pagination-nav" data-aos="fade-up">
                <ul class="pagination justify-content-center">
                  @php
                    $totalPages = ceil($room_contents->count() / $perPage);
                  @endphp
                  @if ($totalPages > 1)
                    <li class="page-item disabled">
                      <a class="page-link" aria-label="Previous" tabindex="-1" aria-disabled="true">
                        <i class="far fa-angle-left"></i>
                      </a>
                    </li>
                  @endif
                  @php
                    $totalPages = ceil($room_contents->count() / $perPage);
                  @endphp
                  @if ($room_contents->count() / $perPage > 1)
                    @for ($i = 1; $i <= ceil($room_contents->count() / $perPage); $i++)
                      <li class="page-item @if ($i == 1) active @endif">
                        <a class="page-link" data-page="{{ $i }}">{{ $i }}</a>
                      </li>
                    @endfor
                  @endif
                  @if ($totalPages > 1)
                    <li class="page-item">
                      <a class="page-link" data-page="2" aria-label="Previous">
                        <i class="far fa-angle-right"></i>
                      </a>
                    </li>
                  @endif
                </ul>
              </nav>
            @endif
          </div>
          @if (!empty(showAd(3)))
            <div class="text-center">
              {!! showAd(3) !!}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  <!-- Listing-area end -->

  <form action="{{ route('frontend.rooms') }}" id="searchForm" method="GET">
    <input type="hidden" name="title" id="title"value="{{ request()->input('title') }}">
    <input type="hidden" name="address" id="address"value="{{ request()->input('address') }}">
    <input type="hidden" name="category" id="category"value="{{ request()->input('category') }}">
    <input type="hidden" name="max_val" id="max_val"value="{{ request()->input('max_val') }}">
    <input type="hidden" name="min_val" id="min_val"value="{{ request()->input('min_val') }}">
    <input type="hidden" name="ratings" id="ratings"value="{{ request()->input('ratings') }}">
    <input type="hidden" name="amenitie" id="amenitie"value="{{ request()->input('amenitie') }}">
    <input type="hidden" name="sort" id="sort"value="{{ request()->input('sort') }}">
    <input type="hidden" name="hotelId" id="hotelId"value="{{ request()->input('hotelId') }}">
    <input type="hidden" name="country" id="country"value="{{ request()->input('country') }}">
    <input type="hidden" name="state" id="state"value="{{ request()->input('state') }}">
    <input type="hidden" name="city" id="city"value="{{ request()->input('city') }}">
    <input type="hidden" name="checkInDates" id="checkInDates"value="{{ request()->input('checkInDates') }}">
    <input type="hidden" name="checkInTimes" id="checkInTimes"value="{{ request()->input('checkInTimes') }}">
    <input type="hidden" name="hour" id="hour"value="{{ request()->input('hour') }}">
    <input type="hidden" name="stars" id="stars"value="{{ request()->input('stars') }}">
    <input type="hidden" name="adult" id="adult"value="{{ request()->input('adult') }}">
    <input type="hidden" name="children" id="children"value="{{ request()->input('children') }}">
    <input type="hidden" name="page" id="page"value="">
    <input type="hidden" id="location_val" name="location_val" value="{{ request()->input('location') }}">
  </form>
  @include('frontend.partials.map-modal')
@endsection
@section('script')
  <!-- Map JS -->
  @if ($basicInfo->google_map_api_key_status == 1)
    <script src="{{ asset('assets/front/js/api-search-2.js') }}"></script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key={{ $basicInfo->google_map_api_key }}&libraries=places&callback=initMap"
      async defer></script>
  @endif
  <script src="{{ asset('assets/front/js/map-room.js') }}"></script>
  <script>
    "use strict";
    var featured_contents = {!! json_encode($featured_contents) !!};
    var room_contents = {!! json_encode($currentPageData) !!};
    var searchUrl = "{{ route('frontend.search_room') }}";
    var getStateUrl = "{{ route('frontend.hotels.get-state') }}";
    var getCityUrl = "{{ route('frontend.hotels.get-city') }}";
    var getAddress = "{{ route('frontend.rooms.get-address') }}";
  </script>
  <script src="{{ asset('assets/front/js/room-search.js') }}"></script>
@endsection
