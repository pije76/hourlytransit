<div class="sidebar sidebar-style-2"
  data-background-color="{{ Auth::guard('vendor')->user()->vendor_theme_version == 'light' ? 'white' : 'dark2' }}">
  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <div class="user">
        <div class="avatar-sm float-left mr-2">
          @if (Auth::guard('vendor')->user()->photo != null)
            <img src="{{ asset('assets/admin/img/vendor-photo/' . Auth::guard('vendor')->user()->photo) }}"
              alt="Vendor Image" class="avatar-img rounded-circle">
          @else
            <img src="{{ asset('assets/img/blank-user.jpg') }}" alt="" class="avatar-img rounded-circle">
          @endif
        </div>

        <div class="info">
          <a data-toggle="collapse" href="#adminProfileMenu" aria-expanded="true">
            <span>
              {{ Auth::guard('vendor')->user()->username }}
              <span class="user-level">{{ __('Vendor') }}</span>
              <span class="caret"></span>
            </span>
          </a>

          <div class="clearfix"></div>

          <div class="collapse in" id="adminProfileMenu">
            <ul class="nav">
              <li>
                <a href="{{ route('vendor.edit.profile') }}">
                  <span class="link-collapse">{{ __('Edit Profile') }}</span>
                </a>
              </li>

              <li>
                <a href="{{ route('vendor.change_password') }}">
                  <span class="link-collapse">{{ __('Change Password') }}</span>
                </a>
              </li>

              <li>
                <a href="{{ route('vendor.logout') }}">
                  <span class="link-collapse">{{ __('Logout') }}</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>


      <ul class="nav nav-primary">
        {{-- search --}}
        <div class="row mb-3">
          <div class="col-12">
            <form>
              <div class="form-group py-0">
                <input name="term" type="text" class="form-control sidebar-search ltr"
                  placeholder="{{ __('Search Menu Here...') }}">
              </div>
            </form>
          </div>
        </div>

        {{-- dashboard --}}
        <li class="nav-item @if (request()->routeIs('vendor.dashboard')) active @endif">
          <a href="{{ route('vendor.dashboard') }}">
            <i class="la flaticon-paint-palette"></i>
            <p>{{ __('Dashboard') }}</p>
          </a>
        </li>

        {{-- Hotels Management --}}
        <li
          class="nav-item @if (request()->routeIs('vendor.hotel_management.hotels')) active 
           @elseif (request()->routeIs('vendor.hotel_management.create_hotel')) active 
           @elseif (request()->routeIs('vendor.hotel_management.hotel.holiday')) active 
            @elseif (request()->routeIs('vendor.hotel_management.edit_hotel')) active 
            @elseif (request()->routeIs('vendor.hotel_management.manage_counter_section')) active @endif">
          <a data-toggle="collapse" href="#hotelManagement">
            <i class="fas fa-building"></i>
            <p>{{ __('Hotels Management') }}</p>
            <span class="caret"></span>
          </a>

          <div id="hotelManagement"
            class="collapse 
              @if (request()->routeIs('vendor.hotel_management.hotels')) show 
              @elseif (request()->routeIs('vendor.hotel_management.create_hotel')) show 
              @elseif (request()->routeIs('vendor.hotel_management.edit_hotel')) show 
              @elseif (request()->routeIs('vendor.hotel_management.hotel.holiday')) show 
              @elseif (request()->routeIs('vendor.hotel_management.manage_counter_section')) show @endif">
            <ul class="nav nav-collapse">

              <li class="{{ request()->routeIs('vendor.hotel_management.create_hotel') ? 'active' : '' }}">
                <a href="{{ route('vendor.hotel_management.create_hotel', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Add Hotel') }}</span>
                </a>
              </li>

              <li
                class=" @if (request()->routeIs('vendor.hotel_management.hotels')) active
                   @elseif (request()->routeIs('vendor.hotel_management.edit_hotel')) active 
                   @elseif (request()->routeIs('vendor.hotel_management.manage_counter_section')) active @endif">
                <a href="{{ route('vendor.hotel_management.hotels', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Manage Hotels') }}</span>
                </a>
              </li>

              <li class=" @if (request()->routeIs('vendor.hotel_management.hotel.holiday')) active @endif">
                <a href="{{ route('vendor.hotel_management.hotel.holiday', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Holidays') }}</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        {{-- End Hotels Management --}}

        {{-- ROOMS management --}}
        <li
          class="nav-item @if (request()->routeIs('vendor.room_management.rooms')) active 
           @elseif (request()->routeIs('vendor.room_management.create_room')) active 
           @elseif (request()->routeIs('vendor.room_management.coupons')) active 
           @elseif (request()->routeIs('vendor.room_management.manage_additional_service')) active 
            @elseif (request()->routeIs('vendor.room_management.edit_room')) active @endif">
          <a data-toggle="collapse" href="#roomManagement">
            <i class="fas fa-building"></i>
            <p>{{ __('Rooms Management') }}</p>
            <span class="caret"></span>
          </a>

          <div id="roomManagement"
            class="collapse 
              @if (request()->routeIs('vendor.room_management.rooms')) show 
              @elseif (request()->routeIs('vendor.room_management.create_room')) show 
              @elseif (request()->routeIs('vendor.room_management.coupons')) show 
              @elseif (request()->routeIs('vendor.room_management.manage_additional_service')) show 
              @elseif (request()->routeIs('vendor.room_management.edit_room')) show @endif">
            <ul class="nav nav-collapse">

              <li class="{{ request()->routeIs('vendor.room_management.coupons') ? 'active' : '' }}">
                <a href="{{ route('vendor.room_management.coupons', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Coupons') }}</span>
                </a>
              </li>

              <li class="{{ request()->routeIs('vendor.room_management.create_room') ? 'active' : '' }}">
                <a href="{{ route('vendor.room_management.create_room', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Add Room') }}</span>
                </a>
              </li>

              <li
                class=" @if (request()->routeIs('vendor.room_management.rooms')) active
                   @elseif (request()->routeIs('vendor.room_management.edit_room')) active
                   @elseif (request()->routeIs('vendor.room_management.manage_additional_service')) active @endif">
                <a href="{{ route('vendor.room_management.rooms', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Manage Rooms') }}</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        {{-- End Rooms management --}}

        {{-- ROOM BOOKINGS --}}
        <li
          class="nav-item @if (request()->routeIs('vendor.room_bookings.all_bookings')) active
            @elseif (request()->routeIs('vendor.room_bookings.paid_bookings')) active
            @elseif (request()->routeIs('vendor.room_bookings.unpaid_bookings')) active
            @elseif (request()->routeIs('vendor.room_bookings.booking_details_and_edit')) active
            @elseif (request()->routeIs('vendor.room_bookings.booking_details')) active
            @elseif (request()->routeIs('vendor.room_bookings.booking_form')) active @endif">
          <a data-toggle="collapse" href="#roomBookings">
            <i class="far fa-calendar-check"></i>
            <p class="pr-2">{{ __('Room Bookings') }}</p>
            <span class="caret"></span>
          </a>
          <div id="roomBookings"
            class="collapse
              @if (request()->routeIs('vendor.room_bookings.all_bookings')) show
              @elseif (request()->routeIs('vendor.room_bookings.paid_bookings')) show
              @elseif (request()->routeIs('vendor.room_bookings.unpaid_bookings')) show
              @elseif (request()->routeIs('vendor.room_bookings.booking_details')) show
              @elseif (request()->routeIs('vendor.room_bookings.booking_details_and_edit')) show
              @elseif (request()->routeIs('vendor.room_bookings.booking_form')) show @endif">
            <ul class="nav nav-collapse">
              <li class="{{ request()->routeIs('vendor.room_bookings.all_bookings') ? 'active' : '' }}">
                <a href="{{ route('vendor.room_bookings.all_bookings', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('All Bookings') }}</span>
                </a>
              </li>
              <li class="{{ request()->routeIs('vendor.room_bookings.paid_bookings') ? 'active' : '' }}">
                <a href="{{ route('vendor.room_bookings.paid_bookings', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Paid Bookings') }}</span>
                </a>
              </li>
              <li class="{{ request()->routeIs('vendor.room_bookings.unpaid_bookings') ? 'active' : '' }}">
                <a href="{{ route('vendor.room_bookings.unpaid_bookings', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Unpaid Bookings') }}</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        {{-- END ROOM BOOKINGS --}}


        {{-- withdraw --}}

        <li
          class="nav-item @if (request()->routeIs('vendor.withdraw')) active 
            @elseif (request()->routeIs('vendor.withdraw.create'))  active @endif">
          <a data-toggle="collapse" href="#Withdrawals">
            <i class="fal fa-donate"></i>
            <p>{{ __('Withdrawals') }}</p>
            <span class="caret"></span>
          </a>

          <div id="Withdrawals"
            class="collapse 
              @if (request()->routeIs('vendor.withdraw')) show 
              @elseif (request()->routeIs('vendor.withdraw.create')) show @endif">
            <ul class="nav nav-collapse">
              <li class="{{ request()->routeIs('vendor.withdraw') ? 'active' : '' }}">
                <a href="{{ route('vendor.withdraw', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Withdrawal Requests') }}</span>
                </a>
              </li>

              <li class="{{ request()->routeIs('vendor.withdraw.create') ? 'active' : '' }}">
                <a href="{{ route('vendor.withdraw.create', ['language' => $defaultLang->code]) }}">
                  <span class="sub-item">{{ __('Make a Request') }}</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        {{-- Transaction --}}
        <li class="nav-item @if (request()->routeIs('vendor.transcation')) active @endif">
          <a href="{{ route('vendor.transcation', ['language' => $defaultLang->code]) }}">
            <i class="fal fa-exchange-alt"></i>
            <p>{{ __('Transactions') }}</p>
          </a>
        </li>

        <li class="nav-item  @if (request()->routeIs('vendor.email_setting.mail_to_admin')) active @endif">
          <a href="{{ route('vendor.email_setting.mail_to_admin') }}">
            <i class="far fa-envelope"></i>
            <p>{{ __('Recipient Mail') }}</p>
          </a>
        </li>

        <li
          class="nav-item 
        @if (request()->routeIs('vendor.plan.extend.index')) active 
        @elseif (request()->routeIs('vendor.plan.extend.checkout')) active @endif">
          <a href="{{ route('vendor.plan.extend.index') }}">
            <i class="fal fa-lightbulb-dollar"></i>
            <p>{{ __('Buy Plan') }}</p>
          </a>
        </li>

        <li class="nav-item @if (request()->routeIs('vendor.payment_log')) active @endif">
          <a href="{{ route('vendor.payment_log') }}">
            <i class="fas fa-list-ol"></i>
            <p>{{ __('Subscription Log') }}</p>
          </a>
        </li>

        {{-- Support Ticket --}}
        @php
          $support_status = DB::table('support_ticket_statuses')->first();
          $vendorId = Auth::guard('vendor')->user()->id;
          $supportTicketsPermission = supportTicketsPermission($vendorId);
        @endphp
        @if ($support_status->support_ticket_status == 'active')
          @if ($supportTicketsPermission)
            <li
              class="nav-item @if (request()->routeIs('vendor.support_tickets')) active
            @elseif (request()->routeIs('vendor.support_tickets.message')) active
            @elseif (request()->routeIs('vendor.support_ticket.create')) active @endif">
              <a data-toggle="collapse" href="#support_ticket">
                <i class="la flaticon-web-1"></i>
                <p>{{ __('Support Tickets') }}</p>
                <span class="caret"></span>
              </a>

              <div id="support_ticket"
                class="collapse
              @if (request()->routeIs('vendor.support_tickets')) show
              @elseif (request()->routeIs('vendor.support_tickets.message')) show
              @elseif (request()->routeIs('vendor.support_ticket.create')) show @endif">
                <ul class="nav nav-collapse">

                  <li
                    class="{{ request()->routeIs('vendor.support_tickets') && empty(request()->input('status')) ? 'active' : '' }}">
                    <a href="{{ route('vendor.support_tickets') }}">
                      <span class="sub-item">{{ __('All Tickets') }}</span>
                    </a>
                  </li>
                  <li class="{{ request()->routeIs('vendor.support_ticket.create') ? 'active' : '' }}">
                    <a href="{{ route('vendor.support_ticket.create') }}">
                      <span class="sub-item">{{ __('Add a Ticket') }}</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>
          @endif

        @endif



        <li class="nav-item @if (request()->routeIs('vendor.edit.profile')) active @endif">
          <a href="{{ route('vendor.edit.profile') }}">
            <i class="fal fa-user-edit"></i>
            <p>{{ __('Edit Profile') }}</p>
          </a>
        </li>
        <li class="nav-item @if (request()->routeIs('vendor.change_password')) active @endif">
          <a href="{{ route('vendor.change_password') }}">
            <i class="fal fa-key"></i>
            <p>{{ __('Change Password') }}</p>
          </a>
        </li>

        <li class="nav-item @if (request()->routeIs('vendor.logout')) active @endif">
          <a href="{{ route('vendor.logout') }}">
            <i class="fal fa-sign-out"></i>
            <p>{{ __('Logout') }}</p>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
