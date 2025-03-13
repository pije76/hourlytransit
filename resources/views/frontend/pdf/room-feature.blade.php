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
  {{-- <link rel="stylesheet" href="{{ asset('assets/admin/css/invoice.css') }}"> --}}
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
              {{ __('ROOM FEATURE INVOICE') }}
            </h2>
          </div>

          <div class="row">
            <div class="col">
              <table class="table table-striped table-bordered table-invoice-style-1">
                <tbody>

                  <tr>
                    <th scope="col">{{ __('Order Date') . ':' }}</th>
                    <td>
                      {{ date_format($order->created_at, 'M d, Y') }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Room Title') . ':' }}</th>
                    <td>
                      {{ $order->room->room_content->where('language_id', $currentLanguageInfo->id)->first()->title }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Days') . ':' }}</th>
                    <td class="text-capitalize">
                      {{ $order->days }}
                    </td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Paid via:') }}</th>
                    <td>{{ $order->payment_method }}</td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Pay amount') . ':' }}</th>
                    <td class="text-capitalize">
                      {{ $position == 'left' ? $currency . ' ' : '' }}
                      {{ number_format($order->total, 2) }}
                      {{ $position == 'right' ? ' ' . $currency : '' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="col">{{ __('Vendor Name') }}</th>
                    <td>{{ $order->vendor->username }}</td>
                  </tr>

                  <tr>
                    <th scope="col">{{ __('Payment Status') . ':' }}</th>
                    <td>
                      {{ $order->payment_status }}
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
