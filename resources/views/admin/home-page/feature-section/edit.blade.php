<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Feature') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="ajaxEditForm" class="modal-form create" action="{{ route('admin.pages.feature_content.update') }}"
          method="post">
          <input type="hidden" id="in_id" name="id">
          @csrf

          <div class="row no-gutters">
            <div class="col-md-12">
              <div class="form-group">
                <label for="">{{ __('Title') . '*' }}</label>
                <input type="text" class="form-control" id="in_title" name="title"
                  placeholder="{{ __('Enter Title') }}">
                <p id="editErr_title" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="">{{ __('Icon Image') . '*' }}</label>
            <br>
            <div class="thumb-preview">
              <img src="" alt="..." class="uploaded-img in_image">
            </div>

            <div class="mt-3">
              <div role="button" class="btn btn-primary btn-sm upload-btn">
                {{ __('Choose Image') }}
                <input type="file" class="img-input" name="image">
              </div>
            </div>
            <p id="editErr_image" class="mt-2 mb-0 text-danger em"></p>
          </div>
          @if ($settings->theme_version != 2)
            <div class="row no-gutters">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="">{{ __('Sub Title') }}</label>
                  <input type="text" class="form-control" id="in_subtitle" name="subtitle"
                    placeholder="{{ __('Enter Sub Title') }}">
                  <p id="editErr_subtitle" class="mt-2 mb-0 text-danger em"></p>
                </div>
              </div>
            </div>
          @endif

          @if ($settings->theme_version == 2)
            <div class="row no-gutters">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="">{{ __('Text') }}</label>
                  <input type="text" class="form-control ltr" name="text" placeholder="{{ __('Enter Text') }}"
                    id="in_text">
                  <p id="editErr_text" class="mt-2 mb-0 text-danger em"></p>
                </div>
              </div>
            </div>
          @endif
          <div class="row no-gutters">
            <div class="col-md-12">
              <div class="form-group">
                <label for="">{{ __('Serial Number') . '*' }}</label>
                <input type="number" class="form-control ltr" name="serial_number"
                  placeholder="{{ __('Enter Serial Number') }}" id="in_serial_number">
                <p id="editErr_serial_number" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          {{ __('Close') }}
        </button>
        <button id="updateBtn" type="button" class="btn btn-primary btn-sm">
          {{ __('Update') }}
        </button>
      </div>
    </div>
  </div>
</div>
