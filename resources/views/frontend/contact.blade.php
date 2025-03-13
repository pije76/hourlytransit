@extends('frontend.layout')

@section('pageHeading')
  {{ !empty($pageHeading) ? $pageHeading->contact_page_title : __('Contact') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_contact }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_contact }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->contact_page_title : __('Contact'),
  ])
  <!-- Page title end-->
  <!--============== Start Contact Section ===============-->

  <!-- Contact-area start -->
  <div class="contact-area pt-100 pb-60">
    <div class="container">
      <div class="contact-info row justify-content-center">
        <div class="col-lg-4 col-md-6 item">
          <div class="card text-center shadow-md radius-md mb-30" data-aos="fade-up">
            <div class="icon bg-primary-light mx-auto radius-sm">
              <i class="fal fa-phone-plus"></i>
            </div>
            <div class="card-text mt-20">
              <span class="mb-15 d-inline-block">{{ __('MOBILE') }}</span>
              <span class="h6 mb-10"><a href="{{ $info->contact_number }}" target="_self"
                  title="{{ $info->contact_number }}">{{ $info->contact_number }}</a></span>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 item">
          <div class="card text-center shadow-md radius-md mb-30" data-aos="fade-up">
            <div class="icon bg-primary-light mx-auto radius-sm">
              <i class="fal fa-envelope"></i>
            </div>
            <div class="card-text mt-20">
              <span class="mb-15 d-inline-block">{{ __('EMAIL') }}</span>
              <span class="h6 mb-10"><a href="{{ $info->email_address }}" target="_self"
                  title="{{ $info->email_address }}">{{ $info->email_address }}</a></span>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 item">
          <div class="card text-center shadow-md radius-md mb-30" data-aos="fade-up">
            <div class="icon bg-primary-light mx-auto radius-sm">
              <i class="fal fa-map-marker-alt"></i>
            </div>
            <div class="card-text mt-20">
              <span class="mb-15 d-inline-block">{{ $info->address }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Spacer -->
      <div class="pb-70"></div>

      <div class="row gx-xl-5">
        <div class="col-lg-6 mb-40" data-aos="fade-up">
          <form id="contactForm" action="{{ route('contact.send_mail') }}" method="post">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-20">
                  <input type="text" name="name"value="{{ old('name') }}" class="form-control" id="name"
                    placeholder="{{ __('Enter Your Full Name') }}" />
                  @error('name')
                    <div class="help-block with-errors text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-20">
                  <input type="email" name="email" class="form-control" id="email" required
                    data-error="Enter your email" value="{{ old('email') }}"
                    placeholder="{{ __('Enter Your Email') }}" />
                  @error('email')
                    <div class="help-block with-errors text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group mb-20">
                  <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" id=""
                    required placeholder="{{ __('Enter Email Subject') }}" />
                  @error('subject')
                    <div class="help-block with-errors text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group mb-20">
                  <textarea name="message" id="message"value="{{ old('message') }}" class="form-control" cols="30" rows="8"
                    required placeholder="{{ __('Write Your Message') }}"></textarea>
                  @error('message')
                    <div class="help-block with-errors text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              @if ($info->google_recaptcha_status == 1)
                <div class="col-md-12">
                  <div class="form-group mb-20">
                    {!! NoCaptcha::renderJs() !!}
                    {!! NoCaptcha::display() !!}
                    @error('g-recaptcha-response')
                      <div class="help-block with-errors text-danger">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              @endif

              <div class="col-md-12">
                <button class="btn btn-lg btn-primary radius-sm" type="submit" aria-label="Send Message">
                  {{ __('Send Message') }}</button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-lg-6 mb-40" data-aos="fade-up">
          <div class="map h-100 overflow-hidden radius-md">
            @if (!empty($info->latitude) && !empty($info->longitude))
              <iframe width="100%" height="450" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                src="https://maps.google.com/maps?q={{ $info->latitude }},{{ $info->longitude }}+({{ urlencode($websiteInfo->website_title) }})&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
              </iframe>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Contact-area end -->

  @if (!empty(showAd(3)))
    <div class="text-center">
      {!! showAd(3) !!}
    </div>
  @endif
  </div>
  <!--============ End Contact Section =============-->
@endsection
