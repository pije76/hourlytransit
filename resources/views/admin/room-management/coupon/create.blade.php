<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Coupon') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="ajaxForm" class="modal-form" action="{{ route('admin.room_management.coupon.store') }}"
          method="post">
          @csrf
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Name') . '*' }}</label>
                <input type="text" class="form-control" name="name" placeholder="{{ __('Enter Coupon Name') }}">
                <p id="err_name" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Code') . '*' }}</label>
                <input type="text" class="form-control" name="code" placeholder="{{ __('Enter Coupon Code') }}">
                <p id="err_code" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Coupon Type') . '*' }}</label>
                <select name="type" class="form-control">
                  <option selected disabled>{{ __('Select a Type') }}</option>
                  <option value="fixed">{{ __('Fixed') }}</option>
                  <option value="percentage">{{ __('Percentage') }}</option>
                </select>
                <p id="err_type" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Value') . '*' }}</label>
                <input type="number" step="0.01" class="form-control" name="value"
                  placeholder="{{ __('Enter Coupon Value') }}">
                <p id="err_value" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Start Date') . '*' }}</label>
                <input type="text" class="form-control datepicker" name="start_date"
                  placeholder="{{ __('Enter Start Date') }}" autocomplete="off">
                <p id="err_start_date" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('End Date') . '*' }}</label>
                <input type="text" class="form-control datepicker" name="end_date"
                  placeholder="{{ __('Enter End Date') }}" autocomplete="off">
                <p id="err_end_date" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>
            <div class="col-lg-12">
              <div class="form-group">
                <label for="">{{ __('Rooms') }}</label>
                <select name="rooms[]" class="form-control select2" multiple="multiple">
                  @foreach ($rooms as $room)
                    <option value="{{ $room->id }}">
                      {{ $room->title }} ({{ $room->vendor->username ?? 'Admin' }})
                    </option>
                  @endforeach
                </select>
                <p class="text-warning mt-2 mb-0">
                  <small>
                    {{ __('This coupon can be applied to these rooms.') }}<br>
                    {{ __('Leave this field empty for all rooms.') }}
                  </small>
                </p>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          {{ __('Close') }}
        </button>
        <button id="submitBtn" type="button" class="btn btn-primary btn-sm">
          {{ __('Save') }}
        </button>
      </div>
    </div>
  </div>
</div>
