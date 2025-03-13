@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Edit Room') }}</h4>
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
        <a href="#">{{ __('Manage Rooms') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Rooms') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      @php
        $dContent = App\Models\RoomContent::where('room_id', $room->id)
            ->where('language_id', $defaultLang->id)
            ->first();
        $title = !empty($dContent) ? $dContent->title : '';
      @endphp
      @if (!empty($title))
        <li class="nav-item">
          <a href="#">
            {{ strlen(@$title) > 20 ? mb_substr(@$title, 0, 20, 'utf-8') . '...' : @$title }}
          </a>
        </li>
        <li class="separator">
          <i class="flaticon-right-arrow"></i>
        </li>
      @endif
      <li class="nav-item">
        <a href="#">{{ __('Edit Room') }}</a>
      </li>
    </ul>
  </div>

  @php
    $vendorId = $room->vendor_id;

    if ($vendorId == 0) {
        $numberoffImages = 99999999;
    } else {
        $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);
        $numberoffImages = $current_package->number_of_images_per_room - count($room->room_galleries);
    }

  @endphp

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Edit Room') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block"
            href="{{ route('admin.room_management.rooms', ['language' => $defaultLang->code]) }}">
            <span class="btn-label">
              <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
          </a>
          @php
            $dContent = App\Models\RoomContent::where('room_id', $room->id)
                ->where('language_id', $defaultLang->id)
                ->first();
            $slug = !empty($dContent) ? $dContent->slug : '';
          @endphp
          @if ($dContent)
            <a class="btn btn-success btn-sm float-right mr-1 d-inline-block"
              href=" {{ route('frontend.room.details', ['slug' => $slug, 'id' => $room->id]) }}" target="_blank">
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
              <div class="alert alert-danger pb-1 dis-none" id="roomErrors">
                <ul></ul>
              </div>
              <div class="col-lg-12">
                <label for="" class="mb-2"><strong>{{ __('Gallery Images') . '*' }}</strong></label>
                <div class="row">
                  <div class="col-12">
                    <table class="table table-striped" id="imgtable">
                      @foreach ($room->room_galleries as $item)
                        <tr class="trdb table-row" id="trdb{{ $item->id }}">
                          <td>
                            <div class="">
                              <img class="thumb-preview wf-150"
                                src="{{ asset('assets/img/room/room-gallery/' . $item->image) }}" alt="Ad Image">
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
                  <input type="hidden" value="{{ $room->id }}" name="room_id">
                </form>
                <p class="em text-danger mb-0" id="errslider_images"></p>
                @if ($vendorId != 0)
                  <p class="text-warning">
                  <p class="text-warning">
                    {{ __('You can upload maximum') }}{{ __(' ') }}
                    {{ $current_package->number_of_images_per_room }}{{ __(' ') }}{{ __('images under one room') }}
                  </p>
                  </p>
                @endif
              </div>

              <form id="roomForm" action="{{ route('admin.room_management.update_room', $room->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">
                <div class="row">
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label for="">{{ __('Featured Image') . '*' }}</label>
                      <br>
                      <div class="thumb-preview">
                        <img
                          src="{{ $room->feature_image ? asset('assets/img/room/featureImage/' . $room->feature_image) : asset('assets/admin/img/noimage.jpg') }}"
                          alt="..." class="uploaded-img">
                      </div>
                      <div class="mt-3">
                        <div role="button" class="btn btn-primary btn-sm upload-btn">
                          {{ __('Choose Image') }}
                          <input type="file" class="img-input" name="thumbnail">
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Status') . '*' }}</label>
                      <select name="status" id="status" class="form-control">
                        <option @if ($room->status == 1) selected @endif value="1">{{ __('Active') }}
                        </option>
                        <option @if ($room->status == 0) selected @endif value="0">{{ __('Deactive') }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Area') }}({{ __('sqft') }})</label>
                      <input type="text" class="form-control" name="area"value="{{ $room->area }}"
                        placeholder="{{ __('Enter Area') }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Adults') . '*' }}</label>
                      <input type="number" class="form-control" name="adult"value="{{ $room->adult }}"
                        placeholder="{{ __('Enter Adult') }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Children') . '*' }}</label>
                      <input type="number" class="form-control" name="children"value="{{ $room->children }}"
                        placeholder="{{ __('Enter Children') }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Beds') . '*' }}</label>
                      <input type="number" class="form-control" name="bed"value="{{ $room->bed }}"
                        placeholder="{{ __('Enter Bed') }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Bathrooms') . '*' }}</label>
                      <input type="number" class="form-control" name="bathroom"value="{{ $room->bathroom }}"
                        placeholder="{{ __('Enter Bathroom') }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Number of rooms of this same type') . '*' }}</label>
                      <input type="number" class="form-control"
                        name="number_of_rooms_of_this_same_type"value="{{ $room->number_of_rooms_of_this_same_type }}"
                        placeholder="{{ __('Enter number of rooms of this same type') }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Preparation time') . '*' }} ({{ __('minutes') }})</label>
                      <input type="text" class="form-control"
                        name="preparation_time"value="{{ $room->preparation_time }}"
                        placeholder="{{ __('Enter Preparation time') }}">
                      <p class="text-warning">
                        {{ __('Time required to clean the room & prepare it for next guest') }}
                      </p>
                    </div>
                  </div>

                  @for ($i = 0; $i < count($bookingHours); $i++)
                    @php
                      $price = null;
                      foreach ($prices as $p) {
                          if ($p->hour_id == $bookingHours[$i]->id) {
                              $price = $p->price;
                              break;
                          }
                      }
                    @endphp
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label>{{ __('Rent for') }} {{ $bookingHours[$i]->hour }} {{ __('Hrs') . '*' }}
                          ({{ $settings->base_currency_text }})</label>
                        <input type="text" class="form-control" name="prices[]"
                          value="{{ $price !== null ? $price : '' }}" placeholder="{{ __('Enter Rent') }}">
                      </div>
                    </div>
                  @endfor

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Select Hotel') . '*' }}</label>
                      <select name="hotel_id" class="form-control js-example-basic-single2 select2">
                        <option selected disabled>{{ __('Select Hotel') }}</option>

                        @foreach ($hotels as $hotel)
                          <option @if ($room->hotel_id == $hotel->id) selected @endif value="{{ $hotel->id }}">
                            {{ @$hotel->hotel_contents->first()->title }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $vendorId }}">
                </div>

                <div id="accordion" class="mt-3">
                  @foreach ($languages as $language)
                    @php
                      $roomContent = App\Models\RoomContent::where('room_id', $room->id)
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
                            {{ $language->name . __(' Language') }}
                            {{ $language->is_default == 1 ? '(Default)' : '' }}
                          </button>
                        </h5>
                      </div>

                      <div id="collapse{{ $language->id }}"
                        class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                        aria-labelledby="heading{{ $language->id }}" data-parent="#accordion">
                        <div
                          class="version-body {{ $language->direction == 1 && $defaultLang->direction == 1
                              ? 'rtl text-right'
                              : ($language->direction == 0 && $defaultLang->direction == 1
                                  ? 'ltr text-left'
                                  : ($language->direction == 1 && $defaultLang->direction == 0
                                      ? 'rtl text-right'
                                      : '')) }}">
                          <div class="row">
                            <div class="col-lg-6">
                              <div
                                class="form-group {{ $language->direction == 1 && $defaultLang->direction == 1
                                    ? 'rtl text-right'
                                    : ($language->direction == 0 && $defaultLang->direction == 1
                                        ? 'ltr text-left'
                                        : ($language->direction == 1 && $defaultLang->direction == 0
                                            ? 'rtl text-right'
                                            : '')) }}">
                                <label>{{ __('Title') . '*' }}</label>
                                <input type="text" class="form-control" name="{{ $language->code }}_title"
                                  placeholder="{{ __('Enter Title') }}"
                                  value="{{ $roomContent ? $roomContent->title : '' }}">
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <div
                                class="form-group {{ $language->direction == 1 && $defaultLang->direction == 1
                                    ? 'rtl text-right'
                                    : ($language->direction == 0 && $defaultLang->direction == 1
                                        ? 'ltr text-left'
                                        : ($language->direction == 1 && $defaultLang->direction == 0
                                            ? 'rtl text-right'
                                            : '')) }}">
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
                                    <option @selected(@$roomContent->room_category == $type->id) value="{{ $type->id }}">
                                      {{ $type->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @php
                              $aminities = App\Models\Amenitie::where('language_id', $language->id)->get();
                              $hasaminitie = json_decode(@$roomContent->amenities);
                            @endphp
                            @if (count($aminities) > 0)
                              <div class="col-lg-12 ">
                                <div
                                  class="form-group {{ $language->direction == 1 && $defaultLang->direction == 1
                                      ? 'rtl text-right'
                                      : ($language->direction == 0 && $defaultLang->direction == 1
                                          ? 'ltr text-left'
                                          : ($language->direction == 1 && $defaultLang->direction == 0
                                              ? 'rtl text-right'
                                              : '')) }}">

                                  <label>{{ __('Select Amenities') . '*' }} </label>
                                  <div class="dropdown-content" id="checkboxes">
                                    @if ($hasaminitie)
                                      @foreach ($aminities as $aminitie)
                                        @if (in_array($aminitie->id, $hasaminitie))
                                          <input id="{{ $aminitie->id }}" type="checkbox"
                                            data-code ="{{ $language->code }}" data-listing_id ="{{ $room->id }}"
                                            data-language_id ="{{ $language->id }}"
                                            name="{{ $language->code }}_amenities[]" value="{{ $aminitie->id }}"
                                            checked>
                                          <label
                                            class="amenities-label {{ $language->direction == 1 ? 'ml-2 mr-0' : 'mr-2' }}"
                                            for="{{ $aminitie->id }}">{{ $aminitie->title }}</label>
                                        @else
                                          <input type="checkbox" name="{{ $language->code }}_amenities[]"
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
                                            name="{{ $language->code }}_amenities[]" value="{{ $aminitie->id }}">
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
                              <div
                                class="form-group {{ $language->direction == 1 && $defaultLang->direction == 1
                                    ? 'rtl text-right'
                                    : ($language->direction == 0 && $defaultLang->direction == 1
                                        ? 'ltr text-left'
                                        : ($language->direction == 1 && $defaultLang->direction == 0
                                            ? 'rtl text-right'
                                            : '')) }}">
                                <label>{{ __('Description') . '*' }}</label>
                                <textarea class="form-control summernote" id="{{ $language->code }}_description"
                                  name="{{ $language->code }}_description" data-height="300">{{ @$roomContent->description }}</textarea>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-12">
                              <div
                                class="form-group {{ $language->direction == 1 && $defaultLang->direction == 1
                                    ? 'rtl text-right'
                                    : ($language->direction == 0 && $defaultLang->direction == 1
                                        ? 'ltr text-left'
                                        : ($language->direction == 1 && $defaultLang->direction == 0
                                            ? 'rtl text-right'
                                            : '')) }}">
                                <label>{{ __('Meta Keywords') }}</label>
                                <input class="form-control" name="{{ $language->code }}_meta_keyword"
                                  placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput"
                                  value="{{ $roomContent ? @$roomContent->meta_keyword : '' }}">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-lg-12">
                              <div
                                class="form-group {{ $language->direction == 1 && $defaultLang->direction == 1
                                    ? 'rtl text-right'
                                    : ($language->direction == 0 && $defaultLang->direction == 1
                                        ? 'ltr text-left'
                                        : ($language->direction == 1 && $defaultLang->direction == 0
                                            ? 'rtl text-right'
                                            : '')) }}">
                                <label>{{ __('Meta Description') }}</label>
                                <textarea class="form-control" name="{{ $language->code }}_meta_description" rows="5"
                                  placeholder="{{ __('Enter Meta Description') }}">{{ $roomContent ? @$roomContent->meta_description : '' }}</textarea>
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
              <button type="submit" form="roomForm" class="btn btn-primary">
                {{ __('Update') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script type="text/javascript" src="{{ asset('assets/admin/js/feature.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-partial.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-dropzone.js') }}"></script>
  <script src="{{ asset('assets/admin/js/admin-room.js') }}"></script>
@endsection

@section('variables')
  <script>
    "use strict";
    var storeUrl = "{{ route('admin.room_management.room.imagesstore') }}";
    var removeUrl = "{{ route('admin.room_management.room.imagermv') }}";
    var rmvdbUrl = "{{ route('admin.room_management.room.imgdbrmv') }}";
    var galleryImages = {{ $numberoffImages }};
    var languages = {!! json_encode($languages) !!};
  </script>
@endsection
