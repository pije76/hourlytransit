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
                class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }} " data-tooltip="tooltip"
                data-bs-placement="top" title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
                <i class="fal fa-bookmark"></i>
              </a>
              <div class="rank-star">
                @for ($i = 0; $i < $hotel_content->stars; $i++)
                  <i class="fas fa-star"></i>
                @endfor
              </div>

              <span class="product_subtitle">
                <a href="{{ route('frontend.hotels', ['category' => $hotel_content->categorySlug]) }}" target="_self"
                  title="{{ __('Link') }}">{{ $hotel_content->categoryName }}</a>
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
                    $State = App\Models\Location\State::Where('id', $hotel_content->state_id)->first()->name;
                }
                if ($hotel_content->country_id) {
                    $country = App\Models\Location\Country::Where('id', $hotel_content->country_id)->first()->name;
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
                        <a data-tooltip="tooltip" data-bs-placement="bottom" aria-label="{{ $amin->title }}"
                          data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip" href="#"><i
                            class="{{ $amin->icon }}" title="{{ $amin->title }}"></i></a>
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
                class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }} " data-tooltip="tooltip"
                data-bs-placement="top" title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
                <i class="fal fa-bookmark"></i>
              </a>
              <div class="rank-star">
                @for ($i = 0; $i < $hotel_content->stars; $i++)
                  <i class="fas fa-star"></i>
                @endfor
              </div>

              <span class="product_subtitle">
                <a href="{{ route('frontend.hotels', ['category' => $hotel_content->categorySlug]) }}" target="_self"
                  title="{{ __('Link') }}">{{ $hotel_content->categoryName }}</a>
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
                    $State = App\Models\Location\State::Where('id', $hotel_content->state_id)->first()->name;
                }
                if ($hotel_content->country_id) {
                    $country = App\Models\Location\Country::Where('id', $hotel_content->country_id)->first()->name;
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
                        <a data-tooltip="tooltip" data-bs-placement="bottom" aria-label="{{ $amin->title }}"
                          data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip" href="#"><i
                            class="{{ $amin->icon }}" title="{{ $amin->title }}"></i></a>
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
    @endforeach


    @if ($hotel_contentss->count() / $perPage > 1)
      <nav class="pagination-nav mb-40" data-aos="fade-up">
        <ul class="pagination justify-content-center">
          @if (request()->input('page'))
            @if (request()->input('page') != 1)
              <li class="page-item">
                <a class="page-link" data-page="{{ request()->input('page') - 1 }}" aria-label="Previous">
                  <i class="far fa-angle-left"></i>
                </a>
              </li>
            @else
              <li class="page-item disabled">
                <a class="page-link" aria-label="Previous" tabindex="-1" aria-disabled="true">
                  <i class="far fa-angle-left"></i>
                </a>
              </li>
            @endif
          @endif

          @if ($hotel_contentss->count() / $perPage > 1)
            @for ($i = 1; $i <= ceil($hotel_contentss->count() / $perPage); $i++)
              <li class="page-item @if (request()->input('page') == $i) active @endif">
                <a class="page-link" data-page="{{ $i }}">{{ $i }}</a>
              </li>
            @endfor
          @endif
          @php
            $totalPages = ceil($hotel_contentss->count() / $perPage);
          @endphp

          @if (request()->input('page'))
            @if (request()->input('page') != $totalPages)
              <li class="page-item">
                <a class="page-link" data-page="{{ request()->input('page') + 1 }}" aria-label="Previous">
                  <i class="far fa-angle-right"></i>
                </a>
              </li>
            @else
              <li class="page-item disabled">
                <a class="page-link" aria-label="Previous" tabindex="-1" aria-disabled="true">
                  <i class="far fa-angle-right"></i>
                </a>
              </li>
            @endif
          @endif
        </ul>
      </nav>
    @endif
  </div>
@endif
<script>
  "use strict";
  var featured_contents = {!! json_encode($featured_contents) !!};
  var hotel_contentss = {!! json_encode($currentPageData) !!};
</script>
