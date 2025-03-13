@extends('admin.layout')

{{-- this style will be applied when the direction of language is right-to-left --}}
@includeIf('admin.partials.rtl-style')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Additional Service') }}</h4>
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
        <a href="#">{{ __('Rooms Management') }}</a>
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
        <a href="#">{{ __('Additional Service') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex gap-10 flex-wrap align-items-center justify-content-between">

            <div class="card-title d-inline-block">{{ __('Additional Service') }}</div>

            <div class="d-flex gap-10 flex-wrap align-items-center">
              <a href="#" data-toggle="modal" data-target="#createModal"
                class="btn btn-primary btn-sm float-lg-right float-left"><i class="fas fa-plus"></i>
                {{ __('Add') }}</a>

              <button class="btn btn-danger btn-sm float-right mr-2 d-none bulk-delete"
                data-href="{{ route('admin.room_management.additional_service.bulk_delete') }}">
                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($services) == 0)
                <h3 class="text-center mt-2">{{ __('NO ADDITIONAL SERVICE FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Title') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Serial Number') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($services as $service)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $service->id }}">
                          </td>
                          <td>
                            {{ strlen($service->title) > 50 ? mb_substr($service->title, 0, 50, 'UTF-8') . '...' : $service->title }}
                          </td>
                          <td>
                            <h2 class="d-inline-block">
                              @if ($service->status == 1)
                                <span class="badge badge-success">{{ __('Active') }}</span>
                              @endif
                              @if ($service->status == 0)
                                <span class="badge badge-danger">{{ __('Deactive') }}</span>
                              @endif
                            </h2>
                          </td>
                          <td>{{ $service->serial_number }}</td>
                          <td>
                            <a class="btn btn-secondary btn-sm mr-1  mt-1 editBtn" href="#" data-toggle="modal"
                              data-target="#editModal"
                              @foreach ($langs as $lang)
                                                            @php
                                                                
                                                                $content = \App\Models\AdditionalServiceContent::where([["additional_service_id",$service->id],['language_id',$lang->id]])->first();
                                                            @endphp 
                                                            data-{{ @$lang->code }}_title="{{ @$content->title }}" @endforeach
                              data-id="{{ $service->id }}" data-name="{{ $service->title }}"
                              data-status="{{ $service->status }}" data-icon="{{ $service->icon }}"
                              data-language_id="{{ $service->language_id }}"
                              data-serial_number="{{ $service->serial_number }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                            </a>

                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.room_management.additional_service.delete', ['id' => $service->id]) }}"
                              method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger  mt-1 btn-sm deleteBtn">
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
  @include('admin.room-management.additional-service.create')

  {{-- edit modal --}}
  @include('admin.room-management.additional-service.edit')
@endsection
