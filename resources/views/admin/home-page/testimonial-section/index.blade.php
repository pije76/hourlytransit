@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Testimonials') }}</h4>
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
        <a href="#">{{ __('Testimonials') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">

    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex gap-10 flex-wrap align-items-center justify-content-between">
            <div class="card-title">{{ __('Testimonials') }}</div>

            <div class="d-flex gap-10 flex-wrap align-items-center">
              <a href="#" data-toggle="modal" data-target="#createModal"
                class="btn btn-primary btn-sm float-lg-right float-left"><i class="fas fa-plus"></i>
                {{ __('Add') }}</a>

              <button class="btn btn-danger btn-sm float-right mr-2 d-none bulk-delete"
                data-href="{{ route('admin.pages.bulk_delete_testimonial') }}">
                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col">
              @if (count($testimonials) == 0)
                <h3 class="text-center mt-2">{{ __('NO TESTIMONIAL FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>

                        @if ($themeInfo->theme_version == 2)
                          <th scope="col">{{ __('Image') }}</th>
                        @endif

                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Occupation') }}</th>
                        <th scope="col">{{ __('Comment') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($testimonials as $testimonial)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $testimonial->id }}">
                          </td>

                          @if ($themeInfo->theme_version == 2)
                            <td>
                              @if (is_null($testimonial->image))
                                -
                              @else
                                <img src="{{ asset('assets/img/clients/' . $testimonial->image) }}" alt="client image"
                                  width="45">
                              @endif
                            </td>
                          @endif

                          <td>{{ $testimonial->name }}</td>
                          <td>{{ $testimonial->occupation }}</td>
                          <td>
                            {{ strlen($testimonial->comment) > 50 ? mb_substr($testimonial->comment, 0, 50, 'UTF-8') . '...' : $testimonial->comment }}
                          </td>
                          <td>
                            <a class="btn btn-secondary btn-sm mr-1  mt-1 editBtn" href="#" data-toggle="modal"
                              data-target="#editModal" data-id="{{ $testimonial->id }}"
                              data-image="{{ is_null($testimonial->image) ? asset('assets/img/noimage.jpg') : asset('assets/img/clients/' . $testimonial->image) }}"
                              data-name="{{ $testimonial->name }}" data-occupation="{{ $testimonial->occupation }}"
                              data-comment="{{ $testimonial->comment }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.pages.delete_testimonial', ['id' => $testimonial->id]) }}"
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
  @include('admin.home-page.testimonial-section.create')

  {{-- edit modal --}}
  @include('admin.home-page.testimonial-section.edit')
@endsection
