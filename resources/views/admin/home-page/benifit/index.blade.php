@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Benifits') }}</h4>
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
        <a href="#">{{ __('Home Page') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Benifits') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Slider Version') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">

    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-7">
              <div class="card-title d-inline-block">{{ __('Benifits') }}</div>
            </div>

            <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
              <a href="#" data-toggle="modal" data-target="#createModal"
                class="btn btn-primary btn-sm float-lg-right float-left"><i class="fas fa-plus"></i>
                {{ __('Add') }}</a>
            </div>
          </div>
        </div>



        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              @if (count($benifits) == 0)
                <h3 class="text-center mt-2">{{ __('NO BENIFIT FOUND') . '!' }}</h3>
              @else
                <div class="row">
                  @foreach ($benifits as $benifit)
                    <div class="col-md-3">
                      <div class="card">
                        <div class="card-body">
                          <img src="{{ asset('assets/img/benifits/' . $benifit->background_image) }}" alt="image"
                            class="w-100">
                        </div>

                        <div class="card-footer text-center">
                          <a class="editBtn btn btn-secondary btn-sm mr-2 mb-1" href="#" data-toggle="modal"
                            data-target="#editModal" data-id="{{ $benifit->id }}"
                            data-image="{{ asset('assets/img/benifits/' . $benifit->background_image) }}"
                            data-title="{{ $benifit->title }}" data-text="{{ $benifit->text }}">
                            <span class="btn-label">
                              <i class="fas fa-edit"></i>
                            </span>
                          </a>

                          <form class="deleteForm d-inline-block"
                            action="{{ route('admin.pages.home_page.benifit_section.delete', ['id' => $benifit->id]) }}"
                            method="post">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm deleteBtn mb-1">
                              <span class="btn-label">
                                <i class="fas fa-trash"></i>
                              </span>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- create modal --}}
  @include('admin.home-page.benifit.create')

  {{-- edit modal --}}
  @include('admin.home-page.benifit.edit')
@endsection
