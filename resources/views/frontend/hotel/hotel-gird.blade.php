@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->hotel_page_title }}
  @else
    {{ __('Hotels') }}
  @endif
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_hotels }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_hotels }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->hotel_page_title : __('Hotels'),
  ])
  <!-- Page title end-->

  <!-- Listing-area start -->
  <div class="listing-area hotels pt-100 pb-60">
    <div class="container">
      <div class="row gx-5">
        <div class="col-xl-3" data-aos="fade-up">
          <div class="widget-offcanvas offcanvas-xl offcanvas-start  sticky" tabindex="-1" id="widgetOffcanvas"
            aria-labelledby="widgetOffcanvas">
            <div class="sidebar-scroll">
              <div class="offcanvas-header px-20">
                <h4 class="offcanvas-title">{{ __('Filter') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#widgetOffcanvas"
                  aria-label="Close"></button>
              </div>
              @include('frontend.hotel.side-bar')
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

                <div class="form-group icon-end location-group overflow-hidden">
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
                <h6 class="mb-0">{{ __('NO HOTEL FOUND') }}</h6>
              </div>
            @else
              <div class="row pb-15" data-aos="fade-up">
                @foreach ($featured_contents as $hotel_content)
                  <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                    <div class="product-default product-default-style-2 border radius-md mb-25 border-primary featured">
                      <div class="product_top text-center">
                        <div class="p-20">
                          <figure class="product_img mx-auto mb-15">
                            <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                              target="_self" title="{{ __('Link') }}">
                              <img class="lazyload rounded-circle"
                                data-src="{{ asset('assets/img/hotel/logo/' . $hotel_content->logo) }}" alt="Hotel">
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
                            <h4 class="title mb-1">
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
                                $city = App\Models\Location\City::Where('id', $hotel_content->city_id)->first()->name;
                            }
                            if ($hotel_content->state_id) {
                                $State = App\Models\Location\State::Where('id', $hotel_content->state_id)->first()
                                    ->name;
                            }
                            if ($hotel_content->country_id) {
                                $country = App\Models\Location\Country::Where('id', $hotel_content->country_id)->first()
                                    ->name;
                            }

                          @endphp
                          <div class="rome-count">
                            <p>{{ __('Total Room') . ':' }}
                              {{ totalHotelRoom($hotel_content->id) }}</p>
                          </div>
                          <ul class="product-info_list list-unstyled flex-column justify-content-center">
                            <li class="font-sm">
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
                                  <div class="rating" style="width: {{ $hotel_content->average_rating * 20 }}%;"></div>
                                </div>
                                <span>
                                  {{ number_format($hotel_content->average_rating, 2) }}
                                  ({{ totalHotelReview($hotel_content->id) }}
                                  {{ totalHotelReview($hotel_content->id) > 1 ? __('Reviews') : __('Review') }})
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
                                      aria-label="{{ $amin->title }}" data-bs-original-title="{{ $amin->title }}"
                                      aria-describedby="tooltip" href="#"><i class="{{ $amin->icon }}"
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
                  </div>
                @endforeach

                @foreach ($currentPageData as $hotel_content)
                  <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                    <div class="product-default product-default-style-2 border radius-md mb-25">
                      <div class="product_top text-center">
                        <div class="p-20">
                          <figure class="product_img mx-auto mb-15">
                            <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel_content->id]) }}"
                              target="_self" title="{{ __('Link') }}">
                              <img class="lazyload rounded-circle"
                                data-src="{{ asset('assets/img/hotel/logo/' . $hotel_content->logo) }}" alt="Hotel">
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
                            <h4 class="title mb-1">
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
                                $city = App\Models\Location\City::Where('id', $hotel_content->city_id)->first()->name;
                            }
                            if ($hotel_content->state_id) {
                                $State = App\Models\Location\State::Where('id', $hotel_content->state_id)->first()
                                    ->name;
                            }
                            if ($hotel_content->country_id) {
                                $country = App\Models\Location\Country::Where('id', $hotel_content->country_id)->first()
                                    ->name;
                            }

                          @endphp
                          <div class="rome-count">
                            <p>{{ __('Total Room') . ':' }}
                              {{ totalHotelRoom($hotel_content->id) }}</p>
                          </div>
                          <ul class="product-info_list list-unstyled flex-column justify-content-center">
                            <li>
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
                                  <div class="rating" style="width: {{ $hotel_content->average_rating * 20 }}%;"></div>
                                </div>
                                <span>
                                  {{ number_format($hotel_content->average_rating, 2) }}
                                  ({{ totalHotelReview($hotel_content->id) }}
                                  {{ totalHotelReview($hotel_content->id) > 1 ? __('Reviews') : __('Review') }})
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
                                      aria-label="{{ $amin->title }}" data-bs-original-title="{{ $amin->title }}"
                                      aria-describedby="tooltip" href="#"><i class="{{ $amin->icon }}"
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
                  </div>
                @endforeach

                <nav class="pagination-nav" data-aos="fade-up">
                  <ul class="pagination justify-content-center">
                    @php
                      $totalPages = ceil($hotel_contentss->count() / $perPage);
                    @endphp
                    @if ($totalPages > 1)
                      <li class="page-item disabled">
                        <a class="page-link" aria-label="Previous" tabindex="-1" aria-disabled="true">
                          <i class="far fa-angle-left"></i>
                        </a>
                      </li>
                    @endif
                    @if ($hotel_contentss->count() / $perPage > 1)
                      @for ($i = 1; $i <= ceil($hotel_contentss->count() / $perPage); $i++)
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
              </div>
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
  <form action="{{ route('frontend.hotels') }}" id="searchForm" method="GET">
    <input type="hidden" name="title" id="title"value="{{ request()->input('title') }}">
    <input type="hidden" name="category" id="category"value="{{ request()->input('category') }}">
    <input type="hidden" name="max_val" id="max_val"value="{{ request()->input('max_val') }}">
    <input type="hidden" name="min_val" id="min_val"value="{{ request()->input('min_val') }}">
    <input type="hidden" name="ratings" id="ratings"value="{{ request()->input('ratings') }}">
    <input type="hidden" name="amenitie" id="amenitie"value="{{ request()->input('amenitie') }}">
    <input type="hidden" name="sort" id="sort"value="{{ request()->input('sort') }}">
    <input type="hidden" name="vendor" id="vendor"value="{{ request()->input('vendor') }}">
    <input type="hidden" name="country" id="country"value="{{ request()->input('country') }}">
    <input type="hidden" name="state" id="state"value="{{ request()->input('state') }}">
    <input type="hidden" name="city" id="city"value="{{ request()->input('city') }}">
    <input type="hidden" name="checkInDates" id="checkInDates"value="{{ request()->input('checkInDates') }}">
    <input type="hidden" name="checkInTimes" id="checkInTimes"value="{{ request()->input('checkInTimes') }}">
    <input type="hidden" name="hour" id="hour"value="{{ request()->input('hour') }}">
    <input type="hidden" name="stars" id="stars"value="{{ request()->input('stars') }}">
    <input type="hidden" name="page" id="page"value="">
    <input type="hidden" id="location_val" name="location_val" value="{{ request()->input('location') }}">
  </form>
  @include('frontend.partials.map-modal')
@endsection

@section('script')
  <!-- Map JS -->
  @if ($basicInfo->google_map_api_key_status == 1)
    <script
      src="https://maps.googleapis.com/maps/api/js?key={{ $basicInfo->google_map_api_key }}&libraries=places&callback=initMap"
      async defer></script>
    <script src="{{ asset('assets/front/js/api-search-2.js') }}"></script>
  @endif
  <script src="{{ asset('assets/front/js/map-hotel.js') }}"></script>
  <script>
    "use strict";
    var featured_contents = {!! json_encode($featured_contents) !!};
    var hotel_contentss = {!! json_encode($currentPageData) !!};
    var searchUrl = "{{ route('frontend.search_hotel') }}";
    var getStateUrl = "{{ route('frontend.hotels.get-state') }}";
    var getCityUrl = "{{ route('frontend.hotels.get-city') }}";
    var getAddress = "{{ route('frontend.rooms.get-address') }}";
  </script>
  <script src="{{ asset('assets/front/js/hotel-search.js') }}"></script>
@endsection
