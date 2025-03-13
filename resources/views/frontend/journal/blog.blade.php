@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->blog_page_title }}
  @else
    {{ __('Posts') }}
  @endif
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_blog }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_blog }}
  @endif
@endsection
@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->blog_page_title : __('Blog'),
  ])
  <!-- Page title end-->
  <div class="blog-area blog-area_v1 pt-70 pb-60">
    <div class="container">
      <div class="row">
        <div class="col-lg-9">
          <div class="row pb-10" data-aos="fade-up">
            @if (count($blogs) > 0)
              @foreach ($blogs as $blog)
                <div class="col-md-6">
                  <article class="card border radius-md mt-30">
                    <div class="card_top mb-30">
                      <div class="card_img">
                        <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}" target="_self"
                          title="{{ __('Link') }}" class="lazy-container radius-sm ratio ratio-2-3">
                          <img class="lazyload" src="{{ asset('assets/img/blogs/' . $blog->image) }}"
                            data-src="{{ asset('assets/img/blogs/' . $blog->image) }}" alt="Blog Image">
                        </a>
                      </div>
                    </div>
                    <div class="card_content px-20">
                      <h4 class="card_title lc-2 mb-15">
                        <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}" target="_self"
                          title="{{ __('Link') }}">
                          {{ @$blog->title }}
                        </a>

                      </h4>
                      <p class="card_text lc-2">
                        {{ strlen(strip_tags(convertUtf8($blog->content))) > 100 ? substr(strip_tags(convertUtf8($blog->content)), 0, 100) . '...' : strip_tags(convertUtf8($blog->content)) }}
                      </p>
                      <div class="cta-btn mt-20">
                        <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}"
                          class="btn btn-lg btn-secondary radius-sm shadow-md icon-end"
                          title="{{ strlen(strip_tags($blog->content)) > 10 ? mb_substr(strip_tags($blog->content), 0, 10, 'UTF-8') . '...' : $blog->content }}"
                          target="_self">
                          <span>{{ __('Read More') }}</span>
                          <i
                            class="fal {{ $currentLanguageInfo->direction == 1 ? 'fa-long-arrow-left' : 'fa-long-arrow-right' }}"></i>
                        </a>
                      </div>
                    </div>
                  </article>
                </div>
              @endforeach
            @else
              <div class="p-3 text-center bg-light radius-md">
                <h6 class="mb-0">{{ __('NO POST FOUND') }}</h6>
              </div>
            @endif

          </div>
          <nav class="pagination-nav mt-20 mb-40" data-aos="fade-up">
            <ul class="pagination justify-content-center">
              {{ $blogs->links() }}
            </ul>
          </nav>
          @if (!empty(showAd(3)))
            <div class="text-center">
              {!! showAd(3) !!}
            </div>
          @endif
        </div>
        <div class="col-xl-3">
          <!-- Spacer -->
          <div class="mt-30 d-none d-lg-block"></div>
          <aside class="widget-area border radius-md px-25" data-aos="fade-up">
            <div class="widget widget-search py-25">
              <h5 class="title mb-15">{{ __('Search Posts') }}</h5>
              <div class="search-form"action="{{ route('blog') }}" method="GET">
                <form id="searchForm">
                  <div class="input-inline bg-white shadow-md rounded-pill">
                    <input class="form-control border-0" placeholder="{{ __('Search here') . '...' }}" type="text"
                      value="{{ request()->input('SEARCH') }}" name="SEARCH" required="">
                    <button class="btn-icon rounded-pill" type="submit" aria-label="Search button">
                      <i class="far fa-search"></i>
                    </button>
                  </div>
                </form>
              </div>
            </div>
            <div class="widget widget-blog-categories py-25">
              <h5 class="title">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#blogCategory">
                  {{ __('Categories') }}
                </button>
              </h5>
              <div id="blogCategory" class="collapse show">
                <div class="accordion-body mt-20 scroll-y">
                  <ul class="list-unstyled m-0">
                    @foreach ($categories as $category)
                      <li class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('blog', ['category' => $category->slug]) }}"><i class="fal fa-folder"></i>
                          {{ $category->name }}</a>
                        <span class="tqy">({{ $category->blogCount }})</span>
                      </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
            @if (!empty(showAd(1)))
              <div class="text-center">
                {!! showAd(1) !!}
              </div>
            @endif
          </aside>
          <!-- Spacer -->
          <div class="pb-40"></div>
        </div>
      </div>
    </div>
  </div>
@endsection
