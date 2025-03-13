<div class="offcanvas-body p-0">

  <aside class="widget-area  px-20">

    <div class="widget widget-amenities py-20">
      <h5 class="title">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#categories">
          {{ __('Categories') }}
        </button>
      </h5>
      <div id="categories" class="collapse show">
        <div class="accordion-body mt-20 scroll-y">
          <ul class="list-group" data-toggle-list="categoriesToggle" data-toggle-show="5">
            <li class="list-item @if (request()->input('category') == null) active @endif">
              <a href="#" class="category-toggle @if (request()->input('category') == null) active @endif" id="">
                {{ __('All') }}
              </a>
            </li>
            @foreach ($categories as $categorie)
              <li class="list-item @if (request()->input('category') == $categorie->slug) active @endif">
                <a href="#" class="category-toggle" id="{{ $categorie->slug }}">
                  {{ $categorie->name }}
                </a>
              </li>
            @endforeach
          </ul>
          <span class="show-more font-sm" data-toggle-btn="toggleListBtn">{{ __('Show More') . '+' }} </span>
        </div>
      </div>
    </div>

    <div class="widget widget-amenities py-20">
      <h5 class="title">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datetime">
          {{ __('Check-in Date & Time') }}
        </button>
      </h5>
      <div id="datetime" class="collapse show">
        <div class="accordion-body mt-20 scroll-y">
          <div class="form-group">
            <input type="text" class="form-control" id="checkInDatetime" name="checkInDatetime"
              value="@if (request()->input('checkInDates')) {{ request()->input('checkInDates') }} {{ request()->input('checkInTimes') }} @endif"
              placeholder="{{ __('Select Check-In Date & Time') }}" autocomplete="off"readonly />
          </div>
        </div>
      </div>
    </div>

    <div class="widget widget-ratings py-20">
      <h5 class="title">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#bookinghours"
          aria-expanded="true" aria-controls="bookinghours">
          {{ __('Booking Hours') }}
        </button>
      </h5>
      <div id="bookinghours" class="collapse show">
        <div class="accordion-body mt-20 scroll-y">

          @foreach ($bookingHours as $index => $charge)
            <ul class="list-group list-group-bordered mb-2">
              <li class="list-group-item">
                <div class="form-check p-0">
                  <label class="form-check-label mb-0" for="radio_{{ $charge->id }}">
                    <input class="form-check-input ml-0 hour" type="radio" name="charge"
                      id="radio_{{ $charge->id }}" value="{{ $charge->hour }}" {{ $index === 0 ? 'checked' : '' }}>
                    {{ $charge->hour }} {{ __('Hours') }}
                  </label>
                </div>
              </li>
            </ul>
          @endforeach
        </div>
      </div>
    </div>

    <div class="widget widget-select py-20">
      <h5 class="title">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#select">
          {{ __('More Filters') }}
        </button>
      </h5>
      <div id="select" class="collapse show">
        <div class="mt-20">
          <div class="form-group icon-end mb-20">
            <input type="text" class="form-control" value="{{ request()->input('title') }}" id="searchBytTitle"
              name="title" placeholder="{{ __('Enter Title') }}">
            <label class="mb-0 color-primary"><i class="fal fa-search"></i></label>
          </div>
          @if ($countries->count() > 0)
            <div class="form-group mb-20">
              <select class="form-control select2 countryDropdown" id="countryDropdown">
                <option value="" selected disabled>{{ __('Select Country') }}</option>
                <option value="">{{ __('All') }}</option>
                @foreach ($countries as $country)
                  <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
              </select>
            </div>
          @endif
          @if ($states->count() > 0)
            <div class="form-group mb-20 hide_state">
              <select class="form-control select2 stateDropdown" id="stateDropdown">
                <option value="" selected disabled>{{ __('Select State') }}</option>
                <option value="">{{ __('All') }}</option>
                @foreach ($states as $state)
                  <option value="{{ $state->id }}">{{ $state->name }}</option>
                @endforeach
              </select>
            </div>
          @endif
          @if ($cities->count() > 0)
            <div class="form-group mb-20">
              <select class="form-control select2 cityDropdown" id="cityDropdown">
                <option value="" selected disabled>{{ __('Select City') }}</option>
                <option value="">{{ __('All') }}</option>
                @foreach ($cities as $city)
                  <option @if (request()->input('city') == $city->id) selected @endif value="{{ $city->id }}">
                    {{ $city->name }}</option>
                @endforeach
              </select>
            </div>
          @endif
          <div class="form-group mb-20">
            <select class="form-control select2 ratingDropdown" id="ratingDropdown">
              <option value="" selected disabled>{{ __('Select Rating') }}</option>
              <option value="">{{ __('All') }}</option>
              <option @if (request()->input('ratings') == 5) selected @endif value="5">{{ __('5 stars') }}</option>
              <option @if (request()->input('ratings') == 4) selected @endif value="4">{{ __('4 stars and above') }}
              </option>
              <option @if (request()->input('ratings') == 3) selected @endif value="3">{{ __('3 stars and above') }}
              </option>
              <option @if (request()->input('ratings') == 2) selected @endif value="2">{{ __('2 stars and above') }}
              </option>
              <option @if (request()->input('ratings') == 1) selected @endif value="1">{{ __('1 star and above') }}
              </option>
            </select>
          </div>
          <div class="form-group mb-20">
            <select class="form-control select2 starsDropdown" id="starsDropdown">
              <option value="" selected disabled>{{ __('Select Stars') }}</option>
              <option value="">{{ __('All') }}</option>
              <option @if (request()->input('stars') == 5) selected @endif value="5">{{ __('5 ★★★★★') }}</option>
              <option @if (request()->input('stars') == 4) selected @endif value="4">{{ __('4 ★★★★') }}
              </option>
              <option @if (request()->input('stars') == 3) selected @endif value="3">{{ __('3 ★★★') }}
              </option>
              <option @if (request()->input('stars') == 2) selected @endif value="2">{{ __('2 ★★') }}
              </option>
              <option @if (request()->input('stars') == 1) selected @endif value="1">{{ __('1 ★') }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="cta mb-30">
      <a href="{{ route('frontend.hotels') }}" class="btn btn-lg btn-primary icon-start w-100"><i
          class="fal fa-sync-alt"></i>{{ __('Reset All') }}</a>
    </div>
  </aside>
</div>
