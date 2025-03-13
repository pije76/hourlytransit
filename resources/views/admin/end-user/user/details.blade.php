@extends('admin.layout')
@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('User Details') }}</h4>
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
        <a href="{{ route('admin.user_management.registered_users') }}">{{ __('Registered Users') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('User Details') }}</a>
      </li>
    </ul>
    @php
      $auto = 'auto';
    @endphp
    <a href="{{ route('admin.user_management.registered_users') }}" class="btn-md btn btn-primary"
      style="margin-left: {{ $auto }};">{{ __('Back') }}</a>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="row">
        <div class="col-md-3">
          <div class="card">
            <div class="card-body text-center p-4">
              <img
                src="{{ !empty($user->image) ? asset('assets/img/users/' . $user->image) : asset('assets/img/noimage.jpg') }}"
                alt="" width="100%">
            </div>
          </div>
        </div>


        <div class="col-md-9">
          <div class="row">

            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">{{ __('Information') }}</h4>
                </div>
                <div class="card-body">
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('Username') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->username ? $user->username : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('First Name') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->first_name ? $user->first_name : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('Last Name') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->last_name ? $user->last_name : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('Email') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->email ? $user->email : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('Phone') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->contact_number ? $user->contact_number : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('Address') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->address ? $user->address : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('City') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->city ? $user->city : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('State') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->state ? $user->state : '-' }}
                    </div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-lg-2">
                      <strong>{{ __('Country') . ':' }}</strong>
                    </div>
                    <div class="col-lg-10">
                      {{ $user->country ? $user->country : '-' }}
                    </div>
                  </div>

                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-6">
      <div class="row row-card-no-pd">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-head-row">
                <h4 class="card-title">{{ __('Recent Room Bookings') }}</h4>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg-12">
                  @php
                    $rbookings = $user->bookHotelRoom()->orderBy('id', 'desc')->limit(10)->get();
                  @endphp
                  @if (count($rbookings) == 0)
                    <h3 class="text-center">{{ __('NO ROOM BOOKING FOUND') . '!' }}</h3>
                  @else
                    <div class="table-responsive">
                      <table class="table table-striped mt-3">
                        <thead>
                          <tr>
                            <th scope="col">{{ __('Room') }}</th>
                            <th scope="col">{{ __('Rent / Night') }}</th>
                            <th scope="col">{{ __('Payment Status') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($rbookings as $booking)
                            <tr>
                              <td>
                                @php
                                  $roomhave = null;
                                  $title = null;
                                  if ($booking->hotelRoom) {
                                      $roomhave = 'done';
                                  }
                                  if ($booking->hotelRoom) {
                                      $roomInfo = $booking->hotelRoom->room_content
                                          ->where('language_id', $defaultLang->id)
                                          ->first();
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
                                {{ symbolPrice($booking->grand_total) }}
                              </td>
                              <td>
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
                                      class="badge badge-{{ $booking->payment_status == '1' ? 'success' : ($booking->payment_status == '0' ? 'warning' : 'danger') }}">
                                      {{ ucfirst(
                                          $booking->payment_status == '1' ? __('Paid') : ($booking->payment_status == '0' ? __('Unpaid') : __('Rejected')),
                                      ) }}

                                    </span>

                                  </h2>
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

                                    <a href="{{ asset('assets/file/invoices/room/' . $booking->invoice) }}"
                                      class="dropdown-item" target="_blank">
                                      {{ __('Invoice') }}
                                    </a>

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
          </div>
        </div>
      </div>
    </div>
  </div>
  @includeIf('admin.room-booking.send-mail')
@endsection
