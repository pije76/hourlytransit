<!DOCTYPE html>
<html>

<head lang="en">
  {{-- required meta tags --}}
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  {{-- title --}}
  <title>{{ 'Room Booking Invoice | ' . config('app.name') }}</title>

  {{-- fav icon --}}
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/' . $websiteInfo->favicon) }}">
  @php
    $mb_30 = '10px';
  @endphp
  {{-- styles --}}
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/invoice.css') }}">

</head>

<body>
  <div class="room-booking-invoice my-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="logo text-center" style="margin-bottom:{{ $mb_30 }}">
            <img src="{{ asset('assets/img/' . $websiteInfo->logo) }}" alt="Company Logo">
          </div>

          <div class="mb-3">
            <h2 class="text-center">
              {{ __('ROOM BOOKING INVOICE') }}
            </h2>
          </div>

          @php
            $position = $bookingInfo->currency_text_position;
            $currency = $bookingInfo->currency_text;
          @endphp

          <div class="row">
            <div class="col">
              <table class="table table-striped table-bordered table-invoice-style-1">
                <tbody>
                  <tr>
                    <th scope="col">{{ __('Booking Number:') }}</th>
                    <td>{{ '#' . $bookingInfo->order_number }}</td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Booking Date:') }}</th>
                    <td>
                      {{ date_format($bookingInfo->created_at, 'M d, Y') }}
                    </td>
                  </tr>

                  @php
                    $arrival_date = Carbon\Carbon::parse($bookingInfo->check_in_date)->format('M d, Y');
                    $arrival_time = Carbon\Carbon::parse($bookingInfo->check_in_time)->format(
                        $websiteInfo->time_format == 24 ? 'H:i' : 'h:i A',
                    );
                    $departure_date = Carbon\Carbon::parse($bookingInfo->check_out_date)->format('M d, Y');
                    $departure_time = Carbon\Carbon::parse($bookingInfo->check_out_time)->format(
                        $websiteInfo->time_format == 24 ? 'H:i' : 'h:i A',
                    );
                  @endphp

                  <tr>
                    <th scope="col">{{ __('Check-in Date:') }}</th>
                    <td>
                      {{ $arrival_date }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Check-in Time:') }}</th>
                    <td>
                      {{ $arrival_time }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Check-out Date:') }}</th>
                    <td>
                      {{ $departure_date }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Check-out Time:') }}</th>
                    <td>
                      {{ $departure_time }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Room Name:') }}</th>
                    <td>
                      {{ $bookingInfo->hotelRoom->room_content->where('language_id', $currentLanguageInfo->id)->first()->title }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Room Rent:') }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                       {{ number_format($bookingInfo->roomPrice, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Additional Service Charge:') }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                      {{ number_format($bookingInfo->serviceCharge, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Discount:') }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                      {{ number_format($bookingInfo->discount, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Subtotal:') }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                      {{ number_format($bookingInfo->total, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Tax:') }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                      {{ number_format($bookingInfo->tax, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Grand Total:') }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                      {{ number_format($bookingInfo->grand_total, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Customer Name:') }}</th>
                    <td>{{ $bookingInfo->booking_name }}</td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Customer Phone:') }}</th>
                    <td>{{ $bookingInfo->booking_phone }}</td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Paid via:') }}</th>
                    <td>{{ $bookingInfo->payment_method }}</td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Payment Status:') }}</th>
                    <td>
                      @if ($bookingInfo->payment_status == 1)
                        {{ __('Paid') }}
                      @else
                        {{ __('Unpaid') }}
                      @endif
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
