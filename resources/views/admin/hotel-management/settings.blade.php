@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Settings') }}</h4>
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
        <a href="#">{{ __('Hotels Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Settings') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('admin.hotel_management.update_settings') }}" method="post">
          @csrf
          <div class="card-header">
            <div class="row">
              <div class="col-lg-10">
                <div class="card-title">{{ __('Settings') }}</div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                <div class="form-group">
                  <label>{{ __('Hotel View') }}</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="hotel_view" value="1" class="selectgroup-input"
                        {{ $info->hotel_view == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Gird') }}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="hotel_view" value="0" class="selectgroup-input"
                        {{ $info->hotel_view == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Map') }}</span>
                    </label>
                  </div>
                  @error('hotel_view')
                    <p class="mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>
                <div class="form-group">
                  <label>{{ __('Time Format') }}</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="time_format" value="12" class="selectgroup-input"
                        {{ $info->time_format == 12 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('12 Hour') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="time_format" value="24" class="selectgroup-input"
                        {{ $info->time_format == 24 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('24 Hour') }}</span>
                    </label>
                  </div>
                  @error('time_format')
                    <p class="mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-success">
                  {{ __('Update') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
