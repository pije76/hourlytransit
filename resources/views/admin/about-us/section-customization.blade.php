@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Hide / Show Section') }}</h4>
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
        <a href="#">{{ __('Hide / Show Section') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('admin.about_us.customize_update') }}" method="POST">
          @csrf
          <div class="card-header">
            <div class="card-title d-inline-block">{{ __('Hide / Show Section') }}</div>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">

                <div class="form-group">
                  <label>{{ __('About Section Status') }}</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="about_section_status" value="1" class="selectgroup-input"
                        {{ $aboutSec->about_section_status == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Enable') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="about_section_status" value="0" class="selectgroup-input"
                        {{ $aboutSec->about_section_status == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Disable') }}</span>
                    </label>
                  </div>
                  @error('about_section_status')
                    <p class="mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label>{{ __('Features Section Status') }}</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="about_features_section_status" value="1" class="selectgroup-input"
                        {{ $aboutSec->about_features_section_status == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Enable') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="about_features_section_status" value="0" class="selectgroup-input"
                        {{ $aboutSec->about_features_section_status == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Disable') }}</span>
                    </label>
                  </div>
                  @error('about_features_section_status')
                    <p class="mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label>{{ __('Counter Section Status') }}</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="about_counter_section_status" value="1" class="selectgroup-input"
                        {{ $aboutSec->about_counter_section_status == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Enable') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="about_counter_section_status" value="0" class="selectgroup-input"
                        {{ $aboutSec->about_counter_section_status == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Disable') }}</span>
                    </label>
                  </div>
                  @error('about_counter_section_status')
                    <p class="mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label>{{ __('Testimonial Section Status') }}</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="about_testimonial_section_status" value="1"
                        class="selectgroup-input"
                        {{ $aboutSec->about_testimonial_section_status == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Enable') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="about_testimonial_section_status" value="0"
                        class="selectgroup-input"
                        {{ $aboutSec->about_testimonial_section_status == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Disable') }}</span>
                    </label>
                  </div>
                  @error('about_testimonial_section_status')
                    <p class="mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>

                @if (count($customSectons) > 0)
                  @foreach ($customSectons as $customSecton)
                    @php
                      $content = App\Models\HomePage\CustomSectionContent::where(
                          'custom_section_id',
                          $customSecton->id,
                      )->first();
                      $customStatus = json_decode($aboutSec->about_custom_section_status, true);
                      $sectionStatus = isset($customStatus[$customSecton->id]) ? $customStatus[$customSecton->id] : 0;
                    @endphp
                    <div class="form-group">
                      <label>{{ $content->section_name }} {{ __('Status') }}</label>
                      <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                          <input type="radio" name="about_custom_section_status[{{ $customSecton->id }}]"
                            value="1" class="selectgroup-input" {{ $sectionStatus == 1 ? 'checked' : '' }}>
                          <span class="selectgroup-button">{{ __('Enable') }}</span>
                        </label>

                        <label class="selectgroup-item">
                          <input type="radio" name="about_custom_section_status[{{ $customSecton->id }}]"
                            value="0" class="selectgroup-input" {{ $sectionStatus == 0 ? 'checked' : '' }}>
                          <span class="selectgroup-button">{{ __('Disable') }}</span>
                        </label>
                      </div>
                      @error('about_custom_section_status')
                        <p class="mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  @endforeach
                @endif


              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-success">
                  {{ __('Update') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
