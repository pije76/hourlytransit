@extends('frontend.layout')

@section('pageHeading')
  {{ __('Reset Password') }}
@endsection


@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => __('Reset Password'),
  ])

  <!-- Authentication-area start -->
  <div class="authentication-area ptb-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6"> 
          <div class="auth-form pb-20 border radius-md">
            @if (Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            @if (Session::has('warning'))
              <div class="alert alert-success">{{ Session::get('warning') }}</div>
            @endif
            <form action="{{ route('user.reset_password_submit') }}" method="POST">
              @csrf
              <div class="title py-3 px-20">
                <h4 class="mb-0">{{ __('Reset Password') }}</h4>
              </div>
              <div class="form-group px-20 mb-20">
                <input type="password" class="form-control" name="new_password" placeholder="{{ __('New Password') }}">
                @error('new_password')
                  <p class="text-danger mt-2">{{ $message }}</p>
                @enderror
              </div>
              <div class="form-group px-20 mb-20">
                <input type="password" class="form-control" name="new_password_confirmation"
                  placeholder="{{ __('Confirm Password') }}">
                @error('new_password_confirmation')
                  <p class="text-danger mt-2">{{ $message }}</p>
                @enderror
              </div>
              <div class="auth-form-btn-area px-20">
                <button type="submit" class="btn btn-lg btn-primary radius-md w-100">{{ __('Reset Password') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Authentication-area end -->
@endsection
