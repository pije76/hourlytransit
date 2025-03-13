@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Additional Services') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('vendor.dashboard') }}">
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
        <a href="#">{{ __('Manage Rooms') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      @php
        $dContent = App\Models\RoomContent::where('room_id', $room_id)
            ->where('language_id', $defaultLang->id)
            ->first();
        $title = !empty($dContent) ? $dContent->title : '';
      @endphp
      @if (!empty($title))
        <li class="nav-item">
          <a href="#">

            {{ strlen(@$title) > 20 ? mb_substr(@$title, 0, 20, 'utf-8') . '...' : @$title }}
          </a>
        </li>
        <li class="separator">
          <i class="flaticon-right-arrow"></i>
        </li>
      @endif

      <li class="nav-item">
        <a href="#">{{ __('Additional Services') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Additional Services') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block"
            href="{{ route('vendor.room_management.rooms', ['language' => $defaultLang->code]) }}">
            <span class="btn-label">
              <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
          </a>
          @php
            $dContent = App\Models\RoomContent::where('room_id', $room_id)
                ->where('language_id', $defaultLang->id)
                ->first();
            $slug = !empty($dContent) ? $dContent->slug : '';
          @endphp
          @if ($dContent)
            <a class="btn btn-success btn-sm float-right mr-1 d-inline-block"
              href="{{ route('frontend.room.details', ['slug' => $slug, 'id' => $room_id]) }}" target="_blank">
              <span class="btn-label">
                <i class="fas fa-eye"></i>
              </span>
              {{ __('Preview') }}
            </a>
          @endif
        </div>
        <div class="card-body pt-5 pb-3">
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="alert alert-danger pb-1 dis-none" id="commonFormErrors">
                <ul></ul>
              </div>

              <form id="commonForm" action="{{ route('vendor.room_management.update_additional_service', $room_id) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="listing_id" value="{{ $room_id }}">
                <div class="row">

                    @php
                      $hasservice = json_decode($room->additional_service, true);
                    @endphp
                    @if ($hasservice)
                      @foreach ($services as $key => $service)
                      <div class="col-lg-6 col-md-6">
                        <ul class="list-group list-group-style-2">

                            <li class="list-group-item d-flex gap-10 justify-content-between align-items-center">
                              <div class="d-flex gap-10">
                                <input class="input-checkbox" type="checkbox" name="checkbox[]"
                                  @if (array_key_exists($service->id, $hasservice)) checked @endif id="checkbox_{{ $service->id }}"
                                  value="{{ $service->id }}">
                                <label for="checkbox_{{ $service->id }}"><span>{{ $service->title }}</span></label>

                              </div>
                              <div class="input-field d-flex gap-10 align-items-center">
                                <input class="form-control"name="price_{{ $service->id }}" type="text"
                                  value="{{ array_key_exists($service->id, $hasservice) ? $hasservice[$service->id] : '' }}">
                                <span class="">({{ $settings->base_currency_text }})</span>
                              </div>
                            </li>
                        </ul>
                      </div>
                      @endforeach
                    @else
                      @foreach ($services as $key => $service)
                      <div class="col-lg-6 col-md-6">
                        <ul class="list-group list-group-style-2">
                            <li class="list-group-item d-flex gap-10 justify-content-between align-items-center">
                              <div class="d-flex gap-10">
                                <input class="input-checkbox" type="checkbox"
                                  name="checkbox[]"id="checkbox_{{ $service->id }}" value="{{ $service->id }}">
                                <label for="checkbox_{{ $service->id }}"><span>{{ $service->title }}</span></label>
                              </div>
                              <div class="input-field d-flex gap-10 align-items-center">
                                <input class="form-control"name="price_{{ $service->id }}" type="text"
                                  value="">
                                <span class="">({{ $settings->base_currency_text }})</span>
                              </div>
                            </li>
                        </ul>
                      </div>
                      @endforeach
                    @endif
                
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" form="commonForm" class="btn btn-primary">
                {{ __('Update') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
