@extends('frontend.layout')
@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->signup_page_title ? $pageHeading->signup_page_title : __('Signup') }}
  @else
    {{ __('Signup') }}
  @endif
@endsection
@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keywords_vendor_signup }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_vendor_signup }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->signup_page_title : __('Signup'),
  ])

  <!-- Authentication Start -->
  <div class="authentication-area bg-light ptb-100">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="main-form">
            <div class="main-form-wrapper">
              <h3 class="title mb-30 text-center">{{ __('Let\'s go') }}!</h3>
              @if (Session::has('success'))
                <div class="alert alert-success">{{ __(Session::get('success')) }}</div>
              @endif
              <form action="{{ route('user.signup_submit') }}" method="POST">
                @csrf
                <div class="form-group mb-20">
                  <label for="username" class="form-label font-sm">{{ __('Username') }}<span
                      class="color-red">*</span></label>
                  <input type="text" class="form-control" name="username" value="{{ old('username') }}"
                    placeholder="{{ __('Username') }}" required>
                  @error('username')
                    <p class="text-danger mt-2">{{ $message }}</p>
                  @enderror
                </div>
                <div class="form-group mb-20">
                  <label for="email" class="form-label font-sm">{{ __('Email') }}<span
                      class="color-red">*</span></label>
                  <input type="text" class="form-control" name="email" value="{{ old('email') }}"
                    placeholder="{{ __('Email') }}" required>
                  @error('email')
                    <p class="text-danger mt-2">{{ $message }}</p>
                  @enderror
                </div>
                <div class="form-group mb-20">
                  <label for="password" class="form-label font-sm">{{ __('Password') }}<span
                      class="color-red">*</span></label>
                  <div class="position-relative">
                    <input type="password" class="form-control" name="password" value="{{ old('password') }}"
                      placeholder="{{ __('Password') }}" required>
                    <span class="show-password-field">
                      <i class="show-icon"></i>
                    </span>
                    @error('password')
                      <p class="text-danger mt-2">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="form-group mb-20">
                  <label for="password_confirmation" class="form-label font-sm">{{ __('Confirm Password') }}<span
                      class="color-red">*</span></label>
                  <div class="position-relative">
                    <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}"
                      class="form-control" placeholder="{{ __('Confirm Password') }}" required>
                    <span class="show-password-field">
                      <i class="show-icon"></i>
                    </span>
                    @error('password_confirmation')
                      <p class="text-danger mt-2">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                @if ($recaptchaInfo->google_recaptcha_status == 1)
                  <div class="form-group mb-30">
                    {!! NoCaptcha::renderJs() !!}
                    {!! NoCaptcha::display() !!}

                    @error('g-recaptcha-response')
                      <p class="mt-1 text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                @endif
                <div class="text-center pt-10">
                  <button class="btn btn-lg btn-primary w-100 radius-sm" type="submit"
                    aria-label="Signup">{{ __('Signup') }}</button>
                </div>
              </form>
            </div>
            <div class="text-center mt-20">
              <div class="link font-sm">
                {{ __('Already a member') }}? <a href="{{ route('user.login') }}" target="_self"
                  title="{{ __('Login Now') }}">{{ __('Login Now') }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Authentication End -->
@endsection
