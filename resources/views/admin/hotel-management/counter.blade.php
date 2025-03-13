@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Counter Information') }}</h4>
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
        <a href="#">{{ __('Manage Hotels') }}</a>
      </li>
      @php
        $dContent = App\Models\HotelContent::where('hotel_id', $hotel_id)
            ->where('language_id', $defaultLang->id)
            ->first();
        $title = !empty($dContent) ? $dContent->title : '';
      @endphp

      @if (!empty($title))
        <li class="separator">
          <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
          <a href="#">
            {{ strlen(@$title) > 20 ? mb_substr(@$title, 0, 20, 'utf-8') . '...' : @$title }}
          </a>
        </li>
      @endif
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Counter Information') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Counter Information') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block"
            href="{{ route('admin.hotel_management.hotels', ['language' => $defaultLang->code]) }}">
            <span class="btn-label">
              <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
          </a>
          @php
            $dContent = App\Models\HotelContent::where('hotel_id', $hotel_id)
                ->where('language_id', $defaultLang->id)
                ->first();
            $slug = !empty($dContent) ? $dContent->slug : '';
          @endphp
          @if ($dContent)
            <a class="btn btn-success btn-sm float-right mr-1 d-inline-block"
              href="{{ route('frontend.hotel.details', ['slug' => $slug, 'id' => $hotel_id]) }}" target="_blank">
              <span class="btn-label">
                <i class="fas fa-eye"></i>
              </span>
              {{ __('Preview') }}
            </a>
          @endif
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-10 offset-lg-1">
              <div class="alert alert-danger pb-1 dis-none" id="commonFormErrors">
                <ul></ul>
              </div>

              <form id="commonForm" action="{{ route('admin.hotel_management.update_counter_section', $hotel_id) }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                  <div class="col-lg-12" id="variation_pricing">
                    <h4 for="">{{ __('Counter Information') }}</h4>
                    <table class="table table-bordered ">
                      <thead>
                        <tr>
                          <th>{{ __('Label') }}</th>
                          <th>{{ __('Value') }}</th>
                          <th><a href="javascrit:void(0)" class="btn  btn-sm btn-success addRow"><i
                                class="fas fa-plus-circle"></i></a></th>
                        </tr>
                      <tbody id="tbody">

                        @if (count($specifications) > 0)
                          @foreach ($specifications as $specification)
                            <tr>
                              <td>
                                @foreach ($languages as $language)
                                  @php
                                    $sp_content = App\Models\HotelCounterContent::where([
                                        ['language_id', $language->id],
                                        ['hotel_counter_id', $specification->id],
                                    ])->first();
                                  @endphp
                                  <div class="form-group">
                                    <input type="text" name="{{ $language->code }}_label[]"
                                      value="{{ @$sp_content->label }}" class="form-control"
                                      placeholder="{{ __('Label') }} ({{ $language->name }})">
                                  </div>
                                @endforeach
                              </td>
                              <td>
                                @foreach ($languages as $language)
                                  @php
                                    $sp_content = App\Models\HotelCounterContent::where([
                                        ['language_id', $language->id],
                                        ['hotel_counter_id', $specification->id],
                                    ])->first();
                                  @endphp
                                  <div class="form-group">
                                    <input type="text" name="{{ $language->code }}_value[]"
                                      value="{{ @$sp_content->value }}" class="form-control"
                                      placeholder="{{ __('Value') }} ({{ $language->name }})">
                                  </div>
                                @endforeach
                              </td>
                              <td>
                                <a href="javascript:void(0)" data-counter="{{ $specification->id }}"
                                  class="btn  btn-sm btn-danger deleteCounter">
                                  <i class="fas fa-minus"></i></a>
                              </td>
                            </tr>
                          @endforeach
                        @else
                          <tr>
                            <td>
                              @foreach ($languages as $language)
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  <input type="text" name="{{ $language->code }}_label[]" class="form-control"
                                    placeholder="Label ({{ $language->name }})">
                                </div>
                              @endforeach
                            </td>
                            <td>
                              @foreach ($languages as $language)
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                  <input type="text" name="{{ $language->code }}_value[]" class="form-control"
                                    placeholder="Value ({{ $language->name }})">
                                </div>
                              @endforeach
                            </td>
                            <td>
                              <a href="javascript:void(0)" class="btn btn-danger  btn-sm deleteRow">
                                <i class="fas fa-minus"></i></a>
                            </td>
                          </tr>
                        @endif
                      </tbody>
                      </thead>
                    </table>
                  </div>
                </div>
            </div>
            </form>
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
  </div>
@endsection
@php
  $languages = App\Models\Language::get();
  $labels = '';
  $values = '';
  foreach ($languages as $language) {
      $label_name = $language->code . '_label[]';
      $value_name = $language->code . '_value[]';
      if ($language->direction == 1) {
          $direction = 'form-group rtl text-right';
      } else {
          $direction = 'form-group';
      }

      $labels .=
          "<div class='$direction'><input type='text' name='" .
          $label_name .
          "' class='form-control' placeholder='Label ($language->name)'></div>";
      $values .= "<div class='$direction'><input type='text' name='$value_name' class='form-control' placeholder='Value ($language->name)'></div>";
  }
@endphp

@section('script')
  <script type="text/javascript" src="{{ asset('assets/admin/js/counter.js') }}"></script>
@endsection

@section('variables')
  <script>
    "use strict";

    var labels = "{!! $labels !!}";
    var values = "{!! $values !!}";

    var featureRmvUrl = "{{ route('admin.hotel_management.delete_counter') }}"

    var languages = {!! json_encode($languages) !!};
  </script>
@endsection
