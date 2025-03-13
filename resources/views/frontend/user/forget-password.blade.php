@extends('frontend.layout')
@section('pageHeading')
  {{ __('Forget Password') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_forget_password }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_forget_password }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->forget_password_page_title : __('Forget Password'),
  ])


  <!-- Authentication-area start -->
  <div class="authentication-area ptb-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="auth-form pb-20 border radius-md">
            @if (Session::has('success'))
              <div class="alert alert-success">{{ __(Session::get('success')) }}</div>
            @endif
            @if (Session::has('warning'))
              <div class="alert alert-success">{{ __(Session::get('warning')) }}</div>
            @endif
            <form action="{{ route('user.send_forget_password_mail') }}" method="POST">
              @csrf
              <div class="title py-3 px-20">
                <h4 class="mb-0">{{ __('Forget Password') }}</h4>
              </div>
              <div class="form-group px-20 mb-20">
                <input type="text" class="form-control"value="{{ old('email') }}" name="email" placeholder="{{ __('Email Address') }}">
                @error('email')
                  <p class="text-danger mt-2">{{ $message }}</p>
                @enderror
              </div>
              <div class="auth-form-btn-area px-20">
                <button type="submit" class="btn btn-lg btn-primary radius-md w-100">{{ __('Send me a recovery link') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Authentication-area end -->
@endsection
