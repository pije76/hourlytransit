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
        <a href="#">{{ __('Room Bookings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Booking Details') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-6">
              <div class="card-title d-inline-block">{{ __('Edit Booking Details') }}</div>
            </div>
            <div class="col-lg-6">
              <a class="btn btn-info btn-sm float-lg-right float-left d-inline-block"
                href="{{ route('vendor.room_bookings.all_bookings', ['language' => $defaultLang->code]) }}">
                <span class="btn-label">
                  <i class="fas fa-backward"></i>
                </span>
                {{ __('Back') }}
              </a>
            </div>
          </div>

        </div>

        <div class="card-body">
          <form id="ajaxForm" action="{{ route('vendor.room_bookings.update_booking') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-lg-8 offset-lg-1">
                <input type="hidden" name="booking_id" value="{{ $details->id }}">

                <input type="hidden" name="room_id" value="{{ $details->room_id }}">

                <div class="row">
                  @php
                    $formattedDate = date('m/d/Y', strtotime($details->check_in_date));
                  @endphp
                  <div class="col-lg-6">
                    <div class="form-group">
                      <div class="form-group">
                        <label for="checkInDate">{{ __('Check-in Date') }}</label>
                        <input type="text" class="form-control "value="{{ $formattedDate }}" id="checkInDate"
                          name="checkInDate" placeholder="MM/DD/YYYY" />
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label for="checkInTime">{{ __('Check-in Time') }}</label>
                      <input type="text" class="form-control " id="checkInTime"value="{{ $details->check_in_time }}"
                        name="checkInTime" placeholder="HH:MM:A" />
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Booking Number') }}</label>
                      <input type="text" name="order_number" class="form-control"
                        value="{{ '#' . $details->order_number }}" readonly>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Booking Date') }}</label>
                      <input type="text" class="form-control"
                        value="{{ date_format($details->created_at, 'F d, Y') }}" readonly>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Customer Full Name') . '*' }}</label>
                      <input type="text" class="form-control" placeholder="{{ __('Enter Full Name') }}"
                        name="customer_name" value="{{ $details->booking_name }}">
                      <p id="err_customer_name" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Customer Email') . '*' }}</label>
                      <input type="email" class="form-control" placeholder="{{ __('Enter Customer Email') }}"
                        name="customer_email" value="{{ $details->booking_email }}">
                      <p id="err_customer_email" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Customer Phone Number') . '*' }}</label>
                      <input type="text" class="form-control" placeholder="{{ __('Enter Phone Number') }}"
                        name="customer_phone" value="{{ $details->booking_phone }}">
                      <p id="err_customer_phone" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Room Name') }}</label>
                      <input type="text" class="form-control" value="{{ $roomTitle }}" readonly>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Adult') . '*' }}</label>
                      <select name="adult" class="form-control">
                        <option selected disabled>{{ __('Select Adult Number') }}</option>
                        @for ($i = 1; $i <= $room->adult; $i++)
                          <option value="{{ $i }}"
                            {{ old('adult', isset($details->adult) ? $details->adult : '') == $i ? 'selected' : '' }}>
                            {{ $i }}
                          </option>
                        @endfor
                      </select>
                      <p id="err_adult" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Children') }}</label>
                      <select name="children" class="form-control">
                        <option selected disabled>{{ __('Select Children Number') }}</option>
                        @for ($i = 0; $i <= $room->children; $i++)
                          <option value="{{ $i }}"
                            {{ old('children', isset($details->children) ? $details->children : '') == $i ? 'selected' : '' }}>
                            {{ $i }}
                          </option>
                        @endfor
                      </select>
                      <p id="err_children" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Subtotal') . ' (' . $details->currency_text . ')' }}</label>
                      <input type="text" class="form-control" name="subtotal" value="{{ $details->total }}"
                        readonly id="subtotal">
                    </div>
                  </div>
                  <input type="hidden" name="additional_charge" id="additional_charge"
                    value="{{ $details->serviceCharge }}">
                  <input type="hidden" name="room_price" id="room_price" value="{{ $details->roomPrice }}">

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Discount') . ' (' . $details->currency_text . ')' }}</label>
                      <input type="text" class="form-control" name="discount" value="{{ $details->discount }}"
                        id="discount" placeholder="Enter Discount Amount" oninput="applyDiscount()">
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Total Rent') . ' (' . $details->currency_text . ')' }}</label>
                      <input type="text" class="form-control" name="total" value="{{ $details->grand_total }}"
                        readonly id="total">
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Payment Method') . '*' }}</label>
                      <select name="payment_method" class="form-control">
                        <option disabled>{{ __('Select a Method') }}</option>

                        @if (count($onlineGateways) > 0)
                          @foreach ($onlineGateways as $onlineGateway)
                            <option {{ $details->payment_method == $onlineGateway->name ? 'selected' : '' }}
                              value="{{ $onlineGateway->name }}">
                              {{ $onlineGateway->name }}
                            </option>
                          @endforeach
                        @endif

                        @if (count($offlineGateways) > 0)
                          @foreach ($offlineGateways as $offlineGateway)
                            <option {{ $details->payment_method == $offlineGateway->name ? 'selected' : '' }}
                              value="{{ $offlineGateway->name }}">
                              {{ $offlineGateway->name }}
                            </option>
                          @endforeach
                        @endif
                      </select>
                      <p id="err_payment_method" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Payment Status') . '*' }}</label>
                      <select name="payment_status" class="form-control">
                        <option selected disabled>{{ __('Select Payment Status') }}</option>
                        <option value="1"
                          {{ old('payment_status', isset($details->payment_status) ? $details->payment_status : '') == '1' ? 'selected' : '' }}>
                          {{ __('Paid') }}
                        </option>
                        <option value="0"
                          {{ old('payment_status', isset($details->payment_status) ? $details->payment_status : '') == '0' ? 'selected' : '' }}>
                          {{ __('Unpaid') }}
                        </option>
                      </select>
                      <p id="err_payment_status" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3">
                <div>
                  <div class="search-containerssss mb-20">
                    @if (count($hourlyPrices) > 0)
                      <div class="form-group">
                        <label>{{ __('Time slot') }}</label>
                        <ul class="list-group custom-radio">
                          @foreach ($hourlyPrices as $hourlyPrice)
                            @php
                              $price = App\Models\BookingHour::find($hourlyPrice->hour_id);
                            @endphp
                            <li>
                              <input class="input-radio handleRadioClick" type="radio" name="price"
                                id="radio_{{ $hourlyPrice->id }}"@if ($hourlyPrice->price == $details->roomPrice) checked @endif
                                value="{{ $hourlyPrice->id }}"data-price="{{ $hourlyPrice->price }}">
                              <label class="form-radio-label" for="radio_{{ $hourlyPrice->id }}">
                                <span> {{ $price->hour }} {{ __('Hrs') }}</span> {{ __('for') }}
                                <span class="qty">{{ symbolPrice($hourlyPrice->price) }}</span>
                              </label>
                            </li>
                          @endforeach
                        </ul>
                      </div>
                    @else
                      <h6 class="mt-2 text-warning ps-3 pb-2">{{ __('No booking slot available') }}</h6>
                    @endif
                    <p id="err_price" class="mt-2 mb-0 text-danger em"></p>
                  </div>
                </div>
                @if ($additionalServices)
                  <div class="additional-service">
                    <div class="form-group">
                      <label class="title">{{ __('Additional Service') }}</label>
                      <table class="shopping-table shadow-none shopping-table-style-1">
                        <thead>
                          <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody class="shopping-table-body">
                          @php
                            $decoded_data = json_decode($details->additional_service, true);
                            if (is_array($decoded_data)) {
                                $takeServices = implode(',', $decoded_data);
                                $takeService = explode(',', $takeServices);
                                $takeService = array_map('trim', $takeService);
                            } else {
                                $takeService = [];
                            }
                          @endphp

                          @foreach ($additionalServices as $id => $charge)
                            @if (in_array($id, $takeService))
                              @php
                                $serviceTitile = App\Models\AdditionalServiceContent::Where([
                                    ['language_id', $defaultLang->id],
                                    ['additional_service_id', $id],
                                ])->get();
                              @endphp
                              <tr>
                                <td>
                                  <input type="checkbox" id="additional_service_{{ $id }}"
                                    name="additional_service[]" value="{{ $id }}" class="shipping_method"
                                    data-shipping_charge="{{ $charge }}" checked>
                                </td>
                                <td>
                                  <label for="additional_service_{{ $id }}">{{ @$serviceTitile[0]->title }}
                                    <br>
                                </td>
                                <td>{{ symbolPrice($charge) }}</td>
                              </tr>
                            @else
                              @php
                                $serviceTitile = App\Models\AdditionalServiceContent::Where([
                                    ['language_id', $defaultLang->id],
                                    ['additional_service_id', $id],
                                ])->get();
                              @endphp
                              <tr>
                                <td>
                                  <input type="checkbox" id="additional_service_{{ $id }}"
                                    name="additional_service[]" value="{{ $id }}" class="shipping_method"
                                    data-shipping_charge="{{ $charge }}">
                                </td>
                                <td>
                                  <label for="additional_service_{{ $id }}">{{ @$serviceTitile[0]->title }}
                                    <br>
                                </td>
                                <td>{{ symbolPrice($charge) }}</td>
                              </tr>
                            @endif
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </form>
        </div>

        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button id="submitBtn" type="button" class="btn btn-success">
                {{ __('Update') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @php
    $hotel = App\Models\RoomContent::Where('room_id', $details->room_id)->get();
  @endphp
  <form action="{{ route('vendor.room_bookings.booking_details', ['id' => $details->id]) }}" id="searchForm"
    method="GET">
    <input type="hidden" name="checkInDates" id="checkInDates"value="{{ $formattedDate }}">
    <input type="hidden" name="checkInTimes" id="checkInTimes"value="{{ $details->check_in_time }}">
    <input type="hidden" name="pricing_booking_id" id="pricing_booking_id"value="{{ $details->id }}">
  </form>
@endsection

@section('script')
  <script>
    'use strict';

    var searchUrl = "{{ route('vendor.room_bookings.get_hourly_price_edit', ['slug' => ':slug', 'id' => ':id']) }}";
    searchUrl = searchUrl.replace(':slug', '{{ $hotel->first()->slug }}');
    searchUrl = searchUrl.replace(':id', '{{ $hotel->first()->room_id }}');
    let taxdata = '{{ $tax }}';
    var holidays = @json($holidayDates);
  </script>

  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-booking-room.js') }}"></script>
@endsection
