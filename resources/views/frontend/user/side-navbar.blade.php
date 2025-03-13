<div class="col-lg-3">
  <aside class="widget-area mb-40">
    <div class="widget radius-md">
      <ul class="links">

        <li><a href="{{ route('user.dashboard') }}"
            class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">{{ __('Dashboard') }}</a></li>
        <li><a href="{{ route('user.room_bookings') }}"
            class="{{ request()->routeIs('user.room_bookings') || request()->routeIs('user.room_booking_details') ? 'active' : '' }}">{{ __('Room Booking') }}
          </a></li>
        <li
          class="{{ request()->routeIs('user.wishlist.hotel') || request()->routeIs('user.wishlist.room') ? 'active' : '' }}">
          <a data-bs-toggle="collapse" href="#collapseExample" class="sidebar-link-item" aria-expanded="false"
            aria-controls="collapseExample">{{ __('My Wishlist') }}
            <span class="caret"></span>
          </a>
          <ul
            class="collapse sidebar-link-item-submenu {{ request()->routeIs('user.wishlist.hotel') || request()->routeIs('user.wishlist.room') ? 'show' : '' }}"
            id="collapseExample">
            <li><a href="{{ route('user.wishlist.hotel') }}"
                class="{{ request()->routeIs('user.wishlist.hotel') ? 'active' : '' }}">{{ __('Hotels') }}</a></li>
            <li><a href="{{ route('user.wishlist.room') }}"
                class="{{ request()->routeIs('user.wishlist.room') ? 'active' : '' }}">{{ __('Rooms') }}</a></li>
          </ul>
        </li>

        <li><a href="{{ route('user.change_password') }}"
            class="{{ request()->routeIs('user.change_password') ? 'active' : '' }}">{{ __('Change Password') }} </a>
        </li>
        <li><a href="{{ route('user.edit_profile') }}"
            class="{{ request()->routeIs('user.edit_profile') ? 'active' : '' }}">{{ __('Edit Profile') }} </a></li>
        <li><a href="{{ route('user.logout') }}">{{ __('Logout') }} </a></li>
      </ul>
    </div>
  </aside>
</div>
