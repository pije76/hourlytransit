@extends('admin.layout')

@section('content')
  <div class="page-header">
    @if (request()->routeIs('admin.room_bookings.all_bookings'))
      <h4 class="page-title">{{ __('All Bookings') }}</h4>
    @elseif (request()->routeIs('admin.room_bookings.paid_bookings'))
      <h4 class="page-title">{{ __('Paid Bookings') }}</h4>
    @elseif (request()->routeIs('admin.room_bookings.unpaid_bookings'))
      <h4 class="page-title">{{ __('Unpaid Bookings') }}</h4>
    @endif

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
        <a href="#">{{ __('Room Bookings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        @if (request()->routeIs('admin.room_bookings.all_bookings'))
          <a href="#">{{ __('All Bookings') }}</a>
        @elseif (request()->routeIs('admin.room_bookings.paid_bookings'))
          <a href="#">{{ __('Paid Bookings') }}</a>
        @elseif (request()->routeIs('admin.room_bookings.unpaid_bookings'))
          <a href="#">{{ __('Unpaid Bookings') }}</a>
        @endif
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-3">
              <div class="card-title">
                @if (request()->routeIs('admin.room_bookings.all_bookings'))
                  {{ __('All Bookings') }}
                @elseif (request()->routeIs('admin.room_bookings.paid_bookings'))
                  {{ __('Paid Bookings') }}
                @elseif (request()->routeIs('admin.room_bookings.unpaid_bookings'))
                  {{ __('Unpaid Bookings') }}
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <form
                @if (request()->routeIs('admin.room_bookings.all_bookings')) action="{{ route('admin.room_bookings.all_bookings') }}"
                @elseif (request()->routeIs('admin.room_bookings.paid_bookings'))
                  action="{{ route('admin.room_bookings.paid_bookings') }}"
                @elseif (request()->routeIs('admin.room_bookings.unpaid_bookings'))
                  action="{{ route('admin.room_bookings.unpaid_bookings') }}" @endif
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

            <div class="col-lg-3">
              <a href="#" data-toggle="modal" data-target="#roomModal"
                class="btn btn-primary btn-sm float-lg-right float-left ml-lg-1 mt-1">
                {{ __('Add Booking') }}
              </a>
              <button class="btn btn-danger btn-sm float-right d-none bulk-delete mt-1 mb-1"
                data-href="{{ route('admin.room_bookings.bulk_delete_booking') }}">
                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
              </button>
            </div>
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
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Booking No') . '.' }}</th>
                        <th scope="col">{{ __('Room') }}</th>
                        <th scope="col">{{ __('Vendor') }}</th>
                        <th scope="col">{{ __('Customer') }}</th>
                        <th scope="col">{{ __('Cust. Paid') }}</th>
                        <th scope="col">{{ __('Paid via') }}</th>
                        <th scope="col">{{ __('Payment Status') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($bookings as $booking)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $booking->id }}">
                          </td>
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
                            @if ($booking->vendor_id != 0)
                              @php
                                $vendor = $booking->vendor()->first();
                              @endphp
                              @if ($vendor)
                                <a
                                  href="{{ route('admin.vendor_management.vendor_details', ['id' => $vendor->id, 'language' => $defaultLang->code]) }}">{{ $vendor->username }}</a>
                              @else
                                --
                              @endif
                            @else
                              <span class="badge badge-success">{{ __('Admin') }}</span>
                            @endif
                          </td>
                          <td>
                            @if ($booking->user_id)
                              @php
                                $user = $booking->user()->first();
                              @endphp
                              @if ($user)
                                <a href="{{ route('admin.user_management.registered_user.view', $user->id) }}"
                                  class="">{{ $user->username }}</a>
                              @else
                                --
                              @endif
                            @else
                              @php
                                $user = $booking->user()->first();
                              @endphp
                              @if ($user)
                                <a href="{{ route('admin.user_management.registered_user.view', $user->id) }}"
                                  class="">{{ $user->username }}</a>
                              @else
                                {{ __('Guest') }}
                              @endif
                            @endif
                          </td>
                          <td>
                            {{ $booking->currency_text_position == 'left' ? $booking->currency_text : '' }}
                            {{ $booking->grand_total }}
                            {{ $booking->currency_text_position == 'right' ? $booking->currency_text : '' }}
                          </td>
                          <td>{{ __($booking->payment_method) }}</td>
                          <td>
                            @if ($booking->payment_method == 'Iyzico')
                              <h2 class="d-inline-block">
                                <span
                                  class="badge badge-{{ $booking->payment_status == 1 ? 'success' : ($booking->payment_status == 0 ? 'warning' : 'danger') }}">
                                  {{ $booking->payment_status == 1 ? __('Completed') : ($booking->payment_status == 0 ? __('Pending') : __('Rejected')) }}
                                </span>
                              </h2>
                            @else
                              @if ($booking->payment_status == 0)
                                <form id="orderStatusForm-{{ $booking->id }}" class="d-inline-block"
                                  action="{{ route('admin.room_bookings.update_payment_status', ['id' => $booking->id]) }}"
                                  method="post">
                                  @csrf
                                  <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                  <select
                                    class="form-control form-control-sm @if ($booking->payment_status == 0) bg-warning text-dark @elseif ($booking->payment_status == 'processing') bg-primary @elseif ($booking->payment_status == 'completed') bg-success @else bg-danger @endif"
                                    name="payment_status"
                                    onchange="document.getElementById('orderStatusForm-{{ $booking->id }}').submit()">
                                    <option value="0" selected>{{ __('Pending') }}</option>
                                    <option value="1">{{ __('Completed') }}</option>
                                    <option value="2">{{ __('Rejected') }}</option>
                                  </select>
                                </form>
                              @else
                                <h2 class="d-inline-block">
                                  <span
                                    class="badge badge-{{ $booking->payment_status == '1' ? 'success' : 'danger' }}">
                                    {{ $booking->payment_status == '1' ? __('Completed') : __('Rejected') }}
                                  </span>
                                </h2>
                              @endif
                            @endif
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                {{ __('Select') }}
                              </button>

                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a href="{{ route('admin.room_bookings.booking_details', ['id' => $booking->id, 'language' => $defaultLang->code]) }}"
                                  class="dropdown-item">
                                  {{ __('Details') }}
                                </a>
                                @if ($roomhave)
                                  <a href="{{ route('admin.room_bookings.booking_details_and_edit', ['id' => $booking->id, 'language' => $defaultLang->code]) }}"
                                    class="dropdown-item">
                                    {{ __('Edit & View') }}
                                  </a>
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

                                <form class="deleteForm d-block"
                                  action="{{ route('admin.room_bookings.delete_booking', ['id' => $booking->id]) }}"
                                  method="post">
                                  @csrf
                                  <button type="submit" class="deleteBtn">
                                    {{ __('Delete') }}
                                  </button>
                                </form>
                              </div>
                            </div>
                          </td>
                        </tr>

                        @includeIf('admin.room-booking.show-attachment')
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

  @includeIf('admin.room-booking.send-mail')

  @includeIf('admin.room-booking.all-rooms')
@endsection

@section('script')
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-room.js') }}"></script>
@endsection
