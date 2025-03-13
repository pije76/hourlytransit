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
            <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}" target="_self"
              title="{{ __('Link') }}" class="lazy-container ratio ratio-2-3 radius-sm">
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
              class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }}" data-tooltip="tooltip"
              data-bs-placement="top" title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
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
                    <span>{{ number_format($room->average_rating, 2) }}
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
                        <a data-tooltip="tooltip" data-bs-placement="bottom" aria-label="{{ $amin->title }}"
                          data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip" href="#"><i
                            class="{{ $amin->icon }}" title="{{ $amin->title }}"></i></a>
                      @endforeach
                    </div>
                  </li>
                @endif
              </ul>

            </div>
            <div class="product_bottom pt-20 pb-20 px-10 border-top text-center">
              <ul class="product-price_list list-unstyled">
                @php
                  $hour = request()->input('hour');
                  $query = App\Models\HourlyRoomPrice::where('room_id', $room->id)
                      ->where('hourly_room_prices.price', '!=', null)
                      ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                      ->orderBy('booking_hours.serial_number')
                      ->select('hourly_room_prices.*', 'booking_hours.serial_number');

                  if (!is_null($hour)) {
                      $query->where('hourly_room_prices.hour', '<=', $hour);
                  }

                  $prices = $query->get();
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
            <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}" target="_self"
              title="{{ __('Link') }}" class="lazy-container ratio ratio-2-3 radius-sm">
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
              class="btn btn-icon radius-sm {{ $checkWishList == false ? '' : 'active' }}" data-tooltip="tooltip"
              data-bs-placement="top" title="{{ $checkWishList == false ? __('Save to Wishlist') : __('Saved') }}">
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
                    <span>{{ number_format($room->average_rating, 2) }}
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
                        <a data-tooltip="tooltip" data-bs-placement="bottom" aria-label="{{ $amin->title }}"
                          data-bs-original-title="{{ $amin->title }}" aria-describedby="tooltip" href="#"><i
                            class="{{ $amin->icon }}" title="{{ $amin->title }}"></i></a>
                      @endforeach
                    </div>
                  </li>
                @endif
              </ul>
            </div>
            <div class="product_bottom pt-20 pb-20 px-10 border-top text-center">
              <ul class="product-price_list list-unstyled">
                @php
                  $hour = request()->input('hour');
                  $query = App\Models\HourlyRoomPrice::where('room_id', $room->id)
                      ->where('hourly_room_prices.price', '!=', null)
                      ->join('booking_hours', 'hourly_room_prices.hour_id', '=', 'booking_hours.id')
                      ->orderBy('booking_hours.serial_number')
                      ->select('hourly_room_prices.*', 'booking_hours.serial_number');

                  if (!is_null($hour)) {
                      $query->where('hourly_room_prices.hour', '<=', $hour);
                  }

                  $prices = $query->get();
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
  @if ($room_contents->count() / $perPage > 1)
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

        @if ($room_contents->count() / $perPage > 1)
          @for ($i = 1; $i <= ceil($room_contents->count() / $perPage); $i++)
            <li class="page-item @if (request()->input('page') == $i) active @endif">
              <a class="page-link" data-page="{{ $i }}">{{ $i }}</a>
            </li>
          @endfor
        @endif

        @php
          $totalPages = ceil($room_contents->count() / $perPage);
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
@endif
<script>
  "use strict";
  var featured_contents = {!! json_encode($featured_contents) !!};
  var room_contents = {!! json_encode($currentPageData) !!};
</script>
