@extends('vendors.layout')

@section('content')
  <div class="page-header">
    @if (request()->routeIs('vendor.room_bookings.all_bookings'))
      <h4 class="page-title">{{ __('All Bookings') }}</h4>
    @elseif (request()->routeIs('vendor.room_bookings.paid_bookings'))
      <h4 class="page-title">{{ __('Paid Bookings') }}</h4>
    @elseif (request()->routeIs('vendor.room_bookings.unpaid_bookings'))
      <h4 class="page-title">{{ __('Unpaid Bookings') }}</h4>
    @endif

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
        <a href="#">{{ __('Room Bookings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        @if (request()->routeIs('vendor.room_bookings.all_bookings'))
          <a href="#">{{ __('All Bookings') }}</a>
        @elseif (request()->routeIs('vendor.room_bookings.paid_bookings'))
          <a href="#">{{ __('Paid Bookings') }}</a>
        @elseif (request()->routeIs('vendor.room_bookings.unpaid_bookings'))
          <a href="#">{{ __('Unpaid Bookings') }}</a>
        @endif
      </li>
    </ul>
  </div>
  @php
    $vendorId = Auth::guard('vendor')->user()->id;

    if ($vendorId) {
        $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);

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
            <div class="col-lg-3">
              <div class="card-title">
                @if (request()->routeIs('vendor.room_bookings.all_bookings'))
                  {{ __('All Bookings') }}
                @elseif (request()->routeIs('vendor.room_bookings.paid_bookings'))
                  {{ __('Paid Bookings') }}
                @elseif (request()->routeIs('vendor.room_bookings.unpaid_bookings'))
                  {{ __('Unpaid Bookings') }}
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <form
                @if (request()->routeIs('vendor.room_bookings.all_bookings')) action="{{ route('vendor.room_bookings.all_bookings') }}"
                @elseif (request()->routeIs('vendor.room_bookings.paid_bookings'))
                  action="{{ route('vendor.room_bookings.paid_bookings') }}"
                @elseif (request()->routeIs('vendor.room_bookings.unpaid_bookings'))
                  action="{{ route('vendor.room_bookings.unpaid_bookings') }}" @endif
                method="GET" id="booking_form">
                <div class="row">
                  <div class="col-lg-6">
                    <input name="booking_no" type="text" id="hotel_title" class="form-control"
                      placeholder="{{ __('Search By Booking No.') }}"
                      value="{{ !empty(request()->input('booking_no')) ? request()->input('booking_no') : '' }}">
                  </div>
                  <div class="col-lg-6">
                    <input name="title" type="text" id="room_title" class="form-control"
                      placeholder="{{ __('Search Here...') }}"
                      value="{{ !empty(request()->input('title')) ? request()->input('title') : '' }}">
                  </div>
                  <input type="hidden" name="language" value="{{ request()->input('language') }}" class="form-control"
                    placeholder="{{ __('language') }}">
                </div>
              </form>
            </div>
            @if (is_array($permissions) && in_array('Add Booking From Dashboard', $permissions))
              <div class="col-lg-3">
                <a href="#" data-toggle="modal" data-target="#roomModal"
                  class="btn btn-primary btn-sm float-lg-right float-left ml-lg-1 mt-1">
                  {{ __('Add Booking') }}
                </a>
              </div>
            @endif
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($bookings) == 0)
                <h3 class="text-center mt-2">{{ __('NO ROOM BOOKING FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('SL') . '#' }}</th>
                        <th scope="col">{{ __('Booking No') . '.' }}</th>
                        <th scope="col">{{ __('Room') }}</th>
                        <th scope="col">{{ __('Cust. Paid') }}</th>
                        <th scope="col">{{ __('Paid via') }}</th>
                        <th scope="col">{{ __('Payment Status') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($bookings as $booking)
                        <tr>
                          <td>#{{ $loop->iteration }}</td>
                          <td>{{ '#' . $booking->order_number }}</td>
                          <td>
                            @php
                              $roomInfo = null;
                              $roomhave = null;
                              if ($booking->hotelRoom) {
                                  $roomInfo = $booking->hotelRoom->room_content
                                      ->where('language_id', $defaultLang->id)
                                      ->first();
                                  $roomhave = 'done';
                              }
                            @endphp
                            @if ($roomInfo)
                              <a href="{{ route('frontend.room.details', ['id' => $roomInfo->room_id, 'slug' => $roomInfo->slug]) }}"
                                target="_blank">{{ strlen($roomInfo->title) > 25 ? mb_substr($roomInfo->title, 0, 25, 'utf-8') . '...' : $roomInfo->title }}</a>
                            @else
                              --
                            @endif
                          </td>
                          <td>
                            {{ $booking->currency_text_position == 'left' ? $booking->currency_text : '' }}
                            {{ $booking->grand_total }}
                            {{ $booking->currency_text_position == 'right' ? $booking->currency_text : '' }}
                          </td>
                          <td>{{ __($booking->payment_method) }}</td>
                          <td>
                            <h2 class="d-inline-block">
                              <span
                                class="badge badge-{{ $booking->payment_status == '1' ? 'success' : ($booking->payment_status == '0' ? 'warning' : 'danger') }}">
                                {{ ucfirst($booking->payment_status == '1' ? 'Completed' : ($booking->payment_status == '0' ? 'Pending' : 'Rejected')) }}
                              </span>
                            </h2>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select') }}
                              </button>

                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a href="{{ route('vendor.room_bookings.booking_details', ['id' => $booking->id, 'language' => $defaultLang->code]) }}"
                                  class="dropdown-item">
                                  {{ __('Details') }}
                                </a>

                                @if ($roomhave)
                                  @if (is_array($permissions) && in_array('Edit Booking From Dashboard', $permissions))
                                    <a href="{{ route('vendor.room_bookings.booking_details_and_edit', ['id' => $booking->id, 'language' => $defaultLang->code]) }}"
                                      class="dropdown-item">
                                      {{ __('Edit & View') }}
                                    </a>
                                  @endif
                                @endif

                                @if (!is_null($booking->attachment))
                                  <a href="javascript:void(0)" class="dropdown-item" data-toggle="modal"
                                    data-target="#attachmentModal_{{ $booking->id }}">
                                    {{ __('Attachment') }}
                                  </a>
                                @endif
                                @if ($booking->invoice)
                                  <a href="{{ asset('assets/file/invoices/room/' . $booking->invoice) }}"
                                    class="dropdown-item" target="_blank">
                                    {{ __('Invoice') }}
                                  </a>
                                @endif
                                
                                <a href="#" class="dropdown-item mailBtn" data-target="#mailModal"
                                  data-toggle="modal" data-booking_email="{{ $booking->booking_email }}">
                                  {{ __('Send Mail') }}
                                </a>
                              </div>
                            </div>
                          </td>
                        </tr>
                        @includeIf('vendors.room-booking.show-attachment')
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
            <div class="d-inline-block mx-auto">
              {{ $bookings->appends([
                      'booking_no' => request()->input('booking_no'),
                      'title' => request()->input('title'),
                      'language' => request()->input('language'),
                  ])->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @includeIf('vendors.room-booking.send-mail')
  @includeIf('vendors.room-booking.all-rooms')

@endsection

@section('script')
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-room.js') }}"></script>
@endsection
