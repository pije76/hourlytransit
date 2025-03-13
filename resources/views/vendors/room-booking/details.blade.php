@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Booking Details') }}</h4>
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
        <a href="#">{{ __('Room Booking') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Bookings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Booking Details') }}</a>
      </li>
    </ul>
    <a href="{{ route('vendor.room_bookings.all_bookings', ['language' => $defaultLang->code]) }}"
      class="btn-md btn btn-primary ml-auto">{{ __('Back') }}</a>
  </div>

  <div class="row">
    @php
      $position = $details->currency_symbol_position;
      $currency = $details->currency_symbol;
    @endphp

    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">
            {{ __('Booking No') . ' .' . '#' . $details->order_number }}
          </div>
        </div>

        <div class="card-body">
          <div class="payment-information">
            <div class="row mb-2">
              <div class="col-lg-6">
                <strong>{{ __('Booking Date') . ' :' }}</strong>
              </div>

              <div class="col-lg-6">{{ date_format($details->created_at, 'M d, Y') }}</div>
            </div>

            <div class="row mb-2">
              <div class="col-lg-6">
                <strong>{{ __('Room Rent') . ' :' }}</strong>
              </div>
              <div class="col-lg-6">
                {{ symbolPrice($details->roomPrice, 2) }}
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-lg-6">
                <strong>{{ __('Service Charge') . ' :' }}</strong>
              </div>
              <div class="col-lg-6">
                {{ symbolPrice($details->serviceCharge) }}
              </div>
            </div>

            @if (!is_null($details->discount))
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Discount') }} <span class="text-danger">(<i class="far fa-minus"></i>)</span>
                    :</strong>
                </div>
                <div class="col-lg-6">
                  {{ symbolPrice($details->discount) }}
                </div>
              </div>
            @endif

            <div class="row mb-2">
              <div class="col-lg-6">
                <strong>{{ __('Subtotal') . ' :' }}</strong>
              </div>
              <div class="col-lg-6">
                @php
                  $subtotal = $details->roomPrice + $details->serviceCharge - $details->discount;
                @endphp
                {{ symbolPrice($subtotal) }}
              </div>
            </div>

            @if (!is_null($details->tax))
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Tax') }} <span class="text-success">(<i class="far fa-plus"></i>)</span>
                    :</strong>
                </div>
                <div class="col-lg-6">
                  {{ symbolPrice($details->tax) }}
                </div>
              </div>
            @endif

            @if (!is_null($details->grand_total))
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Customer Paid') . ' :' }}</strong>
                </div>
                <div class="col-lg-6">
                  {{ symbolPrice($details->grand_total) }}
                </div>
              </div>
            @endif

            @if (!is_null($details->received_amount))
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Received by Vendor') . ' :' }}</strong>
                </div>

                <div class="col-lg-6">
                  {{ $position == 'left' ? $currency . ' ' : '' }}{{ number_format($details->received_amount, 2) }}{{ $position == 'right' ? ' ' . $currency : '' }}
                </div>
              </div>
            @endif

            @if (!is_null($details->received_amount))
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Commision') }} ({{ $details->commission_percentage }}%) : </strong>
                </div>

                <div class="col-lg-6">
                  {{ $position == 'left' ? $currency . ' ' : '' }}{{ number_format($details->comission, 2) }}{{ $position == 'right' ? ' ' . $currency : '' }}

                  ({{ __('Received by Admin') }})
                </div>
              </div>
            @endif

            @if (!is_null($details->payment_method))
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Paid via') . ' :' }}</strong>
                </div>

                <div class="col-lg-6">{{ $details->payment_method }}</div>
              </div>
            @endif

            <div class="row mb-2">
              <div class="col-lg-6">
                <strong>{{ __('Payment Status') . ' :' }}</strong>
              </div>

              <div class="col-lg-6">
                @if ($details->payment_status == 1)
                  <span class="badge badge-success">{{ __('Paid') }}</span>
                @else
                  <span class="badge badge-danger">{{ __('Unpaid') }}</span>
                @endif
              </div>
            </div>
            @if (count($additional_services) > 0)
              <div class="row mb-2">
                <div class="col-lg-6">
                  <strong>{{ __('Additional Service') . ' :' }}</strong>
                </div>
                <div class="col-lg-6">
                  <button type="button" class="btn btn-primary extra-small py-1 px-2" data-toggle="modal"
                    data-target="#additionalServiceModal">{{ __('Show') }}</button>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">
            {{ __('Booking Information') }}
          </div>
        </div>

        <div class="card-body">
          <div class="payment-information">
            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Room') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">
                @if ($roomContentInfo != '--')
                  <a target="_blank"
                    href="{{ route('frontend.room.details', ['id' => $roomContentInfo->room_id, 'slug' => $roomContentInfo->slug]) }}">{{ @$roomContentInfo->title }}</a>
                @else
                  {{ $roomContentInfo }}
                @endif
              </div>
            </div>

            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Hotel') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">
                @if ($hotelContentInfo != '--')
                  <a target="_blank"
                    href="{{ route('frontend.hotel.details', ['id' => $hotelContentInfo->hotel_id, 'slug' => $hotelContentInfo->slug]) }}">{{ @$hotelContentInfo->title }}</a>
                @else
                  {{ $hotelContentInfo }}
                @endif
              </div>
            </div>

            @php
              $arrival_date = Carbon\Carbon::parse($details->check_in_date)->format('M d, Y');
              $arrival_time = Carbon\Carbon::parse($details->check_in_time)->format(
                  $settings->time_format == 24 ? 'H:i' : 'h:i A',
              );
              $departure_date = Carbon\Carbon::parse($details->check_out_date)->format('M d, Y');
              $departure_time = Carbon\Carbon::parse($details->check_out_time)->format(
                  $settings->time_format == 24 ? 'H:i' : 'h:i A',
              );
            @endphp

            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Check-in Date') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">{{ $arrival_date }}</div>
            </div>
            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Check-in Time') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">{{ $arrival_time }}</div>
            </div>

            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Check-out Date') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">{{ $departure_date }}</div>
            </div>
            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Check-out Time') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">{{ $departure_time }}</div>
            </div>

            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Adults') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">{{ $details->adult }}</div>
            </div>

            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Children') . ' :' }}</strong>
              </div>
              <div class="col-lg-8">{{ $details->children }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">
            {{ __('Billing Details') }}
          </div>
        </div>

        <div class="card-body">
          <div class="payment-information">
            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Name') . ' :' }}</strong>
              </div>

              <div class="col-lg-8">{{ $details->booking_name }}</div>
            </div>


            <div class="row mb-2">
              <div class="col-lg-4">
                <strong>{{ __('Email') . ' :' }}</strong>
              </div>

              <div class="col-lg-8">{{ $details->booking_email }}</div>
            </div>

            <div class="row mb-1">
              <div class="col-lg-4">
                <strong>{{ __('Phone Number') . ' :' }}</strong>
              </div>

              <div class="col-lg-8">{{ $details->booking_phone }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @include('vendors.room-booking.services')
@endsection
