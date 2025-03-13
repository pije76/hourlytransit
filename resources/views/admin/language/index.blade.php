@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Languages') }}</h4>
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
        <a href="#">{{ __('Settings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Languages') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">

          <div class="d-flex gap-10 flex-wrap align-items-center justify-content-between">
            <div class="card-title">{{ __('Languages') }}</div>

            <div class="d-flex gap-10 flex-wrap align-items-center">
              <a href="#" class="btn btn-secondary btn-sm editBtn"
                data-toggle="modal" data-target="#addModaladmin">
                <span class="btn-label">
                  <i class="fas fa-plus"></i>
                </span>
                {{ __('Add New Keyword for admin') }}
              </a>
              <a href="#" class="btn btn-secondary btn-sm editBtn"
                data-toggle="modal" data-target="#addModal">    
                <span class="btn-label">
                  <i class="fas fa-plus"></i>
                </span>
                {{ __('Add New Keyword for front') }}
              </a>
              <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#createModal">
                <i class="fas fa-plus"></i> {{ __('Add') }}
              </a>
            </div>

          </div>


        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($languages) == 0)
                <h3 class="text-center">{{ __('NO LANGUAGE FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('#') }}</th>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Code') }}</th>
                        <th scope="col">{{ __('Direction') }}</th>
                        <th scope="col">{{ __('Website Language') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($languages as $language)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $language->name }}</td>
                          <td>{{ $language->code }}</td>
                          <td>{{ $language->direction == 1 ? __('RTL') : __('LTR') }}</td>
                          <td>
                            @if ($language->is_default == 1)
                              <strong class="badge badge-success">{{ __('Default') }}</strong>
                            @else
                              <form class="d-inline-block"
                                action="{{ route('admin.settings.language_management.make_default_language', ['id' => $language->id]) }}"
                                method="post">
                                @csrf
                                <button class="btn btn-primary btn-sm" type="submit" name="button">
                                  {{ __('Make Default') }}
                                </button>
                              </form>
                            @endif
                          </td>
                          <td>
                            <a href="#" class="btn  mt-1 btn-secondary btn-sm mr-1 editBtn" data-toggle="modal"
                              data-target="#editModal" data-id="{{ $language->id }}" data-name="{{ $language->name }}"
                              data-code="{{ $language->code }}" data-direction="{{ $language->direction }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                              {{ __('Edit') }}
                            </a>

                            <a class="btn btn-secondary btn-sm"
                              href="{{ route('admin.settings.language_management.edit_admin_keyword', $language->id) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                              {{ __('Edit Admin Keyword') }}
                            </a>

                            <a class="btn btn-info  mt-1 btn-sm mr-1"
                              href="{{ route('admin.settings.language_management.edit_front_keyword', $language->id) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                              {{ __('Edit Front Keyword') }}
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.settings.language_management.delete', ['id' => $language->id]) }}"
                              method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger  mt-1 btn-sm deleteBtn">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i>
                                </span>
                                {{ __('Delete') }}
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
      </div>
    </div>
  </div>

  {{-- create modal --}}
  @include('admin.language.create')

  {{-- edit modal --}}
  @include('admin.language.edit')

  {{-- keyword modal for front --}}
  @include('admin.language.add-keyword-front')

  {{-- keyword modal for admin --}}
  @include('admin.language.add-keyword-admin')


@endsection
