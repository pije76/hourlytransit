@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Add a ticket') }}</h4>
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
        <a href="#">{{ __('Support Tickets') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Add a ticket') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('vendor.support_ticket.store') }}" enctype="multipart/form-data" method="POST">
          <div class="card-header">
            <div class="card-title d-inline-block">{{ __('Add a ticket') }}</div>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-lg-8 offset-lg-2">
                <div class="alert alert-danger pb-1 dis-none" id="equipmentErrors">
                  <ul></ul>
                </div>

                @csrf
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Email') . '*' }}</label>
                      <input type="email" class="form-control" value="{{ Auth::guard('vendor')->user()->email }}"
                        name="email" placeholder="{{ __('Enter Email') }}">
                    </div>
                    @error('email')
                      <p class="text-danger">{{ $message }}</p>
                    @enderror
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label>{{ __('Subject') . '*' }}</label>
                      <input type="text" class="form-control" name="subject" placeholder="{{ __('Enter Subject') }}">
                    </div>
                    @error('subject')
                      <p class="text-danger">{{ $message }}</p>
                    @enderror
                  </div>

                  <div class="col-lg-12">
                    <div class="form-group">
                      <label>{{ __('Description') }}</label>
                      <textarea name="description" rows="4" class="form-control summernote"></textarea>
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label for="">{{ __('Attachment') }}</label>
                      <div class="input-group">
                        <div class="custom-file">
                          <input type="file" name="attachment" class="custom-file-input"
                            data-href="#"
                            id="zip_file">
                          <label class="custom-file-label" for="zip_file">{{ __('Choose file') }}</label>
                        </div>
                      </div>
                     

                      <div class="progress progress-sm d-none">
                        <div class="progress-bar bg-success " role="progressbar" aria-valuenow="" aria-valuemin="0"
                          aria-valuemax=""></div>
                      </div>

                      <p class="text-warning">{{ __('Upload only ZIP Files, Max File Size is 20 MB') }}</p>
                       @error('attachment')
                        <p class="text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                @php
                  $vendorId = Auth::guard('vendor')->user()->id;
                  $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);
                @endphp
                @if ($current_package == '[]')
                  <button type="button" class="btn btn-success noPackage">
                    {{ __('Save') }}
                  </button>
                @else
                  <button type="submit" class="btn btn-success">
                    {{ __('Save') }}
                  </button>
                @endif
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
@section('script')
  <script type="text/javascript" src="{{ asset('assets/admin/js/support-ticket.js') }}"></script>
@endsection
