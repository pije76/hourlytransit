@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Cities') }}</h4>
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
        <a href="#">{{ __('Cities') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex gap-10 flex-wrap align-items-center justify-content-between">

            <div class="card-title">{{ __('Cities') }}</div>

            <div class="d-flex gap-10 flex-wrap align-items-center">
              <div class="text-right">
                <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-primary btn-sm"><i
                    class="fas fa-plus"></i>
                  {{ __('Add') }}</a>

                <button class="btn btn-danger btn-sm ml-2 d-none bulk-delete"
                  data-href="{{ route('admin.hotel_management.location.bulk_delete_city') }}">
                  <i class="flaticon-interface-5"></i> {{ __('Delete') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col">
              @if (count($cities) == 0)
                <h3 class="text-center mt-2">{{ __('NO CITY FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Image') }}</th>
                        <th scope="col">{{ __('State') }}</th>
                        <th scope="col">{{ __('Country') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($cities as $city)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $city->id }}">
                          </td>
                          <td>
                            {{ $city->name }}
                          </td>
                          <td>
                            <div class="max-dimensions">
                              <img
                                src="{{ $city->feature_image ? asset('assets/img/location/city/' . $city->feature_image) : asset('assets/admin/img/noimage.jpg') }}"
                                alt="...">
                            </div>
                          </td>
                          <td>
                            @if ($city->state_id)
                              {{ $city->state->name }}
                            @else
                              --
                            @endif
                          </td>
                          <td>
                            @if ($city->country_id)
                              {{ $city->country->name }}
                            @else
                              --
                            @endif
                          </td>

                          <td>
                            @php
                              $x = App\Models\Location\State::Where([
                                  ['language_id', $city->language_id],
                                  ['country_id', $city->country_id],
                              ])->count();
                              $okValue = $x != 0 ? 'OK' : null;
                            @endphp
                            <a class="btn btn-secondary btn-sm mr-1  mt-1 editBtn" href="#" data-toggle="modal"
                              data-target="#editModal" data-id="{{ $city->id }}"
                              data-country_id="{{ $city->country_id }}" data-state_id="{{ $city->state_id }}"
                              data-haveState="{{ $okValue }}"
                              data-name="{{ $city->name }}"data-image="{{ asset('assets/img/location/city/' . $city->feature_image) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.hotel_management.location.delete_city', ['id' => $city->id]) }}"
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
  @include('admin.hotel-management.location.city.create')

  {{-- edit modal --}}
  @include('admin.hotel-management.location.city.edit')
@endsection
@section('script')
  <script src="{{ asset('assets/admin/js/location.js') }}"></script>
@endsection
