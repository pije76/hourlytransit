@extends('frontend.layout')

@section('pageHeading')
  {{ __('Dashboard') }}
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => !empty($bgImg) ? $bgImg->breadcrumb : '',
      'title' => !empty($pageHeading) ? $pageHeading->dashboard_page_title : __('Dashboard'),
  ])
  <!--====== Start Dashboard Section ======-->
  <div class="user-dashboard pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        @includeIf('frontend.user.side-navbar')
        <div class="col-lg-9">
          <div class="user-profile-details mb-30">
            <div class="account-info radius-md">
              <div class="title">
                <h4>{{ __('Account Information') }}</h4>
              </div>
              <div class="main-info">
                <ul class="list">
                  <li><span>{{ __('Name') . ':' }}</span> <span>{{ $authUser->name }}</span></li>
                  <li><span>{{ __('Username') . ':' }}</span> <span>{{ $authUser->username }}</span></li>
                  <li><span>{{ __('Email') . ':' }}</span> <span>{{ $authUser->email }}</span></li>
                  <li><span>{{ __('Phone') . ':' }}</span> <span>{{ $authUser->phone }}</span></li>
                  <li><span>{{ __('City') . ':' }}</span> <span>{{ $authUser->city }}</span></li>
                  <li><span>{{ __('Country') . ':' }}</span> <span>{{ $authUser->country }}</span></li>
                  <li><span>{{ __('State') . ':' }}</span> <span>{{ $authUser->state }}</span></li>
                  <li><span>{{ __('Zip Code') . ':' }}</span> <span>{{ $authUser->zip_code }}</span></li>
                  <li><span>{{ __('Address') . ':' }}</span> <span>{{ $authUser->address }}</span></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
              <a href="{{ route('user.room_bookings') }}">
                <div class="card card-box align-items-center radius-md mb-30 color-1">
                  <div class="card-icon mb-15">
                    <i class="fas fa-th"></i>
                  </div>
                  <div class="card-info">
                    <h3 class="mb-0">{{ count($bookings) }}</h3>
                    <p class="mb-0">{{ __('Room Bookings') }}</p>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
              <a href="{{ route('user.wishlist.room') }}">
                <div class="card card-box align-items-center radius-md mb-30 color-1">
                  <div class="card-icon mb-15">
                    <i class="fal fa-shopping-bag"></i>
                  </div>
                  <div class="card-info">
                    <h3 class="mb-0">{{ count($roomwishlists) }}</h3>
                    <p class="mb-0">{{ __('Saved Rooms') }}</p>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
              <a href="{{ route('user.wishlist.hotel') }}">
                <div class="card card-box align-items-center radius-md mb-30 color-1">
                  <div class="card-icon mb-15">
                    <i class="fal fa-heart"></i>
                  </div>
                  <div class="card-info">
                    <h3 class="mb-0">{{ count($hotelwishlists) }}</h3>
                    <p class="mb-0">{{ __('Saved Hotels') }}</p>
                  </div>
                </div>
              </a>
            </div>
          </div>

          <div class="account-info radius-md mb-40">
            <div class="title">
              <h4>{{ __('Recent Bookings') }}</h4>
            </div>
            <div class="main-info">
              @if (count($bookings) == 0)
                <h3 class="text-center mt-2">{{ __('NO BOOKING FOUND') . '!' }}</h3>
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
  <!--====== End Dashboard Section ======-->
@endsection
