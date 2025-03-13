<div class="modal fade" id="featured" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Send Requests') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="feature-payment-form" class="modal-form create"
          action="{{ route('vendor.hotel_management.hotel.purchase_feature') }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @php
            $language = App\Models\Language::where('code', request()->language)->firstOrFail();
          @endphp
          <input type="hidden" name="hotel_id" id="hotel_id">
          <input type="hidden" name="language_id" value="{{ $language->id }}">
          <input type="hidden" name="vendor_id" value="{{ Auth::guard('vendor')->user()->id }}">
          <div class="form-group p-0 mt-2">
            <label for="">{{ __('Select an Option') . '*' }}</label>
            @foreach ($charges as $index => $charge)
              <ul class="list-group list-group-bordered mb-2">
                <li class="list-group-item">
                  <div class="form-check p-0">
                    <label class="form-check-label mb-0" for="radio_{{ $charge->id }}">
                      <input class="form-check-input ml-0" type="radio" name="charge" id="radio_{{ $charge->id }}"
                        value="{{ $charge->id }}" {{ $index === 0 ? 'checked' : '' }}>
                      {{ $charge->days }} {{ __('Days For') }}
                      {{ symbolPrice($charge->price) }}
                    </label>
                  </div>
                </li>
              </ul>
            @endforeach
            <span id="err_charge" class="mt-2 mb-0 text-danger em"></span>
          </div>
          <div class="form-group p-0 mt-1">
            <label>{{ __('Payment Method') . '*' }}</label>
            <div class="form-group p-0 mt-1">
              <select name="gateway" id="gateway" class="form-control form-select select2 js-example-basic-single8">
                <option selected disabled>{{ __('Choose a Payment Method') }}</option>
                @foreach ($onlineGateways as $getway)
                  <option @selected(old('gateway') == $getway->keyword) value="{{ $getway->keyword }}">{{ __($getway->name) }}
                  </option>
                @endforeach

                @if (count($offline_gateways) > 0)
                  @foreach ($offline_gateways as $offlineGateway)
                    <option @if (old('gateway') == $offlineGateway->id) selected @endif value="{{ $offlineGateway->id }}">
                      {{ __($offlineGateway->name) }}
                    </option>
                  @endforeach
                @endif
              </select>
              <span id="err_gateway" class="mt-2 mb-0 text-danger em"></span>
            </div>
          </div>

          <!-- Stripe Payment Will be Inserted here -->
          <div id="stripe-element" class="mb-2">
            <!-- A Stripe Element will be inserted here. -->
          </div>

          <!-- Used to display form errors -->
          <div id="stripe-errors" class="pb-2" role="alert"></div>
          <span id="err_stripeToken" class="mt-2 mb-0 text-danger em"></span>


          <!-- iyzico payment -->
          <div id="iyzico-element" class="d-none">
            <input type="text" name="identity_number" class="form-control"
              placeholder="{{ __('Identity Number') }}">
            @error('identity_number')
              <p class="text-danger text-left">{{ $message }}</p>
            @enderror
          </div>

          <!-- Authorize.net Payment Will be Inserted here -->
          <div class="row gateway-details pb-4 d-none" id="authorizenet-element">
            <div class="col-lg-6">
              <div class="form-group mb-3">
                <input class="form-control" type="text" id="anetCardNumber" placeholder="{{ __('Card Number') }}"
                  disabled />
              </div>
            </div>
            <div class="col-lg-6 mb-3">
              <div class="form-group">
                <input class="form-control" type="text" id="anetExpMonth" placeholder="{{ __('Expire Month') }}"
                  disabled />
              </div>
            </div>
            <div class="col-lg-6 ">
              <div class="form-group">
                <input class="form-control" type="text" id="anetExpYear" placeholder="{{ __('Expire Year') }}"
                  disabled />
              </div>
            </div>
            <div class="col-lg-6 ">
              <div class="form-group">
                <input class="form-control" type="text" id="anetCardCode" placeholder="{{ __('Card Code') }}"
                  disabled />
              </div>
            </div>
            @php
                $none = 'none';
            @endphp
            <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
            <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
            <ul id="authorizeNetErrors" style="display: {{ $none }};"></ul>
          </div>
          {{-- END: Authorize.net Card Details Form --}}

          <!-- offline payment instruction -->
          @foreach ($offline_gateways as $offlineGateway)
            <div class="@if ($errors->has('attachment') && request()->session()->get('gatewayId') == $offlineGateway->id) d-block @else d-none @endif offline-gateway-info"
              id="{{ 'offline-gateway-' . $offlineGateway->id }}">
              @if (!is_null($offlineGateway->short_description))
                <div class="form-group mb-4">
                  <label>{{ __('Description') }}</label>
                  <p>{{ $offlineGateway->short_description }}</p>
                </div>
              @endif

              @if (!is_null($offlineGateway->instructions))
                <div class="form-group mb-4">
                  <label>{{ __('Instructions') }}</label>
                  {!! replaceBaseUrl($offlineGateway->instructions, 'summernote') !!}
                </div>
              @endif

              @if ($offlineGateway->has_attachment == 1)
                <div class="form-group mb-4">
                  <label>{{ __('Attachment') . '*' }}</label>
                  <br>
                  <input type="file" name="attachment" class="form-control">
                  <span id="err_attachment" class="mt-2 mb-0 text-danger em"></span>
                </div>
              @endif
            </div>
            <span id="err_currency" class="mt-2 mb-0 text-danger em"></span>
          @endforeach
          <div class="mt-2">
            <button class="btn btn-primary" type="submit" form="feature-payment-form"
              id="featuredBtn">{{ __('Submit') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
