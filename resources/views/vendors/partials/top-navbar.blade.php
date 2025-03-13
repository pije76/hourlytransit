<div class="main-header">
  <!-- Logo Header Start -->
  <div class="logo-header"
    data-background-color="{{ Auth::guard('vendor')->user()->vendor_theme_version == 'light' ? 'white' : 'dark2' }}">

    @if (!empty($websiteInfo->logo))
      <a href="{{ route('index') }}" class="logo" target="_blank">
        <img src="{{ asset('assets/img/' . $websiteInfo->logo) }}" alt="logo" class="navbar-brand" width="120">
      </a>
    @endif

    <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon">
        <i class="icon-menu"></i>
      </span>
    </button>
    <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>

    <div class="nav-toggle">
      <button class="btn btn-toggle toggle-sidebar">
        <i class="icon-menu"></i>
      </button>
    </div>
  </div>
  <!-- Logo Header End -->

  <!-- Navbar Header Start -->
  <nav class="navbar navbar-header navbar-expand-lg"
    data-background-color="{{ Auth::guard('vendor')->user()->vendor_theme_version == 'light' ? 'white2' : 'dark' }}">
    <div class="container-fluid">
      <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
        <li class="mr-2">
          @if (!empty($langs))
            <select name="language"
              class="form-control {{ Route::currentRouteName() == 'vendor.room_bookings.booking_form' ? 'langBtn2' : 'langBtn' }}">
              <option value="" selected disabled>{{ __('Select a Language') }}</option>
              @foreach ($langs as $lang)
                <option value="{{ $lang->code }}"
                  {{ $lang->code == Auth::guard('vendor')->user()->code ? 'selected' : '' }}>
                  {{ $lang->name }}
                </option>
              @endforeach
            </select>
          @endif
        </li>
        <input type="hidden" id="setLocale" value="{{ route('set-locale-vendor') }}">
        @php
          $vendorId = Auth::guard('vendor')->user()->id;
          $current_packages = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);

        @endphp
        @if ($current_packages != '[]')

          @php
            $vendorId = Auth::guard('vendor')->user()->id;

            if ($vendorId) {
                $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);
                if ($current_package) {
                    $hotelCanAdd = packageTotalHotel($vendorId) - vendorTotalAddedHotel($vendorId);
                    $roomCanAdd = packageTotalRoom($vendorId) - vendorTotalAddedRoom($vendorId);

                    if (!empty($current_package) && !empty($current_package->features)) {
                        $permissions = json_decode($current_package->features, true);
                    } else {
                        $permissions = null;
                        $additionalFeatureLimit = 0;
                        $SocialLinkLimit = 0;
                    }
                }
            } else {
                $permissions = null;
                $additionalFeatureLimit = 0;
                $SocialLinkLimit = 0;
            }
          @endphp
          <li>
            <button type="button" class="btn  btn-secondary mr-2  btn-sm btn-round" id="aa" data-toggle="modal"
              data-target="#checkLimitModal">
              @if ($hotelImgDown || $hotelAmenitieDown || $roomAmenitieDown || $roomImgDown || $hotelCanAdd < 0 || $roomCanAdd < 0)
                <i class="fas fa-exclamation-triangle text-danger"></i>
              @endif
              {{ __('Check Limit') }}
            </button>
          </li>
        @endif

        <form action="{{ route('vendor.change_theme') }}" class="form-inline mr-3" method="POST">
          @csrf
          <div class="form-group">
            <div class="selectgroup selectgroup-secondary selectgroup-pills">
              <label class="selectgroup-item">
                <input type="radio" name="vendor_theme_version" value="light" class="selectgroup-input"
                  {{ Auth::guard('vendor')->user()->vendor_theme_version == 'light' ? 'checked' : '' }}
                  onchange="this.form.submit()">
                <span class="selectgroup-button selectgroup-button-icon"><i class="fa fa-sun"></i></span>
              </label>

              <label class="selectgroup-item">
                <input type="radio" name="vendor_theme_version" value="dark" class="selectgroup-input"
                  {{ Auth::guard('vendor')->user()->vendor_theme_version == 'dark' || Auth::guard('vendor')->user()->vendor_theme_version == '' ? 'checked' : '' }}
                  onchange="this.form.submit()">
                <span class="selectgroup-button selectgroup-button-icon"><i class="fa fa-moon"></i></span>
              </label>
            </div>
          </div>
        </form>


        <li class="nav-item dropdown hidden-caret">
          <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
            <div class="avatar-sm">
              @if (Auth::guard('vendor')->user()->photo != null)
                <img src="{{ asset('assets/admin/img/vendor-photo/' . Auth::guard('vendor')->user()->photo) }}"
                  alt="Vendor Image" class="avatar-img rounded-circle">
              @else
                <img src="{{ asset('assets/img/blank-user.jpg') }}" alt="" class="avatar-img rounded-circle">
              @endif
            </div>
          </a>

          <ul class="dropdown-menu dropdown-user animated fadeIn">
            <div class="dropdown-user-scroll scrollbar-outer">
              <li>
                <div class="user-box">
                  <div class="avatar-lg">
                    @if (Auth::guard('vendor')->user()->photo != null)
                      <img src="{{ asset('assets/admin/img/vendor-photo/' . Auth::guard('vendor')->user()->photo) }}"
                        alt="Vendor Image" class="avatar-img rounded-circle">
                    @else
                      <img src="{{ asset('assets/img/blank-user.jpg') }}" alt=""
                        class="avatar-img rounded-circle">
                    @endif
                  </div>

                  <div class="u-text">
                    <h4>
                      {{ Auth::guard('vendor')->user()->username }}
                    </h4>
                    <p class="text-muted">{{ Auth::guard('vendor')->user()->email }}</p>
                  </div>
                </div>
              </li>

              <li>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('vendor.edit.profile') }}">
                  {{ __('Edit Profile') }}
                </a>

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('vendor.change_password') }}">
                  {{ __('Change Password') }}
                </a>

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('vendor.logout') }}">
                  {{ __('Logout') }}
                </a>
              </li>
            </div>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
  <!-- Navbar Header End -->
</div>
