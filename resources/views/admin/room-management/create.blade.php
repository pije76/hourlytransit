@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Add Room') }}</h4>
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
        <a href="#">{{ __('Rooms Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Add Room') }}</a>
      </li>
    </ul>
  </div>

  @php
    $vendorId = $vendor_id;

    if ($vendorId == 0) {
        $numberoffImages = 99999999;
        $can_room_add = 1;
    } else {
        $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);

        if ($current_package != '[]') {
            $numberoffImages = $current_package->number_of_images_per_room;
        } else {
            $numberoffImages = 0;
        }
        if (!empty($current_package) && !empty($current_package->features)) {
            $permissions = json_decode($current_package->features, true);
        } else {
            $permissions = null;
        }
    }

  @endphp


  <div class="row">
    <div class="col-md-12">
      @if ($vendorId != 0)
        @if ($current_package != '[]')
          @if (vendorTotalAddedRoom($vendorId) >= $current_package->number_of_room)
            <div class="alert alert-warning">
              {{ __('You cannot add more room for this vendor. Vendor will need to upgrade his plan') }}
            </div>
            @php
              $can_room_add = 2;
            @endphp
          @else
            @php
              $can_room_add = 1;
            @endphp
          @endif
        @else
          @php
            $pendingMemb = \App\Models\Membership::query()
                ->where([['vendor_id', '=', Auth::id()], ['status', 0]])
                ->whereYear('start_date', '<>', '9999')
                ->orderBy('id', 'DESC')
                ->first();
            $pendingPackage = isset($pendingMemb)
                ? \App\Models\Package::query()->findOrFail($pendingMemb->package_id)
                : null;
          @endphp
          @if ($pendingPackage)
            <div class="alert alert-warning">
              {{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken.') }}
            </div>
            <div class="alert alert-warning">
              <strong>{{ __('Pending Package') . ':' }} </strong> {{ $pendingPackage->title }}
              <span class="badge badge-secondary">{{ $pendingPackage->term }}</span>
              <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
            </div>
          @else
            @php
              $newMemb = \App\Models\Membership::query()
                  ->where([['vendor_id', '=', Auth::id()], ['status', 0]])
                  ->first();
            @endphp
            @if ($newMemb)
              <div class="alert alert-warning">
                {{ __('Your membership is expired. Please purchase a new package / extend the current package.') }}
              </div>
            @endif
            <div class="alert alert-warning">
              {{ __('Please purchase a new package to add Room.') }}
            </div>
          @endif
          @php
            $can_room_add = 0;
          @endphp
        @endif
      @endif
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Add Room') }}</div>
        </div>
        <div class="card-body">

          <div class="row">
            <div class="col-lg-10 offset-lg-1">


              <div class="alert alert-danger pb-1 dis-none" id="roomErrors">
                <ul></ul>
              </div>
              <div class="col-lg-12">
                <label for="" class="mb-2"><strong>{{ __('Gallery Images') . '*' }}</strong></label>
                <form action="{{ route('admin.room_management.room.imagesstore') }}" id="my-dropzone"
                  enctype="multipart/formdata" class="dropzone create">
                  @csrf
                  <div class="fallback">
                    <input name="file" type="file" multiple />
                  </div>
                </form>
                <p class="em text-danger mb-0" id="errslider_images"></p>
                @if ($vendorId != 0)
                  @if ($current_package != '[]')
                    @if (vendorTotalAddedRoom($vendorId) <= $current_package->number_of_room)
                      <p class="text-warning">
                        {{ __('You can upload maximum') }}{{ __(' ') }}
                        {{ $current_package->number_of_images_per_room }}{{ __(' ') }}{{ __('images under one room') }}
                      </p>
                    @endif
                  @endif
                @endif
              </div>

              <form id="roomForm" action="{{ route('admin.room_management.store_room') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="row">
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label for="">{{ __('Featured Image') . '*' }}</label>
                      <br>
                      <div class="thumb-preview">
                        <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..." class="uploaded-img">
                      </div>

                      <div class="mt-3">
                        <div role="button" class="btn btn-primary btn-sm upload-btn">
                          {{ __('Choose Image') }}
                          <input type="file" class="img-input" name="feature_image">
                        </div>
                      </div>
                    </div>
                  </div>



                  <div class="row">
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Status') . '*' }} </label>
                        <select name="status" id="status" class="form-control">
                          <option value="1">{{ __('Active') }}</option>
                          <option selected value="0">{{ __('Deactive') }} </option>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Area') }} ({{ __('sqft') }})</label>
                        <input type="text" class="form-control" name="area" placeholder="{{ __('Enter Area') }}">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Adults') . '*' }}</label>
                        <input type="number" class="form-control" name="adult" placeholder="{{ __('Enter Adult') }}">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Children') . '*' }}</label>
                        <input type="number" class="form-control" name="children" value="0"
                          placeholder="{{ __('Enter Children') }}">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Beds') . '*' }}</label>
                        <input type="number" class="form-control" name="bed" placeholder="{{ __('Enter Bed') }}">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Bathrooms') . '*' }}</label>
                        <input type="number" class="form-control" name="bathroom"
                          placeholder="{{ __('Enter Bathroom') }}">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Number of rooms of this same type') . '*' }}</label>
                        <input type="number" class="form-control" name="number_of_rooms_of_this_same_type"
                          placeholder="{{ __('Enter number of rooms of this same type') }}">
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Preparation time') . '*' }} ({{ __('minutes') }})</label>
                        <input type="text" class="form-control" name="preparation_time"
                          placeholder="{{ __('Enter Preparation time') }}"value="0">
                        <p class="text-warning">
                          {{ __('Time required to clean the room & prepare it for next guest') }}
                        </p>
                      </div>
                    </div>
                    <input type="hidden" name="vendor_id" id=""value="{{ $vendorId }}">

                    @foreach ($bookingHours as $bookingHour)
                      <div class="col-lg-3">
                        <div class="form-group">
                          <label>{{ __('Rent for') }} {{ $bookingHour->hour }}
                            {{ __('Hrs') . '*' }}({{ $settings->base_currency_text }})</label>
                          <input type="text" class="form-control" name="prices[]"
                            placeholder="{{ __('Enter Rent') }}">
                        </div>
                      </div>
                    @endforeach

                    <div class="col-lg-6">
                      <div class="form-group">
                        <label>{{ __('Select Hotel') . '*' }}</label>
                        <select name="hotel_id" class="form-control js-example-basic-single2 select2">
                          <option selected disabled>{{ __('Select Hotel') }}</option>
                          @foreach ($hotels as $hotel)
                            <option value="{{ $hotel->id }}">{{ @$hotel->hotel_contents->first()->title }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <div id="accordion" class="mt-3">
                    @foreach ($languages as $language)
                      <div class="version">
                        <div class="version-header" id="heading{{ $language->id }}">
                          <h5 class="mb-0">
                            <button type="button" class="btn btn-link" data-toggle="collapse"
                              data-target="#collapse{{ $language->id }}"
                              aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                              aria-controls="collapse{{ $language->id }}">
                              {{ $language->name . __(' Language') }}
                              {{ $language->is_default == 1 ? '(Default)' : '' }}
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
                                  <label>{{ __('Title') . '*' }} </label>
                                  <input type="text" class="form-control" name="{{ $language->code }}_title"
                                    placeholder="{{ __('Enter Title') }}">
                                </div>
                              </div>

                              <div class="col-lg-6">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  @php
                                    $types = App\Models\RoomCategory::where('language_id', $language->id)
                                        ->where('status', 1)
                                        ->orderBy('serial_number', 'asc')
                                        ->get();
                                  @endphp

                                  <label>{{ __('Category') . '*' }}</label>
                                  <select name="{{ $language->code }}_room_category"
                                    class="form-control js-example-basic-single2 select2">
                                    <option selected disabled>{{ __('Select a Category') }}</option>

                                    @foreach ($types as $type)
                                      <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                              @php
                                $aminities = App\Models\Amenitie::where('language_id', $language->id)->get();
                              @endphp
                              @if (count($aminities) > 0)
                                <div class="col-lg-12">
                                  <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">


                                    <label>{{ __('Select Amenities') . '*' }} </label>
                                    <div class="dropdown-content" id="checkboxes">
                                      @foreach ($aminities as $amenity)
                                        <input type="checkbox" id="{{ $amenity->id }}"
                                          name="{{ $language->code }}_amenities[]" value="{{ $amenity->id }}">
                                        <label
                                          class="amenities-label {{ $language->direction == 1 ? 'ml-2 mr-0' : 'mr-2' }}"
                                          for="{{ $amenity->id }}">{{ $amenity->title }}</label>
                                      @endforeach
                                    </div>
                                  </div>
                                </div>
                              @endif

                            </div>

                            <div class="row">
                              <div class="col-lg-12">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  <label>{{ __('Description') . '*' }}</label>
                                  <textarea id="{{ $language->code }}_description" class="form-control summernote"
                                    name="{{ $language->code }}_description" data-height="300"></textarea>
                                </div>
                              </div>
                            </div>

                            <div class="row">
                              <div class="col-lg-12">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  <label>{{ __('Meta Keywords') }}</label>
                                  <input class="form-control" name="{{ $language->code }}_meta_keyword"
                                    placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>
                              </div>
                            </div>

                            <div class="row">
                              <div class="col-lg-12">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  <label>{{ __('Meta Description') }}</label>
                                  <textarea class="form-control" name="{{ $language->code }}_meta_description" rows="5"
                                    placeholder="{{ __('Enter Meta Description') }}"></textarea>
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
              <button type="submit" form="roomForm" data-can_room_add="{{ $can_room_add }}" class="btn btn-success">
                {{ __('Save') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script>
    'use strict';
    var storeUrl = "{{ route('admin.room_management.room.imagesstore') }}";
    var removeUrl = "{{ route('admin.room_management.room.imagermv') }}";
    var galleryImages = {{ $numberoffImages }};
    var languages = {!! json_encode($languages) !!};
  </script>

  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-room.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-dropzone.js') }}"></script>
@endsection
