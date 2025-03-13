@extends('frontend.layout')
@section('pageHeading')
  {{ !empty($pageHeading) ? $pageHeading->about_us_title : __('About Us') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keywords_about_page }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_about_page }}   
  @endif
@endsection

@section('content')
  <!-- Breadcrumb start -->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->about_us_title : __('About Us'),
  ])
  <!-- Breadcrumb end -->

  <!-- About-area start -->
  @if ($secInfo->about_section_status == 1)
    <section class="about-area pt-100 pb-60">
      <div class="container">
        <div class="row align-items-center gx-xl-5">
          <div class="col-lg-6">
            <div class="image img-left mb-40">
              <img class="blur-up lazyload" src="{{ asset('assets/img/homepage/' . $images->about_section_image) }}"
                alt="Image">
            </div>
          </div>
          <div class="col-lg-6">
            <div class="content-title mb-40">
              <span class="subtitle">{{ @$about->title }}</span>
              <h2 class="title mb-20 mt-0">
                {{ @$about->subtitle }}
              </h2>
              <p>
                {!! @$about->text !!}
              </p>
              @if (!empty($about->button_url))
                <a href="{{ $about->button_url }}"
                  class="btn btn-lg btn-primary icon-start ">{{ $about->button_text }}</a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- About-area end -->

  @if (count($after_about) > 0)
    @foreach ($after_about as $customAbout)
      @if (isset($aboutSec[$customAbout->id]))
        @if ($aboutSec[$customAbout->id] == 1)
          @php
            $afAboutCon = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $customAbout->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          <section class="custom-section-area pt-100 pb-60 aaa">
            <div class="container">
              <div class="section-title title-center mb-50" data-aos="fade-up">
                <h2 class="title mb-0">
                  {{ @$afAboutCon->section_name }}
                </h2>
              </div>
              <div class="row align-items-center gx-xl-5">
                {!! @$afAboutCon->content !!}
              </div>
            </div>
          </section>
        @endif
      @endif
    @endforeach
  @endif

  <!-- Feature-area start -->
  @if ($secInfo->about_features_section_status == 1)
    @if ($themeVersion == 1)
      <section class="about-area about-area_v1 pb-60">
        <div class="container-fluid  px-lg-0">
          <div class="row align-items-center gx-xl-5" data-aos="fade-up">
            <div class="col-lg-6">
              <div class="image mb-40">
                <img class="lazyload blur-up"
                  data-src="{{ asset('assets/img/homepage/' . $images->feature_section_image) }}" alt="Image">
              </div>
            </div>
            <div class="col-lg-6">
              <div class="content-title fluid-right pb-20">
                <h2 class="title mb-20">
                  {{ $sectionContent ? $sectionContent->featured_section_title : __('Your Ultimate Hourly Hotel Booking Solution') }}
                </h2>
                <p>
                  {{ $sectionContent ? $sectionContent->featured_section_text : '' }}
                </p>
                <div class="info-list mt-30">
                  @foreach ($features as $feature)
                    <div class="card mb-20">
                      <div class="card_icon">
                        <img class="lazyload" data-src="{{ asset('assets/img/feature/' . $feature->image) }}"
                          alt="Image">
                      </div>
                      <div class="card_content">
                        <span class="h3 lh-1 mb-1">{{ $feature->title }}</span>
                        <p class="card_text">{{ $feature->subtitle }}</p>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    @elseif ($themeVersion == 2)
      <section class="about-area about-area_v2 pb-60">
        <div class="container-fluid ps-0">
          <div class="row align-items-center gx-xl-5" data-aos="fade-up">
            <div class="col-lg-6">
              <div class="content-title fluid-left pb-20">
                <h2 class="title mb-20">
                  {{ $sectionContent ? $sectionContent->featured_section_title : __('Your Ultimate Hourly Hotel Booking Solution') }}
                </h2>
                <p>
                  {{ $sectionContent ? $sectionContent->featured_section_text : '' }}
                </p>
                <div class="info-list mt-40">
                  <div class="row">
                    @foreach ($features as $feature)
                      <div class="col-sm-6">
                        <div class="card mb-20 p-20 radius-md bg-primary-light">
                          <div class="card_top">
                            <div class="card_icon bg-white radius-sm">
                              <img class="lazyload" data-src="{{ asset('assets/img/feature/' . $feature->image) }}"
                                alt="Image">
                            </div>
                            <span class="h3 lh-1 mb-1">{{ $feature->amount }}+</span>
                          </div>
                          <div class="card_content mt-20">
                            <p class="card_text">{{ $feature->title }}</p>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="image mb-40">
                <img class="lazyload blur-up"
                  data-src="{{ asset('assets/img/homepage/' . $images->feature_section_image) }}" alt="Image">
              </div>
            </div>
          </div>
        </div>
      </section>
    @else ($themeVersion == 3)
      <section class="about-area about-area_v3 bg-primary-light ptb-100">
        <div class="container-fluid">
          <div class="row align-items-center gx-xl-5" data-aos="fade-up">
            <div class="col-lg-5">
              <div class="content-title fluid-left ptb-70">
                <h2 class="title mb-20">
                  {{ $sectionContent ? $sectionContent->featured_section_title : __('Your Ultimate Hourly Hotel Booking Solution') }}
                </h2>
                <div class="info-list pt-10">
                  @foreach ($features as $feature)
                    <div class="card mt-20">
                      <div class="card_icon">
                        <img class="lazyload" data-src="{{ asset('assets/img/feature/' . $feature->image) }}"
                          alt="Image">
                      </div>
                      <div class="card_content">
                        <span class="h4 lh-1 mb-1">{{ $feature->title }}</span>
                        <p class="card_text">{{ $feature->subtitle }}</p>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="image">
                <img class="lazyload blur-up"
                  data-src="{{ asset('assets/img/homepage/' . $images->feature_section_image) }}" alt="Image">
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif


  @endif
  <!-- Feature-area end -->

  @if (count($after_features) > 0)
    @foreach ($after_features as $Cufeatures)
      @if (isset($aboutSec[$Cufeatures->id]))
        @if ($aboutSec[$Cufeatures->id] == 1)
          @php
            $cuFeatures = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $Cufeatures->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          <section class="custom-section-area pt-100 pb-60">
            <div class="container">
              <div class="section-title title-center mb-50" data-aos="fade-up">
                <h2 class="title mb-0">
                  {{ @$cuFeatures->section_name }}
                </h2>
              </div>
              <div class="row align-items-center gx-xl-5">
                {!! @$cuFeatures->content !!}
              </div>
            </div>
          </section>
        @endif
      @endif
    @endforeach
  @endif

  <!-- Counter-area start -->
  @if ($secInfo->about_counter_section_status == 1)

    @if ($themeVersion == 1)
      <div class="counter-area counter-area_v1 py-4 bg-img bg-cover z-1"
        data-bg-img="{{ asset('assets/img/homepage/' . $images->counter_section_image) }}">
        <div class="overlay opacity-60"></div>
        <div class="container">
          <div class="row">
            <div class="col-lg-6">
              <div class="row" data-aos="fade-up">
                @foreach ($counters as $counter)
                  <div class="col-sm-6 item">
                    <div class="card radius-md border text-center p-30">
                      <div class="card_icon mb-20">
                        <img class="lazyload" data-src="{{ asset('assets/img/counter/' . $counter->image) }}"
                          alt="Image">
                      </div>
                      <div class="card_content">
                        <h2 class="card_title mb-15">
                          <span class="counter">{{ $counter->amount }}</span>+
                        </h2>
                        <p class="card_text font-lg lh-1">
                          {{ $counter->title }}
                        </p>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-lg-6">
              <div class="video-btn-parent z-2 h-100">
                @if (@$sectionContent->counter_section_video_link)
                  <a href="{{ $sectionContent->counter_section_video_link }}"
                    class="video-btn video-btn-white youtube-popup" title="{{ __('Play Video') }}">
                    <i class="fas fa-play"></i>
                  </a>
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
    @elseif ($themeVersion == 2)
      <div class="counter-area counter-area_v2 pb-70" data-aos="fade-up">
        <div class="counter-inner bg-img bg-cover z-1"
          data-bg-img="{{ asset('assets/img/homepage/' . $images->counter_section_image) }}">
          <div class="overlay opacity-60"></div>
          <div class="video-btn-parent z-2 h-100">
            @if (@$sectionContent->counter_section_video_link)
              <a href="{{ $sectionContent->counter_section_video_link }}"
                class="video-btn video-btn-white youtube-popup mx-auto" title="{{ __('Play Video') }}">
                <i class="fas fa-play"></i>
              </a>
            @endif
          </div>
          <!-- Shapes -->
          <div class="shapes shapes-1">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <div class="shapes shapes-2">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
        <div class="counter-blocks">
          <div class="container">
            <div class="row">
              @foreach ($counters as $counter)
                <div class="col-sm-6 col-lg-3 item">
                  <div class="card radius-md border text-center p-30 mb-30">
                    <div class="card_icon mb-20">
                      <img class="lazyload" data-src="{{ asset('assets/img/counter/' . $counter->image) }}"
                        alt="Image">
                    </div>
                    <div class="card_content">
                      <h2 class="card_title mb-15">
                        <span class="counter">{{ $counter->amount }}</span>+
                      </h2>
                      <p class="card_text font-lg lh-1">
                        {{ $counter->title }}
                      </p>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @elseif ($themeVersion == 3)
      <div class="counter-area counter-area_v1 py-4 bg-img bg-cover z-1"
        data-bg-img="{{ asset('assets/img/homepage/' . $images->counter_section_image) }}">
        <div class="overlay opacity-60"></div>
        <div class="container">
          <div class="row">
            <div class="col-lg-6">
              <div class="row" data-aos="fade-up">
                @foreach ($counters as $counter)
                  <div class="col-sm-6 item">
                    <div class="card radius-md border text-center p-30">
                      <div class="card_icon mb-20">
                        <img class="lazyload" data-src="{{ asset('assets/img/counter/' . $counter->image) }}"
                          alt="Image">
                      </div>
                      <div class="card_content">
                        <h2 class="card_title mb-15">
                          <span class="counter">{{ $counter->amount }}</span>+
                        </h2>
                        <p class="card_text font-lg lh-1">
                          {{ $counter->title }}
                        </p>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-lg-6">
              <div class="video-btn-parent z-2 h-100">
                @if (@$sectionContent->counter_section_video_link)
                  <a href="{{ $sectionContent->counter_section_video_link }}"
                    class="video-btn video-btn-white youtube-popup" title="{{ __('Play Video') }}">
                    <i class="fas fa-play"></i>
                  </a>
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endif
  <!-- Counter-area end -->

  @if (count($after_counter) > 0)
    @foreach ($after_counter as $acounter)
      @if (isset($aboutSec[$acounter->id]))
        @if ($aboutSec[$acounter->id] == 1)
          @php
            $cuWorkProcess = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $acounter->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          <section class="custom-section-area pt-100 pb-60">
            <div class="container">
              <div class="section-title title-center mb-50" data-aos="fade-up">
                <h2 class="title mb-0">
                  {{ @$cuWorkProcess->section_name }}
                </h2>
              </div>
              <div class="row align-items-center gx-xl-5">
                {!! @$cuWorkProcess->content !!}
              </div>
            </div>
          </section>
        @endif
      @endif
    @endforeach
  @endif

  <!-- Testimonial-area start -->
  @if ($secInfo->about_testimonial_section_status == 1)
    @if ($themeVersion == 1)
      <section class="testimonial-area testimonial-area_v1 ptb-100" data-aos="fade-up">
        <div class="container">
          <div class="wrapper">
            <div class="row">
              <div class="col-lg-12">
                <div class="section-title title-center mb-50">
                  <h2 class="title">
                    {{ !empty($sectionContent->testimonial_section_title) ? $sectionContent->testimonial_section_title : '' }}
                  </h2>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="row justify-content-center">
                  <div class="col-lg-6">
                    <div class="swiper" id="testimonial-slider-1">
                      <div class="swiper-wrapper">
                        @foreach ($testimonials as $testimonial)
                          <div class="swiper-slide">
                            <div class="slider-item text-center">
                              <div class="client-img mb-25 mx-auto">
                                <div class="lazy-container ratio ratio-1-1">
                                  <img class="lazyload"
                                    data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                                    alt="Person Image">
                                </div>
                              </div>
                              <div class="quote mb-20">
                                <p class="text">
                                  {{ $testimonial->comment }}
                                </p>
                              </div>
                              <div class="client-info">
                                <h6 class="name">{{ $testimonial->name }}</h6>
                                <span class="designation">{{ $testimonial->occupation }}</span>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                      <div class="swiper-pagination position-static mt-30" id="testimonial-slider-1-pagination"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="avatar">
              @foreach ($testimonials as $testimonial)
                <img class="lazyload avatar-1" data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                  alt="Person Image">
              @endforeach
            </div>
          </div>
        </div>
        <!-- Bg shape -->
        <div class="bg-shape">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </section>
    @elseif ($themeVersion == 2)
      <section class="testimonial-area testimonial-area_v2 pb-60" data-aos="fade-up">
        <div class="container">
          <div class="row flex-lg-nowrap gx-xl-5 align-items-center">
            <div class="col-lg-5">
              <div class="content-title mb-40 mb-lg-0">
                <h2 class="title mb-25">
                  {{ !empty($sectionContent->testimonial_section_title) ? $sectionContent->testimonial_section_title : '' }}
                </h2>
                <p class="text">
                  {{ !empty($sectionContent->testimonial_section_subtitle) ? $sectionContent->testimonial_section_subtitle : '' }}
                </p>
                <div class="clients-avatar mt-30">
                  <div class="client-img">
                    @foreach ($testimonials as $testimonial)
                      <img class="blur-up lazyload" src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                        alt="Client Image">
                    @endforeach
                    <span>
                      {{ !empty($sectionContent->testimonial_section_clients) ? $sectionContent->testimonial_section_clients : '' }}</span>
                  </div>
                </div>
                <!-- Slider navigation buttons -->
                <div class="slider-navigation mt-30">
                  <button type="button" title="{{ __('Slide prev') }}" class="slider-btn rounded-circle btn-outline"
                    id="testimonial-slider-2-prev">
                    <i class="fal fa-angle-left"></i>
                  </button>
                  <button type="button" title="{{ __('Slide next') }}" class="slider-btn rounded-circle btn-outline"
                    id="testimonial-slider-2-next">
                    <i class="fal fa-angle-right"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-10">
              <div class="swiper mb-40" id="testimonial-slider-2">
                <div class="swiper-wrapper row">

                  @foreach ($testimonials as $testimonial)
                    <div class="swiper-slide col-lg-6">
                      <div class="slider-item border radius-md">
                        <div class="client mb-25">
                          <div class="client-info d-flex align-items-center">
                            <div class="client-img">
                              <div class="lazy-container rounded-pill ratio ratio-1-1">
                                <img class="lazyload"
                                  data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                                  alt="Person Image">
                              </div>
                            </div>
                            <div class="content">
                              <h6 class="name">{{ $testimonial->name }}</h6>
                              <span class="designation">{{ $testimonial->occupation }}</span>
                            </div>
                          </div>
                          <span class="icon"><i class="fal fa-quote-right"></i></span>
                        </div>
                        <div class="quote">
                          <p class="text mb-0">
                            {{ $testimonial->comment }}
                          </p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    @else
      <section class="pt-100">
        <div class="testimonial-area testimonial-area_v3 ptb-100 bg-img bg-cover"data-bg-img="{{ asset('assets/img/homepage/' . $images->testimonial_section_image) }}">
          <div class="overlay opacity-80"></div>
          <div class="container">
            <div class="wrapper">
              <div class="row justify-content-center">
                <div class="col-lg-10">
                  <div class="swiper" id="testimonial-slider-1">
                    <div class="swiper-wrapper">
                      @foreach ($testimonials as $testimonial)
                        <div class="swiper-slide">
                          <div class="slider-item text-center">
                            <div class="quote mb-20">
                              <p class="text color-white">
                                {{ $testimonial->comment }}
                              </p>
                            </div>
                            <div class="client-img mb-20 mx-auto">
                              <div class="lazy-container rounded-circle ratio ratio-1-1">
                                <img class="lazyload" data-src="{{ asset('assets/img/clients/' . $testimonial->image) }}"
                                  alt="Person Image">
                              </div>
                            </div>
                            <div class="client-info">
                              <h6 class="name color-white">{{ $testimonial->name }}</h6>
                              <span class="designation color-light">{{ $testimonial->occupation }}</span>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                    <div class="swiper-pagination swiper-pagination_white position-static mt-20"
                      id="testimonial-slider-1-pagination"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif
  @endif
  <!-- Testimonial-area end -->

  @if (count($after_testimonial) > 0)
    @foreach ($after_testimonial as $Cutestimonial)
      @if (isset($aboutSec[$Cutestimonial->id]))
        @if ($aboutSec[$Cutestimonial->id] == 1)
          @php
            $cuTestimonial = App\Models\HomePage\CustomSectionContent::where('custom_section_id', $Cutestimonial->id)
                ->where('language_id', $currentLanguageInfo->id)
                ->first();
          @endphp
          <section class="custom-section-area pt-100 pb-60">
            <div class="container">
              <div class="section-title title-center mb-50" data-aos="fade-up">
                <h2 class="title mb-0">
                  {{ @$cuTestimonial->section_name }}
                </h2>
              </div>
              <div class="row align-items-center gx-xl-5">
                {!! @$cuTestimonial->content !!}
              </div>
            </div>
          </section>
        @endif
      @endif
    @endforeach
  @endif
@endsection
