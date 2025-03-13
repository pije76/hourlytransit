@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Hotels') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('vendor.dashboard') }}">
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
        <a href="#">{{ __('Hotels') }}</a>
      </li>
    </ul>
  </div>
  @php
    $vendor_id = Auth::guard('vendor')->user()->id;

    if ($vendor_id) {
        $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendor_id);

        if (!empty($current_package) && !empty($current_package->features)) {
            $permissions = json_decode($current_package->features, true);
        } else {
            $permissions = null;
        }
    } else {
        $permissions = null;
    }
  @endphp

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          
          <div class="row">
            <div class="col-xl-1">
              <div class="card-title d-inline-block"><h2 class="mb-3">{{ __('Hotels') }}</h2></div>
            </div>
            <div class="col-xl-8">

              <form action="{{ route('vendor.hotel_management.hotels') }}" method="get" id="hotelSearchForm">
                <div class="row">
                  <div class="col-lg-4">
                    <div class="mb-2">
                      <select name="category" id="" class="select2"
                        onchange="document.getElementById('hotelSearchForm').submit()">
                        <option value="" selected disabled>{{ __('Category') }}</option>
                        <option value="All" {{ request()->input('category') == 'All' ? 'selected' : '' }}>
                          {{ __('All') }}</option>
                        @foreach ($categories as $category)
                          <option @selected($category->slug == request()->input('category')) value="{{ $category->slug }}">{{ $category->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-4">
                    <div class="mb-2">
                      <input type="text" name="title" value="{{ request()->input('title') }}" class="form-control"
                        placeholder="{{ __('Title') }}">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-2">
                      <select name="featured" id="" class="select2"
                        onchange="document.getElementById('hotelSearchForm').submit()">
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
                  <input type="hidden" name="language" value="{{ request()->input('language') }}" class="form-control">
                </div>
              </form>
            </div>

            <div class="col-xl-3">
              <div class="d-flex flex-wrap gap-10 mt-2 justify-content-xl-end">
                <a href="{{ route('vendor.hotel_management.create_hotel') }}" class="btn btn-primary float-right btn-sm"><i
                    class="fas fa-plus"></i> {{ __('Add Hotel') }}</a>
                <button class="btn btn-danger d-none btn-sm float-right bulk-delete"
                  data-href="{{ route('vendor.hotel_management.bulk_delete.hotel') }}"><i class="flaticon-interface-5"></i>
                  {{ __('Delete') }}</button>
              </div>

            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($hotels) == 0)
                <h3 class="text-center">{{ __('NO HOTEL FOUND') . '!' }}</h3>
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
                        @if (count($charges) > 0)
                          <th scope="col">{{ __('Featured Status') }}</th>
                        @endif
                        <th scope="col">{{ __('Counter') }}</th>
                        <th scope="col">{{ __('Category') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>

                      @foreach ($hotels as $hotel)
                        @php
                          $hotel_content = $hotel->hotel_contents->first();
                          if (is_null($hotel_content)) {
                              $hotel_content = App\Models\HotelContent::where('hotel_id', $hotel->id)
                                  ->where('language_id', $language->id)
                                  ->first();
                          }
                        @endphp
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $hotel->id }}">
                          </td>

                          <td>
                            @if (!empty($hotel_content))
                              <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel->id]) }}"
                                target="_blank">
                                <div class="max-dimensions">
                                  <img
                                    src="{{ $hotel->logo ? asset('assets/img/hotel/logo/' . $hotel->logo) : asset('assets/admin/img/noimage.jpg') }}"
                                    alt="..." class="uploaded-img">
                                </div>
                              </a>
                            @else
                              <div class="max-dimensions">
                                <img
                                  src="{{ $hotel->logo ? asset('assets/img/hotel/logo/' . $hotel->logo) : asset('assets/admin/img/noimage.jpg') }}"
                                  alt="..." class="uploaded-img">
                              </div>
                            @endif

                          </td>
                          <td class="title">
                            @if (!empty($hotel_content))
                              <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel->id]) }}"
                                target="_blank">
                                {{ strlen(@$hotel_content->title) > 50 ? mb_substr(@$hotel_content->title, 0, 50, 'utf-8') . '...' : @$hotel_content->title }}
                              </a>
                            @else
                              --
                            @endif
                          </td>
                          @if (count($charges) > 0)
                            <td>
                              @php
                                $order_status = App\Models\HotelFeature::where('hotel_id', $hotel->id)->first();
                                $today_date = now()->format('Y-m-d');
                              @endphp

                              @if (is_null($order_status))
                                <button class="btn btn-primary featured btn-sm " data-toggle="modal"
                                  data-target="#featured" data-id="{{ $hotel->id }}">
                                  {{ __('Pay to Feature') }}
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
                                    <button class="btn btn-primary featured  btn-sm"
                                      data-toggle="modal"data-target="#featured"
                                      data-id="{{ $hotel->id }}">{{ __('Pay to Feature') }}</button>
                                  @else
                                    <h1 class="d-inline-block text-large"><span
                                        class="badge badge-success">{{ __('Active') }}</span>
                                    </h1>
                                  @endif
                                @endif
                                @if ($order_status->order_status == 'rejected')
                                  <button class="btn btn-primary featured btn-sm "
                                    data-toggle="modal"data-target="#featured"
                                    data-id="{{ $hotel->id }}">{{ __('Pay to Feature') }}</button>
                                @endif
                              @endif
                            </td>
                          @endif

                          <td>
                            <a
                              href="{{ route('vendor.hotel_management.manage_counter_section', ['id' => $hotel->id]) }}">
                              <button class="btn btn-primary btn-sm">{{ __('Manage') }}</button>
                            </a>

                          </td>
                          <td>
                            @if (!empty($hotel_content))
                              @php
                                $categoryName = App\Models\HotelCategory::where(
                                    'id',
                                    $hotel_content->category_id,
                                )->first();
                              @endphp

                              <a href="{{ route('frontend.hotels', ['category' => @$categoryName->slug]) }}"
                                target="_blank">

                                {{ @$categoryName->name }}
                              </a>
                            @else
                              --
                            @endif
                          </td>
                          <td>
                            <form id="StatusForm{{ $hotel->id }}" class="d-inline-block"
                              action="{{ route('vendor.hotel_management.update_hotel_status') }}" method="post">
                              @csrf
                              <input type="hidden" name="hotelId" value="{{ $hotel->id }}">
                              <select
                                class="form-control {{ $hotel->status == 1 ? 'bg-success' : 'bg-danger' }} form-control-sm"
                                name="status"
                                onchange="document.getElementById('StatusForm{{ $hotel->id }}').submit();">
                                <option value="1" {{ $hotel->status == 1 ? 'selected' : '' }}>
                                  {{ __('Active') }}
                                </option>
                                <option value="0" {{ $hotel->status == 0 ? 'selected' : '' }}>
                                  {{ __('Deactive') }}
                                </option>

                              </select>
                            </form>
                          </td>

                          <td>
                            @if ($current_package == '[]')
                              <form class="deleteForm d-block"
                                action="{{ route('vendor.hotel_management.delete_hotel', ['id' => $hotel->id]) }}"
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

                                  <a href="{{ route('vendor.hotel_management.edit_hotel', ['id' => $hotel->id]) }}"
                                    class="dropdown-item">
                                    {{ __('Edit') }}
                                  </a>
                                  @if (!empty($hotel_content))
                                    <a href="{{ route('frontend.hotel.details', ['slug' => $hotel_content->slug, 'id' => $hotel->id]) }}"
                                      class="dropdown-item"target="_blank">
                                      {{ __('Preview') }}
                                    </a>
                                  @endif

                                  <form class="deleteForm d-block"
                                    action="{{ route('vendor.hotel_management.delete_hotel', ['id' => $hotel->id]) }}"
                                    method="post">
                                    @csrf
                                    <button type="submit" class="deleteBtn">
                                      {{ __('Delete') }}
                                    </button>
                                  </form>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="center pagination_center">
            {{ $hotels->appends([
                    'title' => request()->input('title'),
                    'category' => request()->input('category'),
                    'language' => request()->input('language'),
                    'featured' => request()->input('featured'),
                ])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="razorPayForm"></div>
  @include('vendors.hotel.feature-payment')
@endsection
@section('script')
  @if ($midtrans['midtrans_mode'] == 1)
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>
  @else
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
  @endif
  <script src="https://js.stripe.com/v3/"></script>
  <script src="{{ $anetSource }}"></script>
  <script>
    let stripe_key = "{{ $stripe_key }}";
    let authorize_public_key = "{{ $anetClientKey }}";
    let authorize_login_key = "{{ $anetLoginId }}";
  </script>
  <script src="{{ asset('assets/admin/js/vendor-hotel-feature.js') }}"></script>
  <script>
    @if (old('gateway') == 'autorize.net')
      $(document).ready(function() {
        $('#stripe-element').removeClass('d-none');
      })
    @endif
  </script>
@endsection
