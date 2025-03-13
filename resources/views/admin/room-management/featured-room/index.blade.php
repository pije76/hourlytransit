@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('All') }}</h4>
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
        <a href="#">{{ __('Featured Rooms') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Requests') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('All') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-10">
              <form id="searchForm" action="{{ route('admin.room_management.featured_room.all_request') }}"
                method="GET">
                <div class="row">
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Order Number') }}</label>
                      <input name="order_no" type="text" id="order_no" class="form-control"
                        placeholder="{{ __('Search Here...') }}"
                        value="{{ !empty(request()->input('order_no')) ? request()->input('order_no') : '' }}">
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Room Title') }}</label>
                      <input name="title" type="text" id="room_title" class="form-control"
                        placeholder="{{ __('Search Here...') }}"
                        value="{{ !empty(request()->input('title')) ? request()->input('title') : '' }}">
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Payment Status') }}</label>
                      <select class="form-control h-42" name="payment_status"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" {{ empty(request()->input('payment_status')) ? 'selected' : '' }}>
                          {{ __('All') }}
                        </option>
                        <option value="completed"
                          {{ request()->input('payment_status') == 'completed' ? 'selected' : '' }}>
                          {{ __('Completed') }}
                        </option>
                        <option value="pending" {{ request()->input('payment_status') == 'pending' ? 'selected' : '' }}>
                          {{ __('Pending') }}
                        </option>
                        <option value="rejected"
                          {{ request()->input('payment_status') == 'rejected' ? 'selected' : '' }}>
                          {{ __('Rejected') }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <input type="hidden" name="language" value="{{ request()->input('language') }}" class="form-control"
                    placeholder="{{ __('language') }}">
                  <div class="col-lg-3">
                    <div class="form-group">
                      <label>{{ __('Order Status') }}</label>
                      <select class="form-control h-42" name="order_status"
                        onchange="document.getElementById('searchForm').submit()">
                        <option value="" {{ empty(request()->input('order_status')) ? 'selected' : '' }}>
                          {{ __('All') }}
                        </option>
                        <option value="pending" {{ request()->input('order_status') == 'pending' ? 'selected' : '' }}>
                          {{ __('Pending') }}
                        </option>
                        <option value="apporved" {{ request()->input('order_status') == 'apporved' ? 'selected' : '' }}>
                          {{ __('Approved') }}
                        </option>
                        <option value="rejected" {{ request()->input('order_status') == 'rejected' ? 'selected' : '' }}>
                          {{ __('Rejected') }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </form>
            </div>

            <div class="col-lg-2">
              <button class="btn btn-danger btn-sm d-none bulk-delete float-lg-right"
                data-href="{{ route('admin.room_management.featured_room.bulk_delete_order') }}"
                class="card-header-button">
                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($orders) == 0)
                <h3 class="text-center mt-3">{{ __('NO REQUEST FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-2">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Order No.') }}</th>
                        <th scope="col">{{ __('Room Title') }}</th>
                        <th scope="col">{{ __('Paid via') }}</th>
                        <th scope="col">{{ __('Payment Status') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Days') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($orders as $order)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $order->id }}">
                          </td>
                          <td>{{ '#' . $order->order_number }}</td>
                          <td class="title">
                            @php
                              $room_content = App\Models\RoomContent::Where([
                                  ['room_id', $order->room_id],
                                  ['language_id', $language->id],
                              ])
                                  ->select('title', 'room_id', 'slug')
                                  ->first();
                            @endphp
                            @if (!empty($room_content))
                              <a href="{{ route('frontend.room.details', ['slug' => $room_content->slug, 'id' => $room_content->room_id]) }}"
                                target="_blank">
                                {{ strlen(@$room_content->title) > 60 ? mb_substr(@$room_content->title, 0, 60, 'utf-8') . '...' : @$room_content->title }}
                              </a>
                            @else
                              --
                            @endif
                          </td>
                          <td>{{ __($order->payment_method) }}</td>
                          <td>
                            @if ($order->gateway_type == 'online')
                              <h2 class="d-inline-block">
                                <span
                                  class="badge badge-{{ $order->payment_status == 'completed' ? 'success' : ($order->payment_status == 'pending' ? 'warning' : 'danger') }}">
                                  {{ __($order->payment_status) }}
                                </span>
                              </h2>
                            @else
                              @if ($order->payment_status == 'pending')
                                <form id="paymentStatusForm-{{ $order->id }}" class="d-inline-block"
                                  action="{{ route('admin.room_management.featured_room.update_payment_status', ['id' => $order->id]) }}"
                                  method="post">
                                  @csrf
                                  <select
                                    class="form-control form-control-sm @if ($order->payment_status == 'pending') bg-warning text-dark @elseif ($order->payment_status == 'completed') bg-success @else bg-danger @endif"
                                    name="payment_status"
                                    onchange="document.getElementById('paymentStatusForm-{{ $order->id }}').submit()">
                                    <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>
                                      {{ __('Pending') }}
                                    </option>
                                    <option value="completed"
                                      {{ $order->payment_status == 'completed' ? 'selected' : '' }}>
                                      {{ __('Completed') }}
                                    </option>
                                    <option value="rejected"
                                      {{ $order->payment_status == 'rejected' ? 'selected' : '' }}>
                                      {{ __('Rejected') }}
                                    </option>
                                  </select>
                                </form>
                              @else
                                <h2 class="d-inline-block"><span
                                    class="badge badge-{{ $order->payment_status == 'completed' ? 'success' : 'danger' }}">{{ __($order->payment_status) }}</span>
                                </h2>
                              @endif
                            @endif
                          </td>
                          <td>
                            @if ($order->order_status == 'pending')
                              <form id="orderStatusForm-{{ $order->id }}" class="d-inline-block"
                                action="{{ route('admin.room_management.featured_room.update_order_status', ['id' => $order->id]) }}"
                                method="post">
                                @csrf
                                <select
                                  class="form-control form-control-sm @if ($order->order_status == 'pending') bg-warning text-dark @elseif ($order->order_status == 'processing') bg-primary @elseif ($order->order_status == 'apporved') bg-success @else bg-danger @endif"
                                  name="order_status"
                                  onchange="document.getElementById('orderStatusForm-{{ $order->id }}').submit()">
                                  <option value="pending" selected>{{ __('Pending') }}</option>
                                  <option value="apporved">{{ __('Approved') }}</option>
                                  <option value="rejected">{{ __('Rejected') }}</option>
                                </select>
                              </form>
                            @else
                              <h2 class="d-inline-block"><span
                                  class="badge badge-{{ $order->order_status == 'apporved' ? 'success' : 'danger' }}">
                                  {{ __($order->order_status) }}
                                </span>
                              </h2>
                            @endif
                          </td>
                          <td>
                            @if ($order->order_status == 'apporved')
                              {{ $order->days }} {{ __('days') }}
                              ({{ \Carbon\Carbon::parse($order->start_date)->format('j F, Y') }} -
                              {{ \Carbon\Carbon::parse($order->end_date)->format('j F, Y') }})
                            @else
                              {{ $order->days }}
                            @endif
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                {{ __('Select') }}
                              </button>

                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                @if (!empty($order->attachment))
                                  <a href="#" class="dropdown-item" data-toggle="modal"
                                    data-target="#receiptModal-{{ $order->id }}">
                                    {{ __('Receipt') }}
                                  </a>
                                @endif

                                <a href="#" class="dropdown-item" data-toggle="modal"
                                  data-target="#detailsModal_{{ $order->id }}">
                                  {{ __('Details') }}
                                </a>

                                @if ($order->invoice)
                                  <a href="{{ asset('assets/file/invoices/room-feature/' . $order->invoice) }}"
                                    class="dropdown-item" target="_blank">
                                    {{ __('Invoice') }}
                                  </a>
                                @endif


                                <form class="deleteForm d-block"
                                  action="{{ route('admin.room_management.featured_room.delete', ['id' => $order->id]) }}"
                                  method="post">
                                  @csrf
                                  <button type="submit" class="deleteBtn">
                                    {{ __('Delete') }}
                                  </button>
                                </form>
                              </div>
                            </div>
                          </td>
                        </tr>
                        @includeIf('admin.room-management.featured-room.show-receipt')
                        @includeIf('admin.room-management.featured-room.details')
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="mt-3 text-center">
            <div class="d-inline-block mx-auto">
              {{ $orders->appends([
                      'order_no' => request()->input('order_no'),
                      'payment_status' => request()->input('payment_status'),
                      'order_status' => request()->input('order_status'),
                      'title' => request()->input('title'),
                      'language' => request()->input('language'),
                  ])->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
