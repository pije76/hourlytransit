@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Edit Hotel') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Hotels Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Manage Hotels') }}</a>
      </li>
      @php
        $dContent = App\Models\HotelContent::where('hotel_id', $hotel->id)
            ->where('language_id', $defaultLang->id)
            ->first();
        $title = !empty($dContent) ? $dContent->title : '';
      @endphp

      @if (!empty($title))
        <li class="separator">
          <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
          <a href="#">
            {{ strlen(@$title) > 20 ? mb_substr(@$title, 0, 20, 'utf-8') . '...' : @$title }}
          </a>
        </li>
      @endif
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Edit Hotel') }}</a>
      </li>
    </ul>
  </div>

  @php
    $vendorId = $hotel->vendor_id;

    if ($vendorId == 0) {
        $numberoffImages = 99999999;
    } else {
        $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);
        $numberoffImages = $current_package->number_of_images_per_hotel - count($hotel->hotel_galleries);
    }
  @endphp

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Edit Hotel') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block"
            href="{{ route('admin.hotel_management.hotels', ['language' => $defaultLang->code]) }}">
            <span class="btn-label">
              <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
          </a>
          @php
            $dContent = App\Models\HotelContent::where('hotel_id', $hotel->id)
                ->where('language_id', $defaultLang->id)
                ->first();
            $slug = !empty($dContent) ? $dContent->slug : '';
          @endphp
          @if ($dContent)
            <a class="btn btn-success btn-sm float-right mr-1 d-inline-block"
              href="{{ route('frontend.hotel.details', ['slug' => $slug, 'id' => $hotel->id]) }}" target="_blank">
              <span class="btn-label">
                <i class="fas fa-eye"></i>
              </span>
              {{ __('Preview') }}
            </a>
          @endif

        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-10 offset-lg-1">

              <div class="alert alert-danger pb-1 dis-none" id="hotelErrors">
                <ul></ul>
              </div>
              <div class="col-lg-12">
                <label for="" class="mb-2"><strong>{{ __('Gallery Images') . '*' }}</strong></label>
                <div class="row">
                  <div class="col-12">
                    <table class="table table-striped" id="imgtable">
                      @foreach ($hotel->hotel_galleries as $item)
                        <tr class="trdb table-row" id="trdb{{ $item->id }}">
                          <td>
                            <div class="">
                              <img class="thumb-preview wf-150"
                                src="{{ asset('assets/img/hotel/hotel-gallery/' . $item->image) }}" alt="Ad Image">
                            </div>
                          </td>
                          <td>
                            <i class="fa fa-times rmvbtndb" data-indb="{{ $item->id }}"></i>
                          </td>
                        </tr>
                      @endforeach
                    </table>
                  </div>
                </div>
                <form action="#" id="my-dropzone" enctype="multipart/formdata" class="dropzone create">
                  @csrf
                  <div class="fallback">
                    <input name="file" type="file" multiple />
                  </div>
                  <input type="hidden" value="{{ $hotel->id }}" name="hotel_id">
                </form>
                <p class="em text-danger mb-0" id="errslider_images"></p>
                @if ($vendorId != 0)
                  <p class="text-warning">
                    {{ __('You can upload maximum') }}{{ __(' ') }}
                    {{ $current_package->number_of_images_per_hotel }}{{ __(' ') }}{{ __('images under one hotel') }}
                  </p>
                @endif
              </div>

              <form id="hotelForm" action="{{ route('admin.hotel_management.update_hotel', $hotel->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                <div class="row">
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label for="">{{ __('Hotel Logo') . '*' }}</label>
                      <br>
                      <div class="thumb-preview">
                        <img
                          src="{{ $hotel->logo ? asset('assets/img/hotel/logo/' . $hotel->logo) : asset('assets/admin/img/noimage.jpg') }}"
                          alt="..." class="uploaded-img2">
                      </div>
                      <div class="mt-3">
                        <div role="button" class="btn btn-primary btn-sm upload-btn">
                          {{ __('Choose Image') }}
                          <input type="file" class="img-input2" name="logo">
                        </div>
                      </div>
                      <p class="mt-2 mb-0 text-warning">{{ __('Image Size 300X300') }}</p>
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label>{{ __('Status') . '*' }} </label>
                      <select name="status" id="status" class="form-control">
                        <option @if ($hotel->status == 1) selected @endif value="1">{{ __('Active') }}
                        </option>
                        <option @if ($hotel->status == 0) selected @endif value="0">{{ __('Deactive') }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label>{{ __('Stars') . '*' }} </label>
                      <select name="stars" class="form-control">
                        <option selected disabled>{{ __('Select a star') }}</option>
                        <option @if ($hotel->stars == 1) selected @endif value="1">{{ __('1 ★') }}
                        </option>
                        <option @if ($hotel->stars == 2) selected @endif value="2">{{ __('2 ★★') }}
                        </option>
                        <option @if ($hotel->stars == 3) selected @endif value="3">{{ __('3 ★★★') }}
                        </option>
                        <option @if ($hotel->stars == 4) selected @endif value="4">{{ __('4 ★★★★') }}
                        </option>
                        <option @if ($hotel->stars == 5) selected @endif value="5">{{ __('5 ★★★★★') }}
                        </option>
                      </select>
                    </div>
                  </div>

                  @if ($settings->google_map_api_key_status == 0)
                    <div class="col-lg-6">
                      <div class="form-group ">
                        <label>{{ __('Latitude') . '*' }}</label>
                        <input type="text" class="form-control" value="{{ $hotel->latitude }}" name="latitude"
                          placeholder="{{ __('Enter Latitude') }}" id="latitude" autocomplete="off">
                        <p class="text-warning">
                          {{ __('The Latitude must be between -90 to 90. Ex:49.43453') }}
                        </p>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group ">
                        <label>{{ __('Longitude') . '*' }}</label>
                        <input type="text" class="form-control" value="{{ $hotel->longitude }}"
                          name="longitude"id="longitude" placeholder="{{ __('Enter Longitude') }}" autocomplete="off">
                      </div>
                      <p class="text-warning">
                        {{ __('The Longitude must be between -180 to 180. Ex:149.91553') }}
                      </p>
                    </div>
                  @endif
                  <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $vendorId }}">
                </div>

                <div id="accordion" class="mt-3">
                  @foreach ($languages as $language)
                    @php
                      $hotelContent = App\Models\HotelContent::where('hotel_id', $hotel->id)
                          ->where('language_id', $language->id)
                          ->first();
                    @endphp
                    <div class="version">
                      <div class="version-header" id="heading{{ $language->id }}">
                        <h5 class="mb-0">
                          <button type="button" class="btn btn-link" data-toggle="collapse"
                            data-target="#collapse{{ $language->id }}"
                            aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                            aria-controls="collapse{{ $language->id }}">
                            {{ $language->name . __(' Language') }} {{ $language->is_default == 1 ? '(Default)' : '' }}
                          </button>
                        </h5>
                      </div>

                      <div id="collapse{{ $language->id }}"
                        class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                        aria-labelledby="heading{{ $language->id }}" data-parent="#accordion">
                        <div class="version-body {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                          <div class="row">
                            <div class="col-lg-6">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Title') . '*' }}</label>
                                <input type="text" class="form-control" name="{{ $language->code }}_title"
                                  placeholder="{{ __('Enter Title') }}"
                                  value="{{ $hotelContent ? $hotelContent->title : '' }}">
                              </div>
                            </div>


                            <div class="col-lg-6">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                @php
                                  $categories = App\Models\HotelCategory::where('language_id', $language->id)
                                      ->where('status', 1)
                                      ->orderBy('serial_number', 'asc')
                                      ->get();
                                @endphp

                                <label>{{ __('Category') . '*' }}</label>
                                <select name="{{ $language->code }}_category_id"
                                  class="form-control js-example-basic-single2">
                                  <option selected disabled>{{ __('Select a Category') }}</option>

                                  @foreach ($categories as $category)
                                    <option @selected(@$hotelContent->category_id == $category->id) value="{{ $category->id }}">
                                      {{ $category->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @php
                              $Countries = App\Models\Location\Country::where('language_id', $language->id)->get();
                              $totalCountry = $Countries->count();
                            @endphp

                            @if ($totalCountry > 0)
                              <div class="col-lg-4">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">

                                  <label>{{ __('Country') . '*' }}</label>
                                  <select name="{{ $language->code }}_country_id"
                                    class="form-control js-example-basic-single3" data-code="{{ $language->code }}">
                                    <option selected disabled>{{ __('Select Country') }}</option>
                                    @foreach ($Countries as $Country)
                                      <option @selected(@$hotelContent->country_id == $Country->id) value="{{ $Country->id }}">
                                        {{ $Country->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                            @endif
                            @php
                              $States = App\Models\Location\State::where('language_id', $language->id)->get();
                              $totalState = $States->count();
                            @endphp

                            @if ($totalState > 0)
                              <div
                                class="col-lg-4 {{ $language->code }}_hide_state @if (!@$hotelContent->state_id) d-none @endif">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  <label>{{ __('State') . '*' }} </label>
                                  <select name="{{ $language->code }}_state_id"
                                    class="form-control js-example-basic-single4 {{ $language->code }}_country_state_id"data-code="{{ $language->code }}">
                                    <option selected disabled>{{ __('Select State') }}</option>

                                    @foreach ($States as $State)
                                      <option @selected(@$hotelContent->state_id == $State->id) value="{{ $State->id }}">
                                        {{ $State->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                            @endif

                            @php
                              $cities = App\Models\Location\City::where('language_id', $language->id)->get();
                            @endphp

                            <div class="col-lg-4">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">

                                <label>{{ __('City') . '*' }}</label>
                                <select name="{{ $language->code }}_city_id"
                                  class="form-control js-example-basic-single5 {{ $language->code }}_state_city_id">
                                  <option selected disabled>{{ __('Select a City') }}</option>

                                  @foreach ($cities as $City)
                                    <option @selected(@$hotelContent->city_id == $City->id) value="{{ $City->id }}">
                                      {{ $City->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>

                            <div class="col-lg-12">
                              <div class="form-group">
                                <label for="">{{ __('Address') . '*' }}</label>
                                <input type="text" name="{{ $language->code }}_address" class="form-control"
                                  placeholder="{{ __('Enter Address') }}" id="search-address"
                                  value="{{ $hotelContent ? $hotelContent->address : '' }}">
                                @if ($hotelContent)
                                  @if ($language->is_default == 1 && $settings->google_map_api_key_status == 1)
                                    <a href="" class="btn btn-secondary mt-2 btn-sm" data-toggle="modal"
                                      data-target="#GoogleMapModal">
                                      <i class="fas fa-eye"></i> {{ __('Show Map') }}
                                    </a>
                                  @endif
                                @endif
                              </div>
                            </div>

                            @if ($language->is_default == 1 && $settings->google_map_api_key_status == 1)
                              <div class="col-lg-6">
                                <div class="form-group ">
                                  <label>{{ __('Latitude') . '*' }}</label>
                                  <input type="text" class="form-control" value="{{ $hotel->latitude }}"
                                    name="latitude" placeholder="{{ __('Enter Latitude') }}" id="latitude"
                                    autocomplete="off">
                                  <p class="text-warning">
                                    {{ __('The Latitude must be between -90 to 90. Ex:49.43453') }}
                                  </p>
                                </div>
                              </div>
                              <div class="col-lg-6">
                                <div class="form-group ">
                                  <label>{{ __('Longitude') . '*' }}</label>
                                  <input type="text" class="form-control" value="{{ $hotel->longitude }}"
                                    name="longitude"id="longitude" placeholder="{{ __('Enter Longitude') }}"
                                    autocomplete="off">
                                </div>
                                <p class="text-warning">
                                  {{ __('The Longitude must be between -180 to 180. Ex:149.91553') }}
                                </p>
                              </div>
                            @endif

                            @php
                              $aminities = App\Models\Amenitie::where('language_id', $language->id)->get();
                              $hasaminitie = json_decode(@$hotelContent->amenities);
                            @endphp

                            @if (count($aminities) > 0)
                              <div class="col-lg-12 ">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">

                                  <label>{{ __('Select Amenities') . '*' }}</label>
                                  <div class="dropdown-content" id="checkboxes">
                                    @if ($hasaminitie)
                                      @foreach ($aminities as $aminitie)
                                        @if (in_array($aminitie->id, $hasaminitie))
                                          <input id="{{ $aminitie->id }}" type="checkbox"
                                            data-code ="{{ $language->code }}"
                                            data-listing_id ="{{ $hotel->id }}"
                                            data-language_id ="{{ $language->id }}"
                                            name="{{ $language->code }}_aminities[]" value="{{ $aminitie->id }}"
                                            checked>
                                          <label
                                            class="amenities-label {{ $language->direction == 1 ? 'ml-2 mr-0' : 'mr-2' }}"
                                            for="{{ $aminitie->id }}">{{ $aminitie->title }}</label>
                                        @else
                                          <input type="checkbox" name="{{ $language->code }}_aminities[]"
                                            value="{{ $aminitie->id }}" id="{{ $aminitie->id }}">
                                          <label
                                            class="amenities-label {{ $language->direction == 1 ? 'ml-2 mr-0' : 'mr-2' }}"
                                            for="{{ $aminitie->id }}">{{ $aminitie->title }}</label>
                                        @endif
                                      @endforeach
                                    @else
                                      <div class="dropdown-content" id="checkboxes">
                                        @foreach ($aminities as $aminitie)
                                          <input type="checkbox"id="{{ $aminitie->id }}"
                                            name="{{ $language->code }}_aminities[]" value="{{ $aminitie->id }}">
                                          <label
                                            class="amenities-label {{ $language->direction == 1 ? 'ml-2 mr-0' : 'mr-2' }}"
                                            for="{{ $aminitie->id }}">{{ $aminitie->title }}</label>
                                        @endforeach
                                      </div>
                                    @endif
                                  </div>
                                </div>
                              </div>
                            @endif

                          </div>
                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Description') . '*' }}</label>
                                <textarea class="form-control summernote" id="{{ $language->code }}_description"
                                  name="{{ $language->code }}_description" data-height="300">{{ @$hotelContent->description }}</textarea>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Meta Keywords') }}</label>
                                <input class="form-control" name="{{ $language->code }}_meta_keyword"
                                  placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput"
                                  value="{{ $hotelContent ? @$hotelContent->meta_keyword : '' }}">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Meta Description') }}</label>
                                <textarea class="form-control" name="{{ $language->code }}_meta_description" rows="5"
                                  placeholder="{{ __('Enter Meta Description') }}">{{ $hotelContent ? @$hotelContent->meta_description : '' }}</textarea>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col">
                              @php $currLang = $language; @endphp
                              @foreach ($languages as $language)
                                @continue($language->id == $currLang->id)
                                <div class="form-check py-0">
                                  <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox"
                                      onchange="cloneInput('collapse{{ $currLang->id }}', 'collapse{{ $language->id }}', event)">
                                    <span class="form-check-sign">{{ __('Clone for') }} <strong
                                        class="text-capitalize text-secondary">{{ $language->name }}</strong>
                                      {{ __('language') }}</span>
                                  </label>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div id="sliders">
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" form="hotelForm" class="btn btn-primary">
                {{ __('Update') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Google map modal --}}
  <!-- Modal -->
  <div class="modal fade" id="GoogleMapModal" tabindex="-1" role="dialog"
    aria-labelledby="GoogleMapModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="GoogleMapModalLongTitle">{{ __('Google Map') }}</h5>
          <div>
            <button type="button" class="btn btn-secondary btn-xs" data-dismiss="modal">{{ __('Choose') }}</button>
            <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">X</button>
          </div>
        </div>
        <div class="modal-body">
          <div id="map"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  @if ($settings->google_map_api_key_status == 1)
    <script src="{{ asset('assets/admin/js/edit-map-init.js') }}"></script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key={{ $settings->google_map_api_key }}&libraries=places&callback=initMap"
      async defer></script>
  @endif
  <script type="text/javascript" src="{{ asset('assets/admin/js/feature.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-dropzone.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-hotel.js') }}"></script>
@endsection

@section('variables')
  <script>
    "use strict";
    var address = "{{ $hotelAddress }}";
    var storeUrl = "{{ route('admin.hotel_management.hotel.imagesstore') }}";
    var removeUrl = "{{ route('admin.hotel_management.hotel.imagermv') }}";
    var getStateUrl = "{{ route('admin.hotel_management.get-state') }}";
    var getCityUrl = "{{ route('admin.hotel_management.get-city') }}";
    var rmvdbUrl = "{{ route('admin.hotel_management.hotel.imgdbrmv') }}";
    var galleryImages = {{ $numberoffImages }};
    var languages = {!! json_encode($languages) !!};
  </script>
@endsection
