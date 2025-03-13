@extends('frontend.layout')

@section('pageHeading')
  {{ !empty($pageHeading) ? $pageHeading->faq_page_title : __('FAQ') }}
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_faq }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_faq }}
  @endif
@endsection

@section('content')


  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->faq_page_title : __('FAQ'),
  ])
  <!-- Page title end-->

  <!-- Faq-area start -->
  <div class="faq-area pt-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8" data-aos="fade-up">
          <div class="accordion pb-75" id="faqAccordion">
            @if (count($faqs) == 0)
              <div class="p-3 text-center bg-light radius-md">
                <h6 class="mb-0">{{ __('NO FAQ FOUND') }}</h6>
              </div>
            @else
              @foreach ($faqs as $faq)
                <div class="accordion-item mb-30">
                  <h6 class="accordion-header" id="heading_{{ $faq->id }}">
                    <button class="accordion-button {{ $loop->iteration == 1 ? '' : 'collapsed' }}" type="button"
                      data-bs-toggle="collapse" data-bs-target="#collapse_{{ $faq->id }}" aria-expanded="true"
                      aria-controls="collapse_{{ $faq->id }}"> {{ $faq->question }}
                    </button>
                  </h6>
                  <div id="collapse_{{ $faq->id }}"
                    class="accordion-collapse collapse {{ $loop->iteration == 1 ? 'show' : '' }}"
                    aria-labelledby="heading_{{ $faq->id }}" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                      <p> {{ $faq->answer }}</p>
                    </div>
                  </div>
                </div>
              @endforeach
            @endif
          </div>

          @if (!empty(showAd(3)))
            <div class="text-center mt-4">
              {!! showAd(3) !!}
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>
  <!-- Faq-area end -->
@endsection
