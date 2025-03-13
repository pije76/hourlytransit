<!-- Footer-area start -->
<footer class="footer-area footer-area_v1 bg-img bg-cover"
  data-bg-img="{{ asset('assets/img/' . $basicInfo->footer_background_image) }} ">
  <div class="footer-top pt-100 pb-70">
    <div class="container">
      <div class="row gx-xl-5 justify-content-between">
        <div class="col-xl-4 col-lg-5 col-md-6">
          <div class="footer-widget" data-aos="fade-up">
            <!-- Logo -->
            <div class="logo mb-20">
              @if (!empty($basicInfo->footer_logo))
                <a class="navbar-brand" href="{{ route('index') }}" target="_self" title="{{ __('Link') }}">
                  <img class="lazyload" data-src="{{ asset('assets/img/' . $basicInfo->footer_logo) }}" alt="Logo">
                </a>
              @endif
            </div>
            <p>
              {{ !empty($footerInfo) ? $footerInfo->about_company : '' }}
            </p>
          </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-sm-6">
          <div class="footer-widget" data-aos="fade-up">
            <h4 class="title">{{ __('Quick Links') }}</h4>
            @if (count($quickLinkInfos) == 0)
              <p class="mb-0">{{ __('No Link Found') . '!' }}</p>
            @else
              <ul class="footer-links">
                @foreach ($quickLinkInfos as $quickLinkInfo)
                  <li>
                    <a href="{{ $quickLinkInfo->url }}" target="_self" title="{{ __('Link') }}">{{ $quickLinkInfo->title }}</a>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-sm-6">
          <div class="footer-widget" data-aos="fade-up">
            <h4>{{ __('Contact Us') }}</h4>
            <ul class="info-list">
              <li>
                <i class="fal fa-map-marker-alt"></i>
                @if (!empty($basicInfo->address))
                  <span>{{ $basicInfo->address }}</span>
                @endif
              </li>
              <li>
                <i class="fal fa-phone-plus"></i>
                <a href="tel:{{ $basicInfo->contact_number }}">{{ $basicInfo->contact_number }}</a>
              </li>
              @if (!empty($basicInfo->email_address))
                <li>
                  <i class="fal fa-envelope"></i>
                  <a href="mailto:{{ $basicInfo->email_address }}">{{ $basicInfo->email_address }}</a>
                </li>
              @endif
            </ul>
          </div>
        </div>
        <div class="col-xl-4 col-md-6">
          <div class="footer-widget" data-aos="fade-up">
            <h4 class="title">{{ __('Subscribe Us') }}</h4>
            <p>
              {{ __('Stay update with us and get offer') . '!' }}
            </p>
            <form id="newsletterForm" class="subscription-form" action="{{ route('store_subscriber') }}"
              method="POST">
              @csrf
              <div class="input-inline p-1 bg-white shadow-md radius-sm">
                <input class="form-control border-0" placeholder="{{ __('Enter email here...') }}" type="text"
                  name="email_id" required>
                <button class="btn btn-md btn-primary radius-sm" type="submit"
                  aria-label="button">{{ __('Subscribe') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="copy-right-area py-4">
    <div class="container">
      <div class="copy-right-content">
        @if (count($socialMediaInfos) > 0)
          <div class="social-link rounded justify-content-center mb-10">
            @foreach ($socialMediaInfos as $socialMediaInfo)
              <a href="{{ $socialMediaInfo->url }}" target="_blank" title="{{ __('Link') }}"><i
                  class="{{ $socialMediaInfo->icon }}"></i></a>
            @endforeach
          </div>
        @endif
        <span>
          @isset($footerInfo->copyright_text)
            {!! @$footerInfo->copyright_text !!}
          @else
            {{ __('Copyright ©2024. All Rights Reserved.') }}
          @endisset
        </span>
      </div>
    </div>
  </div>
</footer>
<!-- Footer-area end-->
