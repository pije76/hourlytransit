@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Content') }}</h4>
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
        <a href="#">{{ __('Common Sections') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Footer') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Content') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-10">
              <div class="card-title">{{ __('Content') }}</div>
            </div>

            <div class="col-lg-2">
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-6 offset-lg-3">
              <form id="ajaxForm"
                action="{{ route('admin.pages.footer.update_content', ['language' => request()->input('language')]) }}"
                method="post">
                @csrf
                <div class="form-group">
                  <label>{{ __('About Company') . '*' }}</label>
                  <textarea class="form-control" name="about_company" rows="5" cols="80">{{ !is_null($data) ? $data->about_company : '' }}</textarea>
                  <p id="err_about_company" class="em text-danger mt-2 mb-0"></p>
                </div>

                <div class="form-group">
                  <label>{{ __('Copyright Text') . '*' }}</label>
                  <textarea class="form-control summernote" name="copyright_text" rows="3">{{ !is_null($data) ? $data->copyright_text : '' }}</textarea>
                  <p id="err_copyright_text" class="em text-danger mt-2 mb-0"></p>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" id="submitBtn" class="btn btn-success">
                {{ __('Update') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
