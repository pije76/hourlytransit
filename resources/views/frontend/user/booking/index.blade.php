@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->room_bookings_page_title ?? __('Bookings') }}
  @else
    {{ __('Bookings') }}
  @endif
@endsection


@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => !empty($bgImg) ? $bgImg->breadcrumb : '',
      'title' => !empty($pageHeading) ? $pageHeading->room_bookings_page_title : __('Bookings'),
  ])

  <!-- Dashboard-area start -->
  <div class="user-dashboard pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        @includeIf('frontend.user.side-navbar')
        <div class="col-lg-9">
          <div class="account-info radius-md mb-40">
            <div class="title">
              <h3>{{ __('Room Bookings') }}</h3>
            </div>

            <div class="main-info">
              @if (count($bookings) == 0)
                <h4 class="text-center mt-2">{{ __('NO BOOKING FOUND') . '!' }}</h4>
              @else
                <div class="main-table">
                  <div class="table-responsive">
                    <table id="myTable" class="table table-striped w-100">
                      <thead>
                        <tr>
                          <th>{{ __('Booking No') }}</th>
                          <th>{{ __('Date') }}</th>
                          <th>{{ __('Booking Status') }}</th>
                          <th>{{ __('Action') }}</th>
                        </tr>
                      </thead>
                      <tbody>

                        @foreach ($bookings as $booking)
                          <tr>
                            <td>{{ '#' . $booking->order_number ?? '' }}</td>
                            <td>{{ Carbon\Carbon::parse($booking->created_at)->format('Y-m-d') }}</td>

                            @php
                              if ($booking->payment_status == 0) {
                                  $payment_bg = 'bg-warning';
                                  $payment_status = __('Pending');
                              } elseif ($booking->payment_status == 1) {
                                  $payment_bg = 'bg-success'; 
                                  $payment_status = __('Approved');
                              } elseif ($booking->payment_status == 2) {
                                  $payment_bg = 'bg-danger';
                                  $payment_status = __('Rejected');
                              }
                            @endphp

                            <td><span class="badge {{ $payment_bg }}">{{ $payment_status }}</span></td>
                            <td><a href="{{ route('user.room_booking_details', ['id' => $booking->id]) }}"
                                class="btn">{{ __('Details') }}</a></td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              @endif
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Dashboard-area end -->
@endsection
