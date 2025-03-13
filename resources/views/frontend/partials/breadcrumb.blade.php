<!-- Breadcrumb start -->
<div class="breadcrumb-area bg-img bg-cover z-1 header-next" data-bg-img="{{ asset('assets/img/' . $breadcrumb) }}">
  <div class="overlay opacity-75"></div>
  <div class="container">
    <div class="content text-center">
      <h2 class="color-white">{{ !empty($title) ? $title : '' }}</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('index') }}">{{ __('Home') }}</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ !empty($title) ? $title : '' }}</li>
        </ol>
      </nav>
    </div>
  </div>
</div>
<!-- Breadcrumb end -->
