@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Rooms') }}</h4>
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
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-xl-1">
              <div class="card-title d-inline-block">
                <h2 class="mb-3">
                  {{ __('Rooms') }}
                </h2>
              </div>
            </div>

            <div class="col-xl-8">
              <form action="{{ route('admin.room_management.rooms') }}" method="get" id="roomSearchForm">
                <div class="row">
                  <div class="col-lg-3">
                    <div class="mb-2">
                      <select name="vendor_id" id="" class="select2"
                        onchange="document.getElementById('roomSearchForm').submit()">
                        <option value="" selected disabled>{{ __('Vendor') }}</option>
                        <option value="All" {{ request()->input('vendor_id') == 'All' ? 'selected' : '' }}>
                          {{ __('All') }}</option>
                        @php
                          $vendorInfo = App\Models\Admin::first();
                        @endphp
                        <option value="admin" @selected(request()->input('vendor_id') == 'admin')>{{ $vendorInfo->username }}
                          ({{ __('admin') }})</option>
                        @foreach ($vendors as $vendor)
                          <option @selected($vendor->id == request()->input('vendor_id')) value="{{ $vendor->id }}">
                            {{ $vendor->username }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="mb-2">
                      <select name="roomCategories" id="" class="select2"
                        onchange="document.getElementById('roomSearchForm').submit()">
                        <option value="" selected disabled>{{ __('Category') }}</option>
                        <option value="All" {{ request()->input('roomCategories') == 'All' ? 'selected' : '' }}>
                          {{ __('All') }}</option>
                        @foreach ($roomCategories as $type)
                          <option @selected($type->name == request()->input('roomCategories')) value="{{ $type->name }}">{{ $type->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="mb-2">
                      <select name="featured" id="" class="select2"
                        onchange="document.getElementById('roomSearchForm').submit()">
                        <option value="" selected disabled>{{ __('Featured') }}</option>
                        <option value="All" {{ request()->input('featured') == 'All' ? 'selected' : '' }}>
                          {{ __('All') }}</option>
                        <option value="active" {{ request()->input('featured') == 'active' ? 'selected' : '' }}>
                          {{ __('Active') }}
                        </option>
                        <option value="pending" {{ request()->input('featured') == 'pending' ? 'selected' : '' }}>
                          {{ __('Pending') }}
                        </option>
                        <option value="unfeatured" {{ request()->input('featured') == 'unfeatured' ? 'selected' : '' }}>
                          {{ __('Not Featured') }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="mb-2">
                      <input type="text" name="title" value="{{ request()->input('title') }}" class="form-control"
                        placeholder="{{ __('Title') }}">
                    </div>
                  </div>

                  <input type="hidden" name="language" value="{{ request()->input('language') }}" class="form-control"
                    placeholder="{{ __('language') }}">
                </div>
              </form>
            </div>

            <div class="col-xl-3 mt-2 mt-lg-0">
              <div class="d-flex flex-wrap gap-10 mt-2 justify-content-xl-end">
                <a href="{{ route('admin.room_management.select_vendor') }}"
                  class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> {{ __('Add Room') }}</a>
                <button class="btn btn-danger btn-sm float-right mr-2 d-none bulk-delete"
                  data-href="{{ route('admin.room_management.bulk_delete.room') }}"><i class="flaticon-interface-5"></i>
                  {{ __('Delete') }}</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($rooms) == 0)
                <h3 class="text-center">{{ __('NO ROOM FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Featured Image') }}</th>
                        <th scope="col">{{ __('Title') }}</th>
                        <th scope="col">{{ __('Vendor') }}</th>
                        @if (count($charges) > 0)
                          <th scope="col">{{ __('Featured') }}</th>
                        @endif
                        <th scope="col">{{ __('Additional Services') }}</th>
                        <th scope="col">{{ __('Category') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>

                      @foreach ($rooms as $room)
                        @php
                          $vendor_id = $room->vendor_id;

                          if ($vendor_id == 0) {
                              $vendorId = 0;
                              $current_package = [];
                          } else {
                              $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendor_id);

                              if (!empty($current_package) && !empty($current_package->features)) {
                                  $permissions = json_decode($current_package->features, true);
                              } else {
                                  $permissions = null;
                              }
                          }

                          $room_content = $room->room_content->first();
                          if (is_null($room_content)) {
                              $room_content = App\Models\RoomContent::where('room_id', $room->id)
                                  ->where('language_id', $language->id)
                                  ->first();
                          }
                        @endphp
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $room->id }}">
                          </td>
                          <td>
                            @if (!empty($room_content))
                              <a href="{{ route('frontend.room.details', ['slug' => $room_content->slug, 'id' => $room->id]) }}"
                                target="_blank">
                                <div class="max-dimensions">
                                  <img
                                    src="{{ $room->feature_image ? asset('assets/img/room/featureImage/' . $room->feature_image) : asset('assets/admin/img/noimage.jpg') }}"
                                    alt="..." class="uploaded-img">
                                </div>
                              </a>
                            @else
                              <div class="max-dimensions">
                                <img
                                  src="{{ $room->feature_image ? asset('assets/img/room/featureImage/' . $room->feature_image) : asset('assets/admin/img/noimage.jpg') }}"
                                  alt="..." class="uploaded-img">
                              </div>
                            @endif
                          </td>
                          <td class="title">
                            @if (!empty($room_content))
                              <a href="{{ route('frontend.room.details', ['slug' => $room_content->slug, 'id' => $room->id]) }}"
                                target="_blank">
                                {{ strlen(@$room_content->title) > 50 ? mb_substr(@$room_content->title, 0, 50, 'utf-8') . '...' : @$room_content->title }}
                              </a>
                            @else
                              --
                            @endif
                          </td>

                          <td>
                            @if ($room->vendor_id != 0)
                              <a
                                href="{{ route('admin.vendor_management.vendor_details', ['id' => @$room->vendor->id, 'language' => $defaultLang->code]) }}">{{ @$room->vendor->username }}</a>
                            @else
                              <span class="badge badge-success">{{ __('Admin') }}</span>
                            @endif
                          </td>

                          @if (count($charges) > 0)
                            <td>
                              @php
                                $order_status = App\Models\RoomFeature::where('room_id', $room->id)->first();
                                $today_date = now()->format('Y-m-d');
                              @endphp

                              @if (is_null($order_status))
                                <button class="btn btn-primary featurePaymentModal btn-sm " data-toggle="modal"
                                  data-target="#featurePaymentModal_{{ $room->id }}" data-id="{{ $room->id }}"
                                  data-listing-id="{{ $room->id }}">
                                  {{ __('Feature It') }}
                                </button>
                              @endif

                              @if ($order_status)
                                @if ($order_status->order_status == 'pending')
                                  <h2 class="d-inline-block"><span
                                      class="badge badge-warning">{{ __('pending') }}</span>
                                  </h2>
                                @endif
                                @if ($order_status->order_status == 'apporved')
                                  @if ($order_status->end_date < $today_date)
                                    <button class="btn btn-primary featurePaymentModal  btn-sm"
                                      data-toggle="modal"data-target="#featurePaymentModal_{{ $room->id }}"
                                      data-id="{{ $room->id }}">{{ __('Feature It') }}</button>
                                  @else
                                    @if ($room->vendor_id != 0)
                                      <h1 class="d-inline-block text-large"><span
                                          class="badge badge-success">{{ __('Active') }}</span>
                                      </h1>
                                    @else
                                      <form class="deleteForm d-block"
                                        action="{{ route('admin.room_management.unfeature', ['id' => $order_status->id]) }}"
                                        method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger  mt-1 btn-sm unFeatureBtn">
                                          {{ __('Unfeature') }}
                                          </h1>
                                        </button>
                                      </form>
                                    @endif
                                  @endif
                                @endif
                                @if ($order_status->order_status == 'rejected')
                                  <button class="btn btn-primary featurePaymentModal btn-sm "
                                    data-toggle="modal"data-target="#featurePaymentModal_{{ $room->id }}"
                                    data-id="{{ $room->id }}">{{ __('Feature It') }}</button>
                                @endif
                              @endif
                            </td>
                          @endif

                          <td>
                            <a
                              href="{{ route('admin.room_management.manage_additional_service', ['id' => $room->id, 'language' => $defaultLang->code]) }}">
                              <button class="btn btn-primary btn-sm">{{ __('Manage') }}</button>
                            </a>
                          </td>
                          <td>
                            @if (!empty($room_content))
                              @php
                                $roomCategories = App\Models\RoomCategory::where(
                                    'id',
                                    @$room->room_content[0]->room_category,
                                )->first();
                              @endphp
                              <a href="{{ route('frontend.rooms', ['category' => $roomCategories->slug]) }}"
                                target="_blank">{{ $roomCategories->name }}</a>
                            @else
                              --
                            @endif
                          </td>

                          <td>
                            <form id="StatusForm{{ $room->id }}" class="d-inline-block"
                              action="{{ route('admin.room_management.update_room_status') }}" method="post">
                              @csrf
                              <input type="hidden" name="roomId" value="{{ $room->id }}">
                              <select
                                class="form-control {{ $room->status == 1 ? 'bg-success' : 'bg-danger' }} form-control-sm"
                                name="status"
                                onchange="document.getElementById('StatusForm{{ $room->id }}').submit();">
                                <option value="1" {{ $room->status == 1 ? 'selected' : '' }}>
                                  {{ __('Active') }}
                                </option>
                                <option value="0" {{ $room->status == 0 ? 'selected' : '' }}>
                                  {{ __('Deactive') }}
                                </option>

                              </select>
                            </form>
                          </td>

                          <td>
                            @if ($current_package == '[]')
                              <form class="deleteForm d-block"
                                action="{{ route('admin.room_management.delete_room', ['id' => $room->id]) }}"
                                method="post">
                                @csrf
                                <button type="submit" class="btn btn-danger  mt-1 btn-sm deleteBtn">
                                  <span class="btn-label">
                                    <i class="fas fa-trash"></i>
                                  </span>
                                </button>
                              </form>
                            @else
                              <div class="dropdown">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                  id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                  aria-expanded="false">
                                  {{ __('Select') }}
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                  <a href="{{ route('admin.room_management.edit_room', ['id' => $room->id]) }}"
                                    class="dropdown-item">
                                    {{ __('Edit') }}
                                  </a>
                                  <form class="deleteForm d-block"
                                    action="{{ route('admin.room_management.delete_room', ['id' => $room->id]) }}"
                                    method="post">
                                    @csrf
                                    <button type="submit" class="deleteBtn">
                                      {{ __('Delete') }}
                                    </button>
                                  </form>
                            @endif
                          </td>
                        </tr>
                        @include('admin.room-management.feature-payment')
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="center">
            {{ $rooms->appends([
                    'title' => request()->input('title'),
                    'roomCategories' => request()->input('roomCategories'),
                    'language' => request()->input('language'),
                    'vendor_id' => request()->input('vendor_id'),
                    'featured' => request()->input('featured'),
                ])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('script')
  <script src="https://js.stripe.com/v3/"></script>
  <script src="{{ $anetSource }}"></script>
  <script>
    let stripe_key = "{{ $stripe_key }}";
    let public_key = "{{ $anetClientKey }}";
    let login_id = "{{ $anetLoginId }}";
  </script>
  <script src="{{ asset('assets/js/vendor-feature-checkout.js') }}"></script>
  <script>
    @if (old('gateway') == 'autorize.net')
      $(document).ready(function() {
        $('#stripe-element').removeClass('d-none');
      })
    @endif
  </script>
@endsection
