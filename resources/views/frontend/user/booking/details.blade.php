@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->room_booking_details_page_title ?? __('Booking Details') }}
  @else
    {{ __('Booking Details') }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->room_booking_details_page_title : __('Booking Details'),
  ])

  <!-- Dashboard-area start -->
  <div class="user-dashboard pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        @includeIf('frontend.user.side-navbar')
        <div class="col-lg-9">
          <div class="user-profile-details mb-40">
            <div class="order-details radius-md">

              <div class="title">
                <h4>{{ __('My Booking details') }}</h4>
              </div>
              <div class="view-order-page mb-40">
                <div class="order-info-area">
                  <div class="row align-items-center">
                    <div class="col-lg-8">
                      <div class="order-info mb-20">
                        @php
                          if ($bookingInfo->payment_status == 0) {
                              $payment_bg = 'bg-warning';
                              $payment_status = __('Pending');
                          } elseif ($bookingInfo->payment_status == 1) {
                              $payment_bg = 'bg-success';
                              $payment_status = __('Paid');
                          } elseif ($bookingInfo->payment_status == 2) {
                              $payment_bg = 'bg-danger';
                              $payment_status = __('Rejected');
                          }
                        @endphp
                        <h6>{{ __('Booking No') . '#' }} {{ $bookingInfo->order_number }}
                          <span>[{{ $payment_status }}]</span>
                        </h6>
                        <p class="m-0">{{ __('Booking Date') . ':' }}
                          {{ \Carbon\Carbon::parse($bookingInfo->booking_date)->format('d-M-Y') }}</p>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="printit mb-20">
                        <a href="{{ asset('assets/file/invoices/room/' . $bookingInfo->invoice) }}"
                          class="btn btn-md radius-sm" download>
                          <i class="fas fa-print"></i>{{ ' ' . __('Invoice') }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="billing-add-area mb-10">
                <div class="row">
                  <div class="col-md-4">
                    <div class="main-info mb-30">
                      @if (!empty($user))
                        <h5>{{ __('Booking Information') }}</h5>

                        <ul class="list">

                          <li><span>{{ __('Name') . ':' }}</span>{{ $bookingInfo->booking_name }}</li>
                          <li><span>{{ __('Email') . ':' }}</span>{{ $bookingInfo->booking_email }}</li>
                          <li><span>{{ __('Phone') . ':' }}</span>{{ $bookingInfo->booking_phone }}</li>
                          <li>
                            <span>{{ __('Check-In-Date') . ':' }}</span>
                            {{ \Carbon\Carbon::parse($bookingInfo->check_in_date)->format('d-M-Y') }}
                          </li>
                          <li><span>{{ __('Check-In-Time') . ':' }}</span>
                            {{ \Carbon\Carbon::parse($bookingInfo->check_in_time)->format($basicInfo->time_format == 24 ? 'H:i' : 'h:i A') }}
                          </li>
                          <li><span>{{ __('Check-Out-Date') . ':' }}</span>
                            {{ \Carbon\Carbon::parse($bookingInfo->check_out_date)->format('d-M-Y') }}
                          </li>
                          <li><span>{{ __('Check-Out-Time') . ':' }}</span>
                            {{ \Carbon\Carbon::parse($bookingInfo->check_out_time)->format($basicInfo->time_format == 24 ? 'H:i' : 'h:i A') }}
                          </li>
                          <li><span>{{ __('Adult') . ':' }}</span>{{ $bookingInfo->adult }}</li>
                          <li><span>{{ __('Children') . ':' }}</span>{{ $bookingInfo->children }}</li>
                          <li><span>{{ __('Address') . ':' }}</span>{{ $bookingInfo->booking_address }}</li>

                        </ul>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="payment-information mb-30">
                      <h5>{{ __('Payment Information') }}</h5>


                      <p>{{ __('Room Price') }} <span class="amount">{{ symbolPrice($bookingInfo->roomPrice) }}</span>
                      <p>{{ __('Service Charge') }} <span
                          class="amount">{{ symbolPrice($bookingInfo->serviceCharge) }}</span>
                      </p>
                      @if (!is_null($bookingInfo->discount))
                        <p>{{ __('Discount') }} <span class="amount">{{ symbolPrice($bookingInfo->discount) }}</span>
                        </p>
                      @endif
                      @php
                        $subtotal = $bookingInfo->roomPrice + $bookingInfo->serviceCharge - $bookingInfo->discount;
                      @endphp
                      <p>{{ __('Subtotal') }} <span class="amount">{{ symbolPrice($subtotal) }}</span>
                      </p>
                      <p>{{ __('Tax') }} <span class="amount">{{ symbolPrice($bookingInfo->tax) }}</span>
                      </p>
                      <p>{{ __('Paid Amount') }} <span
                          class="amount">{{ symbolPrice($bookingInfo->grand_total) }}</span></p>
                      <p>{{ __('Payment Method') . ':' }} {{ @$bookingInfo->payment_method }}</p>

                      <p>{{ __('Payment Status') }} <span
                          class="badge {{ $payment_bg }}">{{ $payment_status }}</span>
                      </p>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="payment-information mb-30">
                      <h5>{{ __('Vendor Information') }}</h5>
                      <div class="main-info mb-30">
                        @if (!empty($seller))
                          <ul class="list">
                            <li><span>{{ __('Name') . ':' }}</span>{{ @$seller->username }}</li>
                            <li><span>{{ __('Email') . ':' }}</span>{{ @$seller->email }}</li>
                            <li><span>{{ __('Phone') . ':' }}</span>{{ @$seller->phone }}</li>
                          </ul>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="product-list">
                <h5>{{ __('Booking Room') }}</h5>
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>{{ __('Booking No') . '#' }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Services') }}</th>
                        <th>{{ __('Total') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>{{ @$bookingInfo->order_number }}</td>
                        <td>
                          @if (!empty($roomContent->title))
                            <a href="{{ route('frontend.room.details', ['slug' => $roomContent->slug, 'id' => $roomContent->room_id]) }}"
                              target="_blank">
                              {{ strlen(@$roomContent->title) > 50 ? mb_substr(@$roomContent->title, 0, 50, 'utf-8') . '...' : @$roomContent->title }}
                            </a>
                          @else
                            --
                          @endif
                        </td>
                        <td>
                          @if (count($additional_services) > 0)
                            <button type="button" class="btn btn-primary radius-sm" data-bs-toggle="modal"
                              data-bs-target="#roombokmodal">{{ __('View') }}</button>
                          @else
                            --
                          @endif

                        </td>
                        <td>{{ symbolPrice($bookingInfo->grand_total) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="edit-account-info mt-15">
                <a href="{{ URL::previous() }}" class="btn btn-md btn-primary radius-sm" title="{{ __('Go Back') }}"
                  target="_self">{{ __('Go Back') }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @include('frontend.user.booking.service-details')
@endsection
