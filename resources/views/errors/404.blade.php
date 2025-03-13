@extends('frontend.layout')

@section('pageHeading')
  {{ __('404') }}
@endsection
@php
  $basicInfo = App\Models\BasicSettings\Basic::select('breadcrumb', 'theme_version')->firstOrFail();
  $version = $basicInfo->theme_version;

  if (request()->session()->has('currentLocaleCode')) {
      $locale = request()->session()->get('currentLocaleCode');
  }
  if (empty($locale)) {
      // set the default language as system locale
      $languageCode = App\Models\Language::where('is_default', '=', 1)->pluck('code')->first();

      App::setLocale($languageCode);
  } else {
      // set the selected language as system locale
      App::setLocale($locale);
  }
@endphp


@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $basicInfo->breadcrumb,
      'title' => __('404'),
  ])
  <!--====== Start Error Section ======-->

  <div class="error-area ptb-100">
    <div class="container">
      <div class="error-content">
        <img class="lazyload" src="assets/images/placeholder.png" data-src="{{ asset('assets/img/404.svg') }}"
          alt="image">
        <h3>{{ __('Ooops! Page Not Found') }}</h3>
        <p>
          {{ __('The page you are looking for might have been removed had its name changed or is temporarily unavailable.') }}
        </p>
        <a href="{{ route('index') }}" class="btn btn-lg btn-primary radius-sm" title="Back to Home"
          target="_self">{{ __('Back to Home') }}</a>
      </div>
    </div>
  </div>
  <!--====== End Error Section ======-->
@endsection
