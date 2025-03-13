@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Features') }}</h4>
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
        <a href="#">{{ __('Features') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex gap-10 flex-wrap align-items-center justify-content-between">
            <div class="card-title d-inline-block">{{ __('Features') }}</div>
            <div>
              @includeIf('backend.partials.languages')
            </div>
            <div class="card-header-btn">
              <a href="#" data-toggle="modal" data-target="#createModal"
                class="btn btn-primary btn-sm float-lg-right float-left"><i class="fas fa-plus"></i>
                {{ __('Add') }}</a>

              <button class="btn btn-danger btn-sm float-right mr-2 d-none bulk-delete "
                data-href="{{ route('admin.pages.feature_content.bulk_delete') }}">
                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($features) < 1)
                <div class="p-4 text-center radius-md">
                  <h3 class="mb-0">{{ __('NO FEATUR FOUND') }}</h3>
                </div>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Image') }}</th>
                        <th scope="col">{{ __('Title') }}</th>
                        @if ($settings->theme_version != 2)
                          <th scope="col">{{ __('Sub Title') }}</th>
                        @endif
                        <th scope="col">{{ __('Serial Number') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>

                      @foreach ($features as $feature)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $feature->id }}">
                          </td>
                          <td>
                            @if (is_null($feature->image))
                              -
                            @else
                              <img src="{{ asset('assets/img/feature/' . $feature->image) }}" alt="client image"
                                width="45">
                            @endif
                          </td>
                          <td>
                            {{ $feature->title }}
                          </td>
                          @if ($settings->theme_version != 2)
                            <td>
                              {{ $feature->subtitle }}
                            </td>
                          @endif
                          <td>
                            {{ $feature->serial_number }}
                          </td>
                          <td>
                            <a class="btn btn-secondary btn-sm mr-1  mt-1 editBtn" href="#" data-toggle="modal"
                              data-target="#editModal" data-id="{{ $feature->id }}"
                              data-text="{{ $feature->text }}" data-serial_number="{{ $feature->serial_number }}"
                              data-title="{{ $feature->title }}" data-subtitle="{{ $feature->subtitle }}"
                              data-image="{{ is_null($feature->image) ? asset('assets/img/noimage.jpg') : asset('assets/img/feature/' . $feature->image) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.pages.feature_content.delete', ['id' => $feature->id]) }}"
                              method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger mt-1 btn-sm deleteBtn">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i>
                                </span>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>
        <div class="card-footer"></div>
      </div>
    </div>
  </div>

  {{-- create modal --}}
  @include('admin.home-page.feature-section.create')

  {{-- edit modal --}}
  @include('admin.home-page.feature-section.edit')
@endsection
