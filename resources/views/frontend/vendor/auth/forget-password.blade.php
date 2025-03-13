@extends('frontend.layout')

@section('pageHeading')
  {{ !empty($pageHeading) ? $pageHeading->vendor_forget_password_page_title : __('Forget Password') }}
@endsection
@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keywords_vendor_forget_password }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_descriptions_vendor_forget_password }}
  @endif
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->vendor_forget_password_page_title : __('Forget Password'),
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
            @if (Session::has('error'))
              <div class="alert alert-danger">{{ __(Session::get('error')) }}</div>
            @endif
            <form action="{{ route('vendor.forget.mail') }}" method="POST">
              @csrf
              <div class="title py-3 px-20">
                <h4 class="mb-0">{{ __('Forget Password') }}</h4>
              </div>
              <div class="form-group px-20 mb-20">
                <input type="email" class="form-control" name="email" placeholder="{{ __('Email Address') }}" required>
                @error('email')
                  <p class="text-danger mt-2">{{ $message }}</p>
                @enderror
              </div>
              <div class="auth-form-btn-area px-20">
                <button type="submit" class="btn btn-lg btn-primary radius-md w-100"> {{ __('Submit') }} </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Authentication-area end -->
@endsection
