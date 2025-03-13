@extends('vendors.layout')
@php
  Config::set('app.timezone', App\Models\BasicSettings\Basic::first()->timezone);
@endphp

@php
  $vendor = Auth::guard('vendor')->user();
  $package = \App\Http\Helpers\VendorPermissionHelper::currentPackagePermission($vendor->id);
@endphp
@section('content')
  @if (is_null($package))
    @php
      $pendingMemb = \App\Models\Membership::query()
          ->where([['vendor_id', '=', $vendor->id], ['status', 0]])
          ->whereYear('start_date', '<>', '9999')
          ->orderBy('id', 'DESC')
          ->first();
      $pendingPackage = isset($pendingMemb) ? \App\Models\Package::query()->findOrFail($pendingMemb->package_id) : null;
    @endphp

    @if ($pendingPackage)
      <div class="alert alert-warning text-dark">
        {{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken.') }}
      </div>
      <div class="alert alert-warning text-dark">
        <strong>{{ __('Pending Package') . ':' }} </strong> {{ $pendingPackage->title }}
        <span class="badge badge-secondary">{{ $pendingPackage->term }}</span>
        <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
      </div>
    @else
      @php
        $newMemb = \App\Models\Membership::query()
            ->where([['vendor_id', '=', Auth::id()], ['status', 0]])
            ->first();
      @endphp
      @if ($newMemb)
        <div class="alert alert-warning text-dark">
          {{ __('Your membership is expired. Please purchase a new package / extend the current package.') }}
        </div>
      @endif
      <div class="alert alert-warning text-dark">
        {{ __('Please purchase a new package') . '.' }}
      </div>
    @endif
  @else
    <div class="row justify-content-center align-items-center mb-1">
      <div class="col-12">
        <div class="alert border-left border-primary text-dark">
          @if ($package_count >= 2 && $next_membership)
            @if ($next_membership->status == 0)
              <strong
                class="text-danger">{{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken.') }}</strong><br>
            @elseif ($next_membership->status == 1)
              <strong
                class="text-danger">{{ __('You have another package to activate after the current package expires. You cannot purchase / extend any package, until the next package is activated') }}</strong><br>
            @endif
          @endif

          <strong>{{ __('Current Package') . ':' }} </strong> {{ $current_package->title }}
          <span class="badge badge-secondary">{{ __($current_package->term) }}</span>
          @if ($current_membership->is_trial == 1)
            ({{ __('Expire Date') . ':' }}
            {{ Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
            <span class="badge badge-primary">{{ __('Trial') }}</span>
          @else
            ({{ __('Expire Date') . ':' }}
            {{ $current_package->term === 'lifetime' ? __('Lifetime') : Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
          @endif

          @if ($package_count >= 2 && $next_package) 
            <div>
              <strong>{{ __('Next Package To Activate') . ':' }} </strong> {{ $next_package->title }} <span
                class="badge badge-secondary">{{ __($next_package->term) }}</span>
              @if ($current_package->term != 'lifetime' && $current_membership->is_trial != 1)
                (
                {{ __('Activation Date') . ':' }}
                {{ Carbon\Carbon::parse($next_membership->start_date)->format('M-d-Y') }},
                {{ __('Expire Date') . ':' }}
                {{ $next_package->term === 'lifetime' ? __('Lifetime') : Carbon\Carbon::parse($next_membership->expire_date)->format('M-d-Y') }})
              @endif
              @if ($next_membership->status == 0)
                <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif
  <div class="row mb-5 justify-content-center">
    @foreach ($packages as $key => $package)
      @php
        $permissions = $package->features;
        if (!empty($package->features)) {
            $permissions = json_decode($permissions, true);
        }
      @endphp

      <div class="col-md-3 pr-md-0 mb-5">
        <div class="card-pricing2 @if (isset($current_package->id) && $current_package->id === $package->id) card-success @else card-primary @endif">
          <div class="pricing-header">
            <h3 class="fw-bold d-inline-block">
              {{ $package->title }}
            </h3>
            @if (isset($current_package->id) && $current_package->id === $package->id)
              <h3 class="badge badge-danger d-inline-block float-right ml-2">{{ __('Current') }}</h3>
            @endif
            @if ($package_count >= 2)
              @if ($next_package)
                @if ($next_package->id == $package->id)
                  <h3 class="badge badge-warning d-inline-block float-right ml-2">{{ __('Next') }}</h3>
                @endif
              @endif
            @endif
            <span class="sub-title"></span>
          </div>
          <div class="price-value">
            <div class="value">
              <span class="amount">{{ $package->price == 0 ? 'Free' : format_price($package->price) }}</span>
              @if ($package->term == 'monthly')
                <span class="month">/ {{ __('Monthly') }}</span>
              @elseif($package->term == 'yearly')
                <span class="month">/ {{ __('Yearly') }}</span>
              @elseif($package->term == 'lifetime')
                <span class="month">/ {{ __('Lifetime') }}</span>
              @endif

            </div>
          </div>

          <ul class="pricing-content">
            <li>
              @if ($package->number_of_hotel == 999999)
                {{ __('Hotel (Unlimited)') }}
              @elseif($package->number_of_hotel == 1)
                {{ __('Hotel') }} ({{ $package->number_of_hotel }})
              @else
                {{ __('Hotels') }}({{ $package->number_of_hotel }})
              @endif
            </li>

            <li>
              @if ($package->number_of_room == 999999)
                {{ __('Room (Unlimited)') }}
              @elseif($package->number_of_room == 1)
                {{ __('Room') }} ({{ $package->number_of_room }})
              @else
                {{ __('Rooms') }}({{ $package->number_of_room }})
              @endif
            </li>

            <li>
              @if ($package->number_of_amenities_per_hotel == 999999)
                {{ __('Amenitie Per Hotel (Unlimited)') }}
              @elseif($package->number_of_amenities_per_hotel == 1)
                {{ __('Amenitie Per Hotel') }} ({{ $package->number_of_amenities_per_hotel }})
              @else
                {{ __('Amenities Per Hotel') }}({{ $package->number_of_amenities_per_hotel }})
              @endif
            </li>

            <li>
              @if ($package->number_of_amenities_per_room == 999999)
                {{ __('Amenitie Per Room (Unlimited)') }}
              @elseif($package->number_of_amenities_per_room == 1)
                {{ __('Amenitie Per Room') }} ({{ $package->number_of_amenities_per_room }})
              @else
                {{ __('Amenities Per Room') }}({{ $package->number_of_amenities_per_room }})
              @endif
            </li>

            <li>
              @if ($package->number_of_images_per_hotel == 999999)
                {{ __('Image Per Hotel (Unlimited)') }}
              @elseif($package->number_of_images_per_hotel == 1)
                {{ __('Image Per Hotel') }} ({{ $package->number_of_images_per_hotel }})
              @else
                {{ __('Images Per Hotel') }}({{ $package->number_of_images_per_hotel }})
              @endif
            </li>

            <li>
              @if ($package->number_of_images_per_room == 999999)
                {{ __('Image Per Room (Unlimited)') }}
              @elseif($package->number_of_images_per_room == 1)
                {{ __('Image Per Room') }} ({{ $package->number_of_images_per_room }})
              @else
                {{ __('Images Per Room') }}({{ $package->number_of_images_per_room }})
              @endif
            </li>
            <li>
              @if ($package->number_of_bookings == 999999)
                {{ __('Number of Booking (Unlimited)') }}
              @elseif($package->number_of_bookings == 1)
                {{ __('Number of Booking') }} ({{ $package->number_of_bookings }})
              @else
                {{ __('Number of Bookings') }}({{ $package->number_of_bookings }})
              @endif
            </li>

            <li class="@if (is_array($permissions) && !in_array('Add Booking From Dashboard', $permissions)) disable @endif">{{ __('Add Booking From Dashboard') }}
            </li>
            <li class="@if (is_array($permissions) && !in_array('Edit Booking From Dashboard', $permissions)) disable @endif">{{ __('Edit Booking From Dashboard') }}
            </li>
            <li class="@if (is_array($permissions) && !in_array('Support Tickets', $permissions)) disable @endif">{{ __('Support Tickets') }}
            </li>

            @if (!is_null($package->custom_features))
              @php
                $features = explode("\n", $package->custom_features);
              @endphp
              @if (count($features) > 0)
                @foreach ($features as $key => $value)
                  <li>{{ $value }}</li>
                @endforeach
              @endif
            @endif
          </ul>

          @php
            $hasPendingMemb = \App\Http\Helpers\VendorPermissionHelper::hasPendingMembership(Auth::id());
          @endphp
          @if ($package_count < 2 && !$hasPendingMemb)
            <div class="px-4">
              @if (isset($current_package->id) && $current_package->id === $package->id)
                @if ($package->term != 'lifetime' || $current_membership->is_trial == 1)
                  <a href="{{ route('vendor.plan.extend.checkout', $package->id) }}"
                    class="btn btn-success btn-lg w-75 fw-bold mb-3">{{ __('Extend') }}</a>
                @endif
              @else
                <a href="{{ route('vendor.plan.extend.checkout', $package->id) }}"
                  class="btn btn-primary btn-block btn-lg fw-bold mb-3">{{ __('Buy Now') }}</a>
              @endif
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
@endsection
