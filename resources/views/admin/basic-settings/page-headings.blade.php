@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Headings') }}</h4>
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
        <a href="#">{{ __('Pages') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Common Sections') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Breadcrumb') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Headings') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('admin.pages.breadcrumb.headings.update', ['language' => request()->input('language')]) }}"
          method="post">
          @csrf
          <div class="card-header">
            <div class="row">
              <div class="col-lg-10">
                <div class="card-title">{{ __('Headings') }}</div>
              </div>
              <div class="col-lg-2">

              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-10 offset-lg-1">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Hotel Page Title') }}</label>
                      <input type="text" class="form-control" name="hotel_page_title"
                        value="{{ $data != null ? $data->hotel_page_title : '' }}">
                      @error('hotel_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Room Page Title') }}</label>
                      <input type="text" class="form-control" name="rooms_page_title"
                        value="{{ $data != null ? $data->rooms_page_title : '' }}">
                      @error('rooms_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Room Checkout Page Title') }}</label>
                      <input type="text" class="form-control" name="room_checkout_page_title"
                        value="{{ $data != null ? $data->room_checkout_page_title : '' }}">
                      @error('room_checkout_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Vendors Page Title') }}</label>
                      <input type="text" class="form-control" name="vendor_page_title"
                        value="{{ $data != null ? $data->vendor_page_title : '' }}">
                      @error('vendor_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Checkout Page Title') }}</label>
                      <input type="text" class="form-control" name="checkout_page_title"
                        value="{{ $data != null ? $data->checkout_page_title : '' }}">
                      @error('checkout_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('User Login Page Title') }}</label>
                      <input type="text" class="form-control" name="login_page_title"
                        value="{{ $data != null ? $data->login_page_title : '' }}">
                      @error('login_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('User Signup Page Title') }}</label>
                      <input type="text" class="form-control" name="signup_page_title"
                        value="{{ $data != null ? $data->signup_page_title : '' }}">
                      @error('signup_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('User Forget Page Title') }}</label>
                      <input type="text" class="form-control" name="forget_password_page_title"
                        value="{{ $data != null ? $data->forget_password_page_title : '' }}">
                      @error('forget_password_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Vendor Login Page Title') }}</label>
                      <input type="text" class="form-control" name="vendor_login_page_title"
                        value="{{ $data != null ? $data->vendor_login_page_title : '' }}">
                      @error('vendor_login_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Vendor Signup Page Title') }}</label>
                      <input type="text" class="form-control" name="vendor_signup_page_title"
                        value="{{ $data != null ? $data->vendor_signup_page_title : '' }}">
                      @error('vendor_signup_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Vendor Forget Password Page Title') }}</label>
                      <input type="text" class="form-control" name="vendor_forget_password_page_title"
                        value="{{ $data != null ? $data->vendor_forget_password_page_title : '' }}">
                      @error('vendor_forget_password_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('404 Error Page Title') }}</label>
                      <input type="text" class="form-control" name="error_page_title"
                        value="{{ $data != null ? $data->error_page_title : '' }}">
                      @error('error_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('About Us Page Title') }}</label>
                      <input type="text" class="form-control" name="about_us_title"
                        value="{{ $data != null ? $data->about_us_title : '' }}">
                      @error('about_us_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Blog Page Title') }}</label>
                      <input type="text" class="form-control" name="blog_page_title"
                        value="{{ $data != null ? $data->blog_page_title : '' }}">
                      @error('blog_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('FAQ Page Title') }}</label>
                      <input type="text" class="form-control" name="faq_page_title"
                        value="{{ $data != null ? $data->faq_page_title : '' }}">
                      @error('faq_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Contact Page Title') }}</label>
                      <input type="text" class="form-control" name="contact_page_title"
                        value="{{ $data != null ? $data->contact_page_title : '' }}">
                      @error('contact_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Room Wishlist Page Title') }}</label>
                      <input type="text" class="form-control" name="room_wishlist_page_title"
                        value="{{ $data != null ? $data->room_wishlist_page_title : '' }}">
                      @error('room_wishlist_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Hotel Wishlist Page Title') }}</label>
                      <input type="text" class="form-control" name="hotel_wishlist_page_title"
                        value="{{ $data != null ? $data->hotel_wishlist_page_title : '' }}">
                      @error('hotel_wishlist_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Dashboard Page Title') }}</label>
                      <input type="text" class="form-control" name="dashboard_page_title"
                        value="{{ $data != null ? $data->dashboard_page_title : '' }}">
                      @error('dashboard_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Room Booking Page Title') }}</label>
                      <input type="text" class="form-control" name="room_bookings_page_title"
                        value="{{ $data != null ? $data->room_bookings_page_title : '' }}">
                      @error('room_bookings_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Room Booking Details Page Title') }}</label>
                      <input type="text" class="form-control" name="room_booking_details_page_title"
                        value="{{ $data != null ? $data->room_booking_details_page_title : '' }}">
                      @error('room_booking_details_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Support Ticket Page Title') }}</label>
                      <input type="text" class="form-control" name="support_ticket_page_title"
                        value="{{ $data != null ? $data->support_ticket_page_title : '' }}">
                      @error('support_ticket_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Support Ticket Create Page Title') }}</label>
                      <input type="text" class="form-control" name="support_ticket_create_page_title"
                        value="{{ $data != null ? $data->support_ticket_create_page_title : '' }}">
                      @error('support_ticket_create_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Change Password Page Title') }}</label>
                      <input type="text" class="form-control" name="change_password_page_title"
                        value="{{ $data != null ? $data->change_password_page_title : '' }}">
                      @error('change_password_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Edit Profile Page Title') }}</label>
                      <input type="text" class="form-control" name="edit_profile_page_title"
                        value="{{ $data != null ? $data->edit_profile_page_title : '' }}">
                      @error('edit_profile_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ __('Pricing Page Title') }}</label>
                      <input type="text" class="form-control" name="pricing_page_title"
                        value="{{ $data != null ? $data->pricing_page_title : '' }}">
                      @error('pricing_page_title')
                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                  <!-- additional page input -->
                  @if (count($pages) > 0)
                    @foreach ($pages as $page)
                      @php
                        $pageContent = App\Models\CustomPage\PageContent::where('page_id', $page->id)
                            ->where('language_id', $defaultLang->id)
                            ->first();
                      @endphp
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>{{ @$pageContent->title }}</label>
                          <input type="text" class="form-control" name="custom_page_heading[{{ $page->id }}]"
                            value="{{ isset($decodedHeadings[$page->id]) ? $decodedHeadings[$page->id] : '' }}">
                          @error('custom_page_heading.' . $page->id)
                            <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                          @enderror
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-success">
                  {{ __('Update') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
