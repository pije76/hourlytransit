@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Holidays') }}</h4>
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
        <a href="#">{{ __('Holidays') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <!-- card-header-area -->
          <div class="card-header-area">
            <div class="card-title"><h2 class="mb-0">{{ __('Holidays') }}</h2></div>
            <!-- holiday-form -->
            <div class="holiday-form">
              <form action="{{ route('admin.hotel_management.hotel.holiday') }}" method="get" id="daySearch">
                <div class="select-input">
                  <select name="vendor_id" class="select2 select2-element"
                    onchange="document.getElementById('daySearch').submit()">
                    <option value="admin" @selected(request()->input('vendor_id') == 'admin')>{{ __('Admin') }}</option>
                    @foreach ($vendors as $vendor)
                      <option @selected($vendor->id == request()->input('vendor_id')) value="{{ $vendor->id }}">
                        {{ $vendor->username }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <input type="hidden" name="language" id=""value="{{ request()->input('language') }}">
              </form>
            </div>
            <!-- card-header-buttons -->
            <div class="card-header-buttons">
              <div class="btn-groups justify-content-md-end gap-10">
                <a class="btn btn-info btn-sm d-inline-block" href="#" data-toggle="modal"
                  data-target="#createModal">
                  <span class="btn-label">
                    <i class="fas fa-plus"></i>
                  </span>
                  {{ __('Add Holiday') }}
                </a>
                <button class="btn btn-danger btn-sm d-none bulk-delete"
                  data-href="{{ route('admin.global.holiday.bluk-destroy') }}">
                  <i class="flaticon-interface-5"></i> {{ __('Delete') }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($globalHoliday) == 0)
                <h3 class="text-center mt-2">{{ __('NO HOLIDAY FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Hotel') }}</th>
                        <th scope="col">{{ __('Date') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($globalHoliday as $holiday)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $holiday->id }}">
                          </td>
                          <td>
                            <a href="{{ route('frontend.hotel.details', ['slug' => $holiday->slug, 'id' => $holiday->hotel_id]) }}"
                              target="_blank">
                              {{ strlen(@$holiday->title) > 50 ? mb_substr(@$holiday->title, 0, 50, 'utf-8') . '...' : @$holiday->title }}
                            </a>

                          <td>{{ \Carbon\Carbon::parse($holiday->date)->format('d F, Y') }}</td>

                          <td>
                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.hotel_management.hotel.holiday.delete', ['id' => $holiday->id]) }}"
                              method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger btn-sm deleteBtn">
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
  @include('admin.hotel-management.holiday.create')
@endsection
