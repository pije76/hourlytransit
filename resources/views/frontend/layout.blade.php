<!DOCTYPE html>
<html lang="zxx"dir="{{ $currentLanguageInfo->direction == 1 ? 'rtl' : '' }}">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="KreativDev">
  <meta name="keywords" content="@yield('metaKeywords')">
  <meta name="description" content="@yield('metaDescription')">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta property="og:title" content="@yield('ogTitle')">
  {{-- title --}}
  <title>@yield('pageHeading') {{ '| ' . $websiteInfo->website_title }}</title>
  {{-- fav icon --}}
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/' . $websiteInfo->favicon) }}">
  <link rel="apple-touch-icon" href="{{ asset('assets/img/' . $websiteInfo->favicon) }}">


  @includeIf('frontend.partials.styles')
</head>

<body class="theme-color">
  <!-- Preloader start -->
  @if ($basicInfo->preloader_status == 1)
    <div id="preLoader">
      <img src="{{ asset('assets/img/' . $basicInfo->preloader) }}" alt="">
    </div>
  @endif

  <div class="request-loader">
    <img src="{{ asset('assets/img/' . $basicInfo->preloader) }}" alt="">
  </div>
  <!-- Preloader end -->

  <!-- Header-area start -->
  @if ($basicInfo->theme_version == 1)
    @includeIf('frontend.partials.header.header-v1')
  @elseif ($basicInfo->theme_version == 2)
    @includeIf('frontend.partials.header.header-v2')
  @elseif ($basicInfo->theme_version == 3)
    @includeIf('frontend.partials.header.header-v3')
  @endif
  <!-- Header-area end -->

  @yield('content')
  <!-- Go to Top -->
  <div class="go-top"><i class="fal fa-angle-up"></i></div>
  <!-- Go to Top -->


  @include('frontend.partials.footer')

  <!-- Go to Top -->
  <div class="go-top"><i class="fal fa-angle-up"></i></div>
  <!-- Go to Top -->

  @includeIf('frontend.partials.popups')
  {{-- cookie alert --}}
  @if (!is_null($cookieAlertInfo) && $cookieAlertInfo->cookie_alert_status == 1)
    @include('cookie-consent::index')
  @endif

  <!-- WhatsApp Chat Button -->
  <div id="WAButton" class="whatsapp-btn-1"></div>
  <!-- WhatsApp Chat Button END-->


  @include('frontend.partials.scripts')
</body>

</html>
