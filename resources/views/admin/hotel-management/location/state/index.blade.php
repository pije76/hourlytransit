@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('States') }}</h4>
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
        <a href="#">{{ __('Hotels Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Specifications') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Location') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('States') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">

    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-header-area">
            <div class="card-title">
              <h2 class="mb-0">{{ __('States') }}</h2>
            </div>
            <div class="card-header-buttons">
              <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-primary btn-sm"><i
                  class="fas fa-plus"></i>
                {{ __('Add') }}</a>

              <button class="btn btn-danger btn-sm ml-2 d-none bulk-delete"
                data-href="{{ route('admin.hotel_management.location.bulk_delete_state') }}">
                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col">
              @if (count($states) == 0)
                <h3 class="text-center mt-2">{{ __('NO STATE FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Country Name') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($states as $state)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $state->id }}">
                          </td>
                          <td>
                            {{ $state->name }}
                          </td>
                          <td>
                            @if ($state->country_id)
                              {{ $state->country->name }}
                            @else
                              --
                            @endif
                          </td>
                          <td>
                            <a class="btn btn-secondary btn-sm mr-1  mt-1 editBtn" href="#" data-toggle="modal"
                              data-target="#editModal"
                              data-id="{{ $state->id }}"data-country_id="{{ $state->country_id }}"
                              data-name="{{ $state->name }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.hotel_management.location.delete_state', ['id' => $state->id]) }}"
                              method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger btn-sm  mt-1 deleteBtn">
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
  @include('admin.hotel-management.location.state.create')

  {{-- edit modal --}}
  @include('admin.hotel-management.location.state.edit')
@endsection
@section('script')
  <script src="{{ asset('assets/admin/js/location.js') }}"></script>
@endsection
