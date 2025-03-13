@extends('frontend.layout')

@section('pageHeading')
  {{ __('Edit Profile') }}
@endsection


@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->edit_profile_page_title : __('Edit Profile'),
  ])


  <!--====== Start Dashboard Section ======-->
  <div class="user-dashboard pt-100 pb-60">
    <div class="container">
      <div class="row gx-xl-5">
        @includeIf('frontend.user.side-navbar')
        <div class="col-lg-9">
          <div class="user-profile-details mb-40">
            <div class="account-info radius-md">
              <div class="title">
                <h4>{{ __('Edit Profile') }}</h4>
              </div>
              <div class="edit-info-area">
                @if (Session::has('success'))
                  <div class="alert alert-success">{{ Session::get('success') }}</div>
                @endif
                <form action="{{ route('user.update_profile') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="upload-img">
                    <div class="file-upload-area">
                      <div class="file-edit">
                        <input type='file' id="imageUpload" name="image" />
                        <label for="imageUpload"></label>
                      </div>
                      <div class="file-preview">
                        @if (Auth::guard('web')->user()->image != null)
                          <div id="imagePreview" class="bg-img"
                            data-bg-img="{{ asset('assets/img/users/' . Auth::guard('web')->user()->image) }}"></div>
                        @else
                          <div id="imagePreview" class="bg-img" data-bg-img="{{ asset('assets/img/blank-user.jpg') }}">
                          </div>
                        @endif
                      </div>
                    </div>
                    <p class="mt-2 mb-0 text-warning">{{ __('Image Size') . '80x80' }} </p>
                    @error('image')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>

                  <div id="errorMsg"></div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Name') . ' *' }}</label>
                    <input type="text" class="form-control"
                      value="{{ old('name') ? old('name') : Auth::guard('web')->user()->name }}"
                      placeholder="{{ __('Name') }}" name="name">
                    @error('name')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Username') . ' *' }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Username') }}" name="username"
                      value="{{ old('username') ? old('username') : Auth::guard('web')->user()->username }}">
                    @error('username')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Email') . ' *' }}</label>
                    <input type="email" class="form-control" placeholder="{{ __('Email') }}" name="email"
                      value="{{ Auth::guard('web')->user()->email }}" readonly>
                    @error('email')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Phone') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Phone') }}" name="phone"
                      value="{{ old('phone') ? old('phone') : Auth::guard('web')->user()->phone }}">
                    @error('phone')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Country') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Country') }}" name="country"
                      value="{{ old('country') ? old('country') : Auth::guard('web')->user()->country }}">
                    @error('country')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('City') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('City') }}" name="city"
                      value="{{ old('city') ? old('city') : Auth::guard('web')->user()->city }}">
                    @error('city')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('State') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('State') }}" name="state"
                      value="{{ old('state') ? old('state') : Auth::guard('web')->user()->state }}">
                    @error('state')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Zip Code') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Zip Code') }}" name="zip_code"
                      value="{{ old('zip_code') ? old('zip_code') : Auth::guard('web')->user()->zip_code }}">
                    @error('zip_code')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="col-lg-12">
                  <div class="form-group mb-30">
                    <label for="" class="mb-1">{{ __('Address') }}</label>
                    <textarea name="address" id="" class="form-control" rows="3" placeholder="{{ __('Address') }}">{{ old('address') ? old('address') : Auth::guard('web')->user()->address }}</textarea>
                    @error('address')
                      <p class="text-danger mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="col-lg-12 mb-15">
                  <div class="form-button">
                    <button type="submit" class="btn btn-md btn-primary radius-sm">{{ __('Update Profile') }}</button>
                  </div>
                </div>
              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  <!--====== End Dashboard Section ======-->
@endsection
