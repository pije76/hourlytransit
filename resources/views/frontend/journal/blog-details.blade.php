@extends('frontend.layout')

@php
  $title = strlen($details->title) > 40 ? mb_substr($details->title, 0, 40, 'UTF-8') . '...' : $details->title;
@endphp
@section('pageHeading')
  @if (!empty($title))
    {{ $title ? $title : $pageHeading->blog_page_title }}
  @endif
@endsection

@section('metaKeywords')
  {{ $details->meta_keywords }}
@endsection

@section('metaDescription')
  {{ $details->meta_description }}
@endsection

@section('ogTitle')
  @if (!empty($details))
    {{ $details->title }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->blog_page_title : $details->title,
  ])
  <!-- Page title end-->

  <!-- Blog-details-area start -->
  <div class="blog-details-area pt-100 pb-60">
    <div class="container">
      <div class="row justify-content-center gx-xl-5">
        <div class="col-lg-8">
          <div class="blog-description mb-40">
            <article class="item-single">
              <div class="image radius-md">
                <div class="lazy-container ratio ratio-16-9">
                  <img class="lazyload" src="assets/images/placeholder.png"
                    data-src="{{ asset('assets/img/blogs/' . $details->image) }}" alt="Blog Image">
                </div>
                <a href="#" class="btn btn-md btn-primary"data-bs-toggle="modal"
                  data-bs-target="#socialMediaModal"><i class="fas fa-share-alt"></i>{{ __('Share Now') }}</a>
              </div>
              <div class="content">
                <ul class="info-list">

                  <li><i class="fal fa-user"></i>{{ __('Admin') }}</li>
                  <li><i class="fal fa-calendar"></i>{{ date_format($details->created_at, 'M d, Y') }}</li>
                  <li><i class="fal fa-tag"></i>{{ $details->categoryName }}</li>

                </ul>
                <h3 class="title">
                  {{ $details->title }}
                </h3>
                <p>
                  {!! replaceBaseUrl($details->content, 'summernote') !!}

                </p>
              </div>
            </article>
          </div>

        </div>
        <div class="col-lg-4">
          <aside class="widget-area border radius-md px-25 mb-10">
            <div class="widget widget-search py-25 mb-30">
              <h4 class="title mb-15">{{ __('Search Posts') }}</h4>
              <div class="search-form">
                <form class="search-form radius-md" action="{{ route('blog') }}" method="GET">
                  <div class="input-inline bg-white shadow-md rounded-pill">
                    <input type="search" class="form-control border-0"placeholder="{{ __('Search By Title') }}"
                      name="title" value="{{ !empty(request()->input('title')) ? request()->input('title') : '' }}">
                    @if (!empty(request()->input('category')))
                      <input type="hidden" name="category" value="{{ request()->input('category') }}">
                    @endif
                    <button class="btn-icon rounded-pill" type="submit">
                      <i class="far fa-search"></i>
                    </button>
                  </div>
                </form>
              </div>

            </div>
            <div class="widget widget-blog-categories radius-md pb-25 mb-30">
              <h3 class="title mb-15">{{ __('Categories') }}</h3>
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

            <div class="widget widget-post radius-md pb-25 mb-30">
              <h4 class="title mb-15">{{ __('Recent Posts') }}</h4>

              @foreach ($recent_blogs as $blog)
                <article class="article-item mb-30">
                  <div class="image">
                    <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}"
                      class="lazy-container ratio ratio-1-1">
                      <img class="lazyload" data-src="{{ asset('assets/img/blogs/' . $blog->image) }}" alt="Blog Image">
                    </a>
                  </div>
                  <div class="content">
                    <h6>
                      <a href="{{ route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]) }}">
                        {{ strlen($blog->title) > 40 ? mb_substr($blog->title, 0, 40, 'UTF-8') . '...' : $blog->title }}
                      </a>
                    </h6>
                    <ul class="info-list">
                      <li><i class="fal fa-user"></i>{{ __('Admin') }}</li>
                      <li><i class="fal fa-calendar"></i>{{ date_format($details->created_at, 'M d, Y') }}</li>
                    </ul>
                  </div>
                </article>
              @endforeach
            </div>
            @if (!empty(showAd(2)))
              <div class="text-center mb-30">
                {!! showAd(2) !!}
              </div>
            @endif
            @if (!empty(showAd(1)))
              <div class="text-center mb-30">
                {!! showAd(1) !!}
              </div>
            @endif
          </aside>
        </div>
      </div>
    </div>
  </div>
  <!-- Blog-details-area end -->
  @include('frontend.journal.share')
@endsection
