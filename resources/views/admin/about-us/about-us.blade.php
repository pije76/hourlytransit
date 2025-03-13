@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('About') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Pages') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('About Us') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('About') }}</a>
      </li>
    </ul>
  </div>


  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-8">
              <div class="card-title">{{ __('About') }}</div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 ">
              <div class="card">
                <div class="card-body">
                  <form id="ajaxForm2" action="{{ route('admin.pages.about_us.update_image') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="">{{ __('Image') . '*' }}</label>
                        <br>
                        <div class="thumb-preview">
                          @if (@$images->about_section_image != null)
                            <img src="{{ asset('assets/img/homepage/' . $images->about_section_image) }}" alt="..."
                              class="uploaded-img">
                          @else
                            <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..." class="uploaded-img">
                          @endif
                        </div>

                        <div class="mt-3">
                          <div role="button" class="btn btn-primary btn-sm upload-btn">
                            {{ __('Choose Image') }}
                            <input type="file" class="img-input" name="about_section_image">
                          </div>
                        </div>
                        <p id="err_about_section_image" class="mt-2 mb-0 text-danger em"></p>
                      </div>
                    </div>

                    <!-- Counter Image -->
                    @if ($settings->theme_version == 3)
                      <div class="col-lg-12">
                        <div class="form-group">
                          <label for="">{{ __('Counter Image') . '*' }}</label>
                          <br>
                          <div class="thumb-preview">
                            @if (!empty($images->counter_section_image))
                              <img src="{{ asset('assets/img/homepage/' . $images->counter_section_image) }}"
                                alt="image" class="uploaded-img3">
                            @else
                              <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..." class="uploaded-img3">
                            @endif
                          </div>
                          <div class="mt-3">
                            <div role="button" class="btn btn-primary btn-sm upload-btn">
                              {{ __('Choose Image') }}
                              <input type="file" class="img-input3" name="counter_section_image">
                            </div>
                          </div>
                          <p id="err_counter_section_image" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                      </div>
                    @endif
                  </form>
                </div>
                <div class="card-footer">
                  <div class="row">
                    <div class="col-12 text-center">
                      <button id="submitBtn2" type="button" class="btn btn-success">
                        {{ __('Update') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-8">

              <div class="card">
                <div class="card-body">
                  <form id="ajaxForm"
                    action="{{ route('admin.pages.about_us.update', ['language' => request()->input('language')]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="title">{{ __('Title') }}</label>
                          <input type="text" value="{{ @$data->title }}" class="form-control" name="title"
                            placeholder="{{ __('Enter Title') }}">
                          <p id="err_title" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="subtitle">{{ __('Subtitle') }}</label>
                          <input type="text" value="{{ @$data->subtitle }}" class="form-control" name="subtitle"
                            placeholder="{{ __('Enter Subtitle') }}">
                          <p id="err_subtitle" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="button_text">{{ __('Button Text') }}</label>
                          <input type="text" value="{{ @$data->button_text }}" class="form-control"
                            name="button_text" placeholder="{{ __('Enter button text') }}">
                          <p id="err_button_text" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="button_url">{{ __('Button Url') }}</label>
                          <input type="text" value="{{ @$data->button_url }}" class="form-control"
                            name="button_url" placeholder="{{ __('Enter button url') }}">
                          <p id="err_button_url" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                      </div>
                      @if ($settings->theme_version == 3)
                        <div class="col-lg-12">
                          <div class="form-group">
                            <label for="">{{ __('Counter Section Video Link') }}</label>
                            <input type="text" class="form-control" name="counter_section_video_link"
                              value="{{ empty($counterdata->counter_section_video_link) ? '' : $counterdata->counter_section_video_link }}"
                              placeholder="{{ __('Enter counter section video link') }}">
                            <p id="err_hero_section_title" class="mt-2 mb-0 text-danger em"></p>
                          </div>
                        </div>
                      @endif
                    </div>

                    <div class="form-group">
                      <label for="text">{{ __('Text') }}</label>
                      <textarea class="form-control summernote" name="text" data-height="300">{{ @$data->text }}</textarea>
                    </div>
                  </form>
                </div>
                <div class="card-footer">
                  <div class="row">
                    <div class="col-12 text-center">
                      <button id="submitBtn" type="button" class="btn btn-success">
                        {{ __('Update') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endsection
