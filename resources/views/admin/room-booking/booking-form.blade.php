@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('New Booking') }}</h4>
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
        <a href="#">{{ __('New Booking') }}</a>
      </li>
    </ul>
  </div>
  @php
    $room = App\Models\Room::Where('id', request()->input('room_id'))->first();
  @endphp
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-6">
              <div class="card-title d-inline-block">{{ __('New Booking') }}</div>
            </div>
            <div class="col-lg-6">
              <a class="btn btn-info btn-sm float-lg-right float-left d-inline-block"
                href="{{ route('admin.room_bookings.all_bookings', ['language' => $defaultLang->code]) }}">
                <span class="btn-label">
                  <i class="fas fa-backward"></i>
                </span>
                {{ __('Back') }}
              </a>
            </div>
          </div>

        </div>

        <div class="card-body">

          <form id="ajaxForm" action="{{ route('admin.room_bookings.make_booking') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-lg-8 offset-lg-1">
                <input type="hidden" name="room_id" value="{{ request()->input('room_id') }}">
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <div class="form-group">
                        <label for="checkInDate">{{ __('Check-in Date') }}</label>
                        <input type="text" class="form-control "value="{{ old('checkInDate') }}" id="checkInDate"
                          name="checkInDate" placeholder="MM/DD/YYYY" />
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label for="checkInTime">{{ __('Check-in Time') }}</label>
                      <input type="text" class="form-control " id="checkInTime"value="{{ old('checkInTime') }}"
                        name="checkInTime" placeholder="HH:MM:A" />
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Adult') . '*' }}</label>
                      <select name="adult" class="form-control">
                        <option selected disabled>{{ __('Select Adult Number') }}</option>
                        @for ($i = 1; $i <= $room->adult; $i++)
                          <option value="{{ $i }}" {{ old('adult') == $i ? 'selected' : '' }}>
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
                          <option value="{{ $i }}" {{ old('children') == $i ? 'selected' : '' }}>
                            {{ $i }}
                          </option>
                        @endfor
                      </select>
                      <p id="err_children" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Subtotal') . ' (' . $currencyInfo->base_currency_text . ')' }}</label>
                      <input type="text" class="form-control" name="subtotal" value="0.00" readonly id="subtotal">
                    </div>
                  </div>
                  <input type="hidden" name="additional_charge" id="additional_charge" value="0.00">
                  <input type="hidden" name="room_price" id="room_price" value="0.00">

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Discount') . ' (' . $currencyInfo->base_currency_text . ')' }}</label>
                      <input type="text" class="form-control" name="discount" value="0.00" id="discount"
                        placeholder="Enter Discount Amount" oninput="applyDiscount()">
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Total Rent') . ' (' . $currencyInfo->base_currency_text . ')' }}</label>
                      <input type="text" class="form-control" name="total" value="0.00" readonly id="total">
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Customer Full Name') . '*' }}</label>
                      <input type="text" class="form-control" placeholder="{{ __('Enter Full Name') }}"
                        name="customer_name" value="{{ old('customer_name') }}">
                      <p id="err_customer_name" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Customer Phone Number') . '*' }}</label>
                      <input type="text" class="form-control" placeholder="{{ __('Enter Phone Number') }}"
                        name="customer_phone" value="{{ old('customer_phone') }}">
                      <p id="err_customer_phone" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Customer Email') . '*' }}</label>
                      <input type="email" class="form-control" placeholder="{{ __('Enter Customer Email') }}"
                        name="customer_email" value="{{ old('customer_email') }}">
                      <p id="err_customer_email" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Payment Method') . '*' }}</label>
                      <select name="payment_method" class="form-control">
                        <option selected disabled>{{ __('Select a Method') }}</option>

                        @if (count($onlineGateways) > 0)
                          @foreach ($onlineGateways as $onlineGateway)
                            <option {{ old('payment_method') == $onlineGateway->name ? 'selected' : '' }}
                              value="{{ $onlineGateway->name }}">
                              {{ $onlineGateway->name }}
                            </option>
                          @endforeach
                        @endif

                        @if (count($offlineGateways) > 0)
                          @foreach ($offlineGateways as $offlineGateway)
                            <option {{ old('payment_method') == $offlineGateway->name ? 'selected' : '' }}
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
                        <option {{ old('payment_status') == '1' ? 'selected' : '' }} value="1">
                          {{ __('Paid') }}
                        </option>
                        <option {{ old('payment_status') == '0' ? 'selected' : '' }} value="0">
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
                        <label class="search-containerssss-title">{{ __('Time slot') }}</label>
                        <ul class="list-group custom-radio">
                          @foreach ($hourlyPrices as $hourlyPrice)
                            @php
                              $price = App\Models\BookingHour::find($hourlyPrice->hour_id);
                            @endphp
                            <li>
                              <input class="input-radio handleRadioClick" type="radio" name="price"
                                id="radio_{{ $hourlyPrice->id }}"
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
                      <label>{{ __('Additional Service') }}</label>
                      <table class="shopping-table shadow-none shopping-table-style-1">
                        <thead>
                          <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody class="shopping-table-body">
                          @foreach ($additionalServices as $id => $charge)
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
                {{ __('Submit') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @php
    $hotel = App\Models\RoomContent::Where('room_id', request()->input('room_id'))->get();
  @endphp
  <form action="{{ route('admin.room_bookings.booking_form', ['room_id' => request()->input('room_id')]) }}"
    id="searchForm" method="GET">
    <input type="hidden" name="checkInDate" id="checkInDates"value="{{ request()->input('checkInDate') }}">
    <input type="hidden" name="checkInTime" id="checkInTimes"value="{{ request()->input('checkInTime') }}">
  </form>
@endsection

@section('script')
  <script>
    'use strict';
    let taxdata = '{{ $tax }}';
    var searchUrl = "{{ route('admin.room_bookings.get_hourly_price', ['slug' => ':slug', 'id' => ':id']) }}";
    searchUrl = searchUrl.replace(':slug', '{{ $hotel->first()->slug }}');
    searchUrl = searchUrl.replace(':id', '{{ $hotel->first()->room_id }}');
    var holidays = @json($holidayDates);
  </script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-booking-room.js') }}"></script>
@endsection
