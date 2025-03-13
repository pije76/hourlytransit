@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Images & Texts') }}</h4>
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
        <a href="#">{{ __('Home Page') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Images & Texts') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">

        <div class="card-header">
          <div class="row">
            <div class="col-lg-12">
              <div class="card-title">{{ __('Images & Texts') }}</div>
            </div>
          </div>
        </div>

        <div class="card-body">

          <div class="row">

            <div class="col-lg-6">
              <div class="card">

                <div class="body">
                  <form id="ajaxForm" action="{{ route('admin.pages.home_page.images_and_texts.images_update') }}"
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <!-- hero section -->
                    @if ($settings->theme_version != 2)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Hero Section') }}</h2>
                        <hr>
                        <div class="col-lg-12">
                          <div class="form-group">
                            <label for="">{{ __('Image') . '*' }}</label>
                            <br>
                            <div class="thumb-preview">
                              @if (!empty($images->hero_section_image))
                                <img src="{{ asset('assets/img/homepage/' . $images->hero_section_image) }}"
                                  alt="image" class="uploaded-img">
                              @else
                                <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..." class="uploaded-img">
                              @endif
                            </div>
                            <div class="mt-3">
                              <div role="button" class="btn btn-primary btn-sm upload-btn">
                                {{ __('Choose Image') }}
                                <input type="file" class="img-input" name="hero_section_image">
                              </div>
                            </div>
                            <p id="err_hero_section_image" class="mt-2 mb-0 text-danger em"></p>
                          </div>
                        </div>
                      </div>
                    @endif

                    <!-- featured  section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('Featured Section') }}</h2>
                      <hr>
                      <div class="col-lg-12">
                        <div class="form-group">
                          <label for="">{{ __('Image') . '*' }}</label>
                          <br>
                          <div class="thumb-preview">
                            @if (!empty($images->feature_section_image))
                              <img src="{{ asset('assets/img/homepage/' . $images->feature_section_image) }}"
                                alt="image" class="uploaded-img2">
                            @else
                              <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..." class="uploaded-img2">
                            @endif
                          </div>
                          <div class="mt-3">
                            <div role="button" class="btn btn-primary btn-sm upload-btn">
                              {{ __('Choose Image') }}
                              <input type="file" class="img-input2" name="feature_section_image">
                            </div>
                          </div>
                          <p id="err_feature_section_image" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                      </div>
                    </div>

                    <!-- Counter section -->
                    @if ($settings->theme_version != 3)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Counter Section') }}</h2>
                        <hr>
                        <div class="col-lg-12">
                          <div class="form-group">
                            <label for="">{{ __('Image') . '*' }}</label>
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
                      </div>
                    @endif

                    <!-- testimonial section -->
                    @if ($settings->theme_version == 3)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Testimonial Section') }}</h2>
                        <hr>
                        <div class="row">
                          <div class="col-lg-12">
                            <div class="form-group">
                              <label for="">{{ __('Image') . '*' }}</label>
                              <br>
                              <div class="thumb-preview">
                                @if (@$images->testimonial_section_image != null)
                                  <img src="{{ asset('assets/img/homepage/' . $images->testimonial_section_image) }}"
                                    alt="..."class="uploaded-img4">
                                @else
                                  <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..."
                                    class="uploaded-img4">
                                @endif
                              </div>
                              <div class="mt-3">
                                <div role="button" class="btn btn-primary btn-sm upload-btn">
                                  {{ __('Choose Image') }}
                                  <input type="file" class="img-input4" name="testimonial_section_image">
                                </div>
                              </div>
                              <p id="err_testimonial_section_image" class="mt-2 mb-0 text-danger em"></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif

                    <!-- call to action section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('Call To Action Section') }}</h2>
                      <hr>
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="row">
                            <div class="col-lg-6">
                              <div class="form-group">
                                <label for="">{{ __('Background Image') . '*' }}</label>
                                <br>
                                <div class="thumb-preview">
                                  @if (!empty($images->call_to_action_section_image))
                                    <img
                                      src="{{ asset('assets/img/homepage/' . $images->call_to_action_section_image) }}"
                                      alt="image" class="uploaded-img5">
                                  @else
                                    <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..."
                                      class="uploaded-img5">
                                  @endif
                                </div>

                                <div class="mt-3">
                                  <div role="button" class="btn btn-primary btn-sm upload-btn">
                                    {{ __('Choose Image') }}
                                    <input type="file" class="img-input5" name="call_to_action_section_image">
                                  </div>
                                </div>
                                <p id="err_call_to_action_section_image" class="mt-2 mb-0 text-danger em"></p>
                              </div>
                            </div>
                            @if ($settings->theme_version == 3)
                              <div class="col-lg-6">
                                <div class="form-group">
                                  <label for="">{{ __('Inner Image') . '*' }}</label>
                                  <br>
                                  <div class="thumb-preview">
                                    @if (!empty($images->call_to_action_section_inner_image))
                                      <img
                                        src="{{ asset('assets/img/homepage/' . $images->call_to_action_section_inner_image) }}"
                                        alt="image" class="uploaded-img6">
                                    @else
                                      <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..."
                                        class="uploaded-img6">
                                    @endif
                                  </div>
                                  <div class="mt-3">
                                    <div role="button" class="btn btn-primary btn-sm upload-btn">
                                      {{ __('Choose Image') }}
                                      <input type="file" class="img-input6"
                                        name="call_to_action_section_inner_image">
                                    </div>
                                  </div>
                                  <p id="err_call_to_action_section_inner_image" class="mt-2 mb-0 text-danger em"></p>
                                </div>
                              </div>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="card-footer">
                  <div class="row">
                    <div class="col-12 text-center">
                      <button id="submitBtn" type="button" class="btn btn-primary"> {{ __('Update') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card">

                <div class="body">
                  <div class="alert alert-danger pb-1 dis-none" id="commonFormErrors">
                    <ul></ul>
                  </div>
                  <form id="commonForm"
                    action="{{ route('admin.pages.home_page.section_content_update', ['language' => request()->input('language')]) }}"
                    method="post" enctype="multipart/form-data">
                    @csrf

                    <!-- hero section -->
                    @if ($settings->theme_version != 2)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Hero Section') }}</h2>
                        <hr>
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label for="">{{ __('Hero Section Title') . '*' }}</label>
                              <input type="text" class="form-control" name="hero_section_title"
                                value="{{ empty($data->hero_section_title) ? '' : $data->hero_section_title }}"
                                placeholder="{{ __('Enter hero section title') }}">
                              <p id="err_hero_section_title" class="mt-2 mb-0 text-danger em"></p>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label for="">{{ __('Hero Section Subtitle') }}</label>
                              <input type="text" class="form-control" name="hero_section_subtitle"
                                value="{{ empty($data->hero_section_subtitle) ? '' : $data->hero_section_subtitle }}"
                                placeholder="{{ __('Enter hero section subtitle') }}">
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                    <!-- city section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('City Section') }}</h2>
                      <hr>
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="form-group">
                            <label for="">{{ __('City Section Title') . '*' }}</label>
                            <input type="text" class="form-control" name="city_section_title"
                              value="{{ empty($data->city_section_title) ? '' : $data->city_section_title }}"
                              placeholder="{{ __('Enter city section title') }}">
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- featured  section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('Featured Section') }}</h2>
                      <hr>
                      <div class="row">
                        <div class="col-lg-{{ $settings->theme_version != 3 ? 6 : 12 }}">
                          <div class="form-group">
                            <label for="">{{ __('Featured Section Title') . '*' }}</label>
                            <input type="text" class="form-control" name="featured_section_title"
                              value="{{ empty($data->featured_section_title) ? '' : $data->featured_section_title }}"
                              placeholder="{{ __('Enter featured service section title') }}">
                          </div>
                        </div>
                        @if ($settings->theme_version != 3)
                          <div class="col-lg-6">
                            <label for="featured_section_text">{{ __('Text') }}</label>
                            <textarea id="featured_section_text" class="form-control" name="featured_section_text"
                              placeholder="{{ __('Enter Text') }}" data-height="300">{{ $data->featured_section_text ?? '' }}</textarea>
                          </div>
                        @endif
                      </div>
                    </div>

                    <!-- featured Room section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('Featured Room Section') }}</h2>
                      <hr>
                      <div class="row">
                        <div class="col-lg-{{ $settings->theme_version == 2 ? 6 : 12 }}">
                          <div class="form-group">
                            <label for="">{{ __('Featured Room Section Title') . '*' }}</label>
                            <input type="text" class="form-control" name="featured_room_section_title"
                              value="{{ empty($data->featured_room_section_title) ? '' : $data->featured_room_section_title }}"
                              placeholder="{{ __('Enter featured service section title') }}">
                          </div>
                        </div>
                        @if ($settings->theme_version == 2)
                          <div class="col-lg-6">
                            <div class="form-group">
                              <label for="">{{ __('Featured Room Section Button Text') }}</label>
                              <input type="text" class="form-control" name="featured_room_section_button_text"
                                value="{{ empty($data->featured_room_section_button_text) ? '' : $data->featured_room_section_button_text }}"
                                placeholder="{{ __('Enter featured room section button text') }}">
                            </div>
                          </div>
                        @endif
                      </div>
                    </div>


                    <!-- work process section -->
                    @if ($settings->theme_version != 3)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Counter Section') }}</h2>
                        <hr>
                        <div class="row">
                          <div class="col-lg-12">
                            <div class="form-group">
                              <label for="">{{ __('Counter Section Video Link') }}</label>
                              <input type="text" class="form-control" name="counter_section_video_link"
                                value="{{ empty($data->counter_section_video_link) ? '' : $data->counter_section_video_link }}"
                                placeholder="{{ __('Enter counter section video link') }}">
                              <p id="err_hero_section_title" class="mt-2 mb-0 text-danger em"></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif


                    <!-- Benifit section -->
                    @if ($settings->theme_version == 3)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Benifit Section') }}</h2>
                        <hr>
                        <div class="row">

                          <div class="col-lg-6">
                            <div class="form-group">
                              <label for="">{{ __('Benifit Section Title') }}</label>
                              <input type="text" class="form-control" name="benifit_section_title"
                                value="{{ empty($data->benifit_section_title) ? '' : $data->benifit_section_title }}"
                                placeholder="{{ __('Enter benifit section title') }}">
                            </div>
                          </div>

                        </div>
                      </div>
                    @endif


                    <!-- testimonial section -->
                    @if ($settings->theme_version == 1 || $settings->theme_version == 2)
                      <div class="col-lg-12">
                        <h2 class="mt-3 text-warning">{{ __('Testimonial Section') }}</h2>
                        <hr>
                        <div class="row">

                          <div class="col-lg-6">
                            <div class="form-group">
                              <label for="">{{ __('Testimonial Section Title') . '*' }}</label>
                              <input type="text" class="form-control" name="testimonial_section_title"
                                value="{{ empty($data->testimonial_section_title) ? '' : $data->testimonial_section_title }}"
                                placeholder="{{ __('Enter testimonial section title') }}">
                            </div>
                          </div>

                          @if ($settings->theme_version == 2)
                            <div class="col-lg-6">
                              <div class="form-group">
                                <label for="">{{ __('Testimonial Section Subtitle') }}</label>
                                <input type="text" class="form-control" name="testimonial_section_subtitle"
                                  value="{{ empty($data->testimonial_section_subtitle) ? '' : $data->testimonial_section_subtitle }}"
                                  placeholder="{{ __('Enter testimonial section subtitle') }}">
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <div class="form-group">
                                <label for="">{{ __('Testimonial Section Clients') }}</label>
                                <input type="text" class="form-control" name="testimonial_section_clients"
                                  value="{{ empty($data->testimonial_section_clients) ? '' : $data->testimonial_section_clients }}"
                                  placeholder="{{ __('Enter testimonial section clients') }}">
                              </div>
                            </div>
                          @endif
                        </div>
                      </div>
                    @endif

                    <!-- blog section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('Blog Section') }}</h2>
                      <hr>
                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="">{{ __('Blog Section Title') . '*' }}</label>
                            <input type="text" class="form-control" name="blog_section_title"
                              value="{{ empty($data->blog_section_title) ? '' : $data->blog_section_title }}"
                              placeholder="{{ __('Enter blog section title') }}">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="">{{ __('Blog Section Button Text') . '*' }}</label>
                            <input type="text" class="form-control" name="blog_section_button_text"
                              value="{{ empty($data->blog_section_button_text) ? '' : $data->blog_section_button_text }}"
                              placeholder="{{ __('Enter blog section button text') }}">
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- call to action section -->
                    <div class="col-lg-12">
                      <h2 class="mt-3 text-warning">{{ __('Call To Action Section') }}</h2>
                      <hr>
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="row">
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-group">
                            <label for="">{{ __('Call To Action Section Title') . '*' }}</label>
                            <input type="text" class="form-control" name="call_to_action_section_title"
                              value="{{ empty($data->call_to_action_section_title) ? '' : $data->call_to_action_section_title }}"
                              placeholder="{{ __('Enter call to action section title') }}">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="">{{ __('Call To Action Section Button Name') }}</label>
                            <input type="text" class="form-control" name="call_to_action_section_btn"
                              value="{{ empty($data->call_to_action_section_btn) ? '' : $data->call_to_action_section_btn }}"
                              placeholder="{{ __('Enter call to action section button name') }}">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="">{{ __('Call To Action Button Url') }}</label>
                            <input type="text" class="form-control" name="call_to_action_button_url"
                              value="{{ empty($data->call_to_action_button_url) ? '' : $data->call_to_action_button_url }}"
                              placeholder="{{ __('Enter call to action section button url') }}">
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="card-footer">
                  <div class="row">
                    <div class="col-12 text-center">
                      <button type="submit" form="commonForm" class="btn btn-primary">
                        {{ __('Update') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
            </div>
          </div>
        </div>
        </form>
      </div>
    </div>
  </div>
@endsection
