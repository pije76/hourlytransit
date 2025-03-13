<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/

Route::get('/set-locale-admin', 'Admin\BasicSettings\BasicController@setLocaleAdmin')->name('set-Locale-admin');
Route::prefix('/admin')->middleware('auth:admin', 'Demo', 'adminLang')->group(function () {

  // change admin-panel theme (dark/light) route
  Route::get('/change-theme', 'Admin\AdminController@changeTheme')->name('admin.change_theme');

  // admin redirect to dashboard route
  Route::get('/dashboard', 'Admin\AdminController@redirectToDashboard')->name('admin.dashboard');
  Route::get('/membership-request', 'Admin\AdminController@membershipRequest')->name('admin.membership-request');
  Route::post('/membership-request/update/{id}', 'Admin\AdminController@membershipRequestUpdate')->name('admin.payment-log.update');
  Route::get('/transcation', 'Admin\AdminController@transcation')->middleware('permission:Transaction')->name('admin.transcation');

  // admin profile settings route start
  Route::get('/edit-profile', 'Admin\AdminController@editProfile')->name('admin.edit_profile');

  Route::post('/update-profile', 'Admin\AdminController@updateProfile')->name('admin.update_profile');

  Route::get('/change-password', 'Admin\AdminController@changePassword')->name('admin.change_password');

  Route::post('/update-password', 'Admin\AdminController@updatePassword')->name('admin.update_password');
  // admin profile settings route end

  // admin logout attempt route
  Route::get('/logout', 'Admin\AdminController@logout')->name('admin.logout');

  // menu-builder route start
  Route::prefix('/menu-builder')->middleware('permission:Menu Builder')->group(function () {
    Route::get('', 'Admin\MenuBuilderController@index')->name('admin.menu_builder');

    Route::post('/update-menus', 'Admin\MenuBuilderController@update')->name('admin.menu_builder.update_menus');
  });
  // menu-builder route end

  // withdraw route start

  Route::prefix('withdraw')->middleware('permission:Withdrawals Management')->group(function () {
    Route::get('/payment-methods', 'Admin\WithdrawPaymentMethodController@index')->name('admin.withdraw.payment_method');
    Route::post('/payment-methods/store', 'Admin\WithdrawPaymentMethodController@store')->name('admin.withdraw_payment_method.store');
    Route::put('/payment-methods/update', 'Admin\WithdrawPaymentMethodController@update')->name('admin.withdraw_payment_method.update');
    Route::post('/payment-methods/delete/{id}', 'Admin\WithdrawPaymentMethodController@destroy')->name('admin.withdraw_payment_method.delete');

    Route::get('/payment-method/input', 'Admin\WithdrawPaymentMethodInputController@index')->name('admin.withdraw_payment_method.mange_input');
    Route::post('/payment-method/input-store', 'Admin\WithdrawPaymentMethodInputController@store')->name('admin.withdraw_payment_method.store_input');
    Route::get('/payment-method/input-edit/{id}', 'Admin\WithdrawPaymentMethodInputController@edit')->name('admin.withdraw_payment_method.edit_input');
    Route::get('/payment-method/input-edit/{id}', 'Admin\WithdrawPaymentMethodInputController@edit')->name('admin.withdraw_payment_method.edit_input');
    Route::post('/payment-method/input-update', 'Admin\WithdrawPaymentMethodInputController@update')->name('admin.withdraw_payment_method.update_input');
    Route::post('/payment-method/order-update', 'Admin\WithdrawPaymentMethodInputController@order_update')->name('admin.withdraw_payment_method.order_update');
    Route::get('/payment-method/input-option/{id}', 'Admin\WithdrawPaymentMethodInputController@get_options')->name('admin.withdraw_payment_method.options');
    Route::post('/payment-method/input-delete', 'Admin\WithdrawPaymentMethodInputController@delete')->name('admin.withdraw_payment_method.options_delete');

    Route::get(
      '/withdraw-request',
      'Admin\WithdrawController@index'
    )->name('admin.withdraw.withdraw_request');
    Route::post('/withdraw-request/delete', 'Admin\WithdrawController@delete')->name('admin.witdraw.delete_withdraw');
    Route::get('/withdraw-request/approve/{id}', 'Admin\WithdrawController@approve')->name('admin.witdraw.approve_withdraw');

    Route::get('/withdraw-request/decline/{id}', 'Admin\WithdrawController@decline')->name('admin.witdraw.decline_withdraw');
  });

  //withdraw route end

  // Payment Log
  Route::get('/subscription-log', 'Admin\PaymentLogController@index')->middleware('permission:Payment Log')->name('admin.subscription-log');
  Route::post('/payment-log/update', 'Admin\PaymentLogController@update')->name('admin.payment-log.update');

  Route::prefix('packages')->middleware('permission:Packages Management')->group(function () {
    // Package Settings routes
    Route::get('/settings', 'Admin\PackageController@settings')->name('admin.package.settings');
    Route::post('/settings', 'Admin\PackageController@updateSettings')->name('admin.package.settings');
    // Package routes

    Route::get('', 'Admin\PackageController@index')->name('admin.package.index');
    Route::post('/store', 'Admin\PackageController@store')->name('admin.package.store');
    Route::get('/{id}/edit', 'Admin\PackageController@edit')->name('admin.package.edit');

    Route::post('/update', 'Admin\PackageController@update')->name('admin.package.update');
    Route::post('package/upload', 'Admin\PackageController@upload')->name('admin.package.upload');

    Route::post('package/{id}/uploadUpdate', 'Admin\PackageController@uploadUpdate')->name('admin.package.uploadUpdate');
    Route::post('package/delete', 'Admin\PackageController@delete')->name('admin.package.delete');
    Route::post('package/bulk-delete', 'Admin\PackageController@bulkDelete')->name('admin.package.bulk.delete');
  });

  // Room Bookings Routes
  Route::prefix('room-bookings')->middleware('permission:Room Bookings')->group(function () {

    Route::get('/all-bookings', 'Admin\RoomBookingController@index')->name('admin.room_bookings.all_bookings');

    Route::get('/paid-bookings', 'Admin\RoomBookingController@index')->name('admin.room_bookings.paid_bookings');

    Route::get('/unpaid-bookings', 'Admin\RoomBookingController@index')->name('admin.room_bookings.unpaid_bookings');

    Route::post('/update-payment-status', 'Admin\RoomBookingController@updatePaymentStatus')->name('admin.room_bookings.update_payment_status');

    Route::get('/booking-details-and-edit/{id}', 'Admin\RoomBookingController@editBookingDetails')->name('admin.room_bookings.booking_details_and_edit');

    Route::get('/booking-details/{id}', 'Admin\RoomBookingController@details')->name('admin.room_bookings.booking_details');

    Route::post('/update-booking', 'Admin\RoomBookingController@updateBooking')->name('admin.room_bookings.update_booking');

    Route::post('/send-mail', 'Admin\RoomBookingController@sendMail')->name('admin.room_bookings.send_mail');

    Route::post('/delete-booking/{id}', 'Admin\RoomBookingController@delete')->name('admin.room_bookings.delete_booking');

    Route::post('/bulk-delete-booking', 'Admin\RoomBookingController@bulkDeleteBooking')->name('admin.room_bookings.bulk_delete_booking');

    Route::get('/get-booked-dates', 'Admin\RoomBookingController@bookedDates')->name('admin.room_bookings.get_booked_dates');

    Route::get('/{slug}/{id}/get-hourly-price', 'Admin\RoomBookingController@getPrice')->name('admin.room_bookings.get_hourly_price');

    Route::get('/{slug}/{id}/get-hourly-price-edit', 'Admin\RoomBookingController@getPriceForEdit')->name('admin.room_bookings.get_hourly_price_edit');

    Route::get('/booking-form', 'Admin\RoomBookingController@bookingForm')->name('admin.room_bookings.booking_form');

    Route::post('/make-booking', 'Admin\RoomBookingController@makeBooking')->name('admin.room_bookings.make_booking');
  });
  // Room Bookings Routes END

  //========Hotels Management//
  Route::prefix('hotel-management')->middleware('permission:Hotels Management')->group(function () {

    // settings route
    Route::get('/settings', 'Admin\HotelManagement\HotelController@settings')->name('admin.hotel_management.settings');
    Route::post('/update-settings', 'Admin\HotelManagement\HotelController@updateSettings')->name('admin.hotel_management.update_settings');

    // category route
    Route::prefix('categories')->group(function () {

      Route::get('/', 'Admin\HotelManagement\CategoryController@index')->name('admin.hotel_management.categories');
      Route::post('/store', 'Admin\HotelManagement\CategoryController@store')->name('admin.hotel_management.category.store');
      Route::post('/update', 'Admin\HotelManagement\CategoryController@update')->name('admin.hotel_management.category.update');
      Route::post('/delete/{id}', 'Admin\HotelManagement\CategoryController@destroy')->name('admin.hotel_management.category.delete');
      Route::post('/bulk-delete', 'Admin\HotelManagement\CategoryController@bulkDestroy')->name('admin.hotel_management.category.bulk_delete');
    });

    // HOtel Aminites route
    Route::prefix('amenities')->group(function () {
      Route::get('/', 'Admin\HotelManagement\AmenitieController@index')->name('admin.hotel_management.amenities');
      Route::post('/store-amenitie', 'Admin\HotelManagement\AmenitieController@store')->name('admin.hotel_management.amenitie.store');
      Route::post('/update-amenitie', 'Admin\HotelManagement\\AmenitieController@update')->name('admin.hotel_management.amenitie.update');
      Route::post('/delete-amenitie/{id}', 'Admin\HotelManagement\AmenitieController@destroy')->name('admin.hotel_management.amenitie.delete');
      Route::post('/bulk-delete-amenities', 'Admin\HotelManagement\AmenitieController@bulkDestroy')->name('admin.hotel_management.amenitie.bulk_delete');
    });

    // HOtel Location route
    Route::prefix('location')->group(function () {

      //Country route
      Route::prefix('countries')->group(function () {
        Route::get('/', 'Admin\HotelManagement\Location\CountryController@index')->name('admin.hotel_management.location.countries');
        Route::post('/store', 'Admin\HotelManagement\Location\CountryController@store')->name('admin.hotel_management.location.store_country');
        Route::post('/update', 'Admin\HotelManagement\Location\CountryController@update')->name('admin.hotel_management.location.update_country');
        Route::post('/delete/{id}', 'Admin\HotelManagement\Location\CountryController@destroy')->name('admin.hotel_management.location.delete_country');
        Route::post('/bulk-delete', 'Admin\HotelManagement\Location\CountryController@bulkDestroy')->name('admin.hotel_management.location.bulk_delete_country');
      });

      //  states route
      Route::prefix('states')->group(function () {
        Route::get('/', 'Admin\HotelManagement\Location\StateController@index')->name('admin.hotel_management.location.states');
        Route::get('/get-country/{language_id}', 'Admin\HotelManagement\Location\StateController@getCountry')->name('admin.hotel_management.location.get-countries');
        Route::post('/store', 'Admin\HotelManagement\Location\StateController@store')->name('admin.hotel_management.location.store_state');
        Route::post('/update', 'Admin\HotelManagement\Location\StateController@update')->name('admin.hotel_management.location.update_state');
        Route::post('/delete/{id}', 'Admin\HotelManagement\Location\StateController@destroy')->name('admin.hotel_management.location.delete_state');
        Route::post('/bulk-delete', 'Admin\HotelManagement\Location\StateController@bulkDestroy')->name('admin.hotel_management.location.bulk_delete_state');
      });

      //  City route
      Route::prefix('cities')->group(function () {
        Route::get('/', 'Admin\HotelManagement\Location\CityController@index')->name('admin.hotel_management.location.city');
        Route::get('/get-state/{country}', 'Admin\HotelManagement\Location\CityController@getState')->name('admin.hotel_management.location.get-state');
        Route::post('/store', 'Admin\HotelManagement\Location\CityController@store')->name('admin.hotel_management.location.store_city');
        Route::post('/update', 'Admin\HotelManagement\Location\CityController@update')->name('admin.hotel_management.location.update_city');
        Route::post('/delete/{id}', 'Admin\HotelManagement\Location\CityController@destroy')->name('admin.hotel_management.location.delete_city');
        Route::post('/bulk-delete', 'Admin\HotelManagement\Location\CityController@bulkDestroy')->name('admin.hotel_management.location.bulk_delete_city');
      });
    });

    Route::get('/', 'Admin\HotelManagement\HotelController@index')->name('admin.hotel_management.hotels');

    Route::post('/get-state', 'Admin\HotelManagement\HotelController@getState')->name('admin.hotel_management.get-state');
    Route::post('/get-city', 'Admin\HotelManagement\HotelController@getCity')->name('admin.hotel_management.get-city');

    Route::get('/select-vendor', 'Admin\HotelManagement\HotelController@selectVendor')->name('admin.hotel_management.select_vendor');
    Route::post('take-vendor', 'Admin\HotelManagement\HotelController@findVendor')->name('admin.hotel_management.find_vendor_id')->withoutMiddleware('Demo');

    Route::get('/create/{vendor_id}', 'Admin\HotelManagement\HotelController@create')->name('admin.hotel_management.create_hotel');
    Route::post('store', 'Admin\HotelManagement\HotelController@store')->name('admin.hotel_management.store_hotel');

    Route::get('edit-hotel/{id}', 'Admin\HotelManagement\HotelController@edit')->name('admin.hotel_management.edit_hotel');
    Route::post('update/{id}', 'Admin\HotelManagement\HotelController@update')->name('admin.hotel_management.update_hotel');

    Route::post('delete/{id}', 'Admin\HotelManagement\HotelController@delete')->name('admin.hotel_management.delete_hotel');
    Route::post('bulk_delete', 'Admin\HotelManagement\HotelController@bulkDelete')->name('admin.hotel_management.bulk_delete.hotel');

    Route::post('update-status', 'Admin\HotelManagement\HotelController@updateStatus')->name('admin.hotel_management.update_hotel_status');

    Route::get('manage-counter-section/{id}', 'Admin\HotelManagement\HotelController@manageCounterInformation')->name('admin.hotel_management.manage_counter_section');
    Route::post('upadte-counter-section/{id}', 'Admin\HotelManagement\HotelController@updateCounterInformation')->name('admin.hotel_management.update_counter_section');
    Route::post('counter/delete', 'Admin\HotelManagement\HotelController@CounterDelete')->name('admin.hotel_management.delete_counter');


    Route::post('/update_featured', 'Admin\HotelManagement\HotelController@updateFeatured')->name('admin.hotel_management.purchase_feature');

    Route::post('/unfeature/{id}', 'Admin\HotelManagement\HotelController@unfeature')->name('admin.hotel_management.unfeature');


    //#==========Hotel slider image
    Route::post('/img-store', 'Admin\HotelManagement\HotelController@imagesstore')->name('admin.hotel_management.hotel.imagesstore');
    Route::post('/img-remove', 'Admin\HotelManagement\HotelController@imagermv')->name('admin.hotel_management.hotel.imagermv');
    Route::post('/img-db-remove', 'Admin\HotelManagement\HotelController@imagedbrmv')->name('admin.hotel_management.hotel.imgdbrmv');
    //#==========Hotel slider image End

    //Featured hotels 
    Route::prefix('/feature-hotel')->group(function () {

      Route::prefix('/charges')->group(function () {

        Route::get('/', 'Admin\HotelManagement\FeaturedHotel\ChargeController@index')->name('admin.hotel_management.featured_hotel.charge');
        Route::post('/store', 'Admin\HotelManagement\FeaturedHotel\ChargeController@store')->name('admin.hotel_management.featured_hotel.charge_store');
        Route::post('/update', 'Admin\HotelManagement\FeaturedHotel\ChargeController@update')->name('admin.hotel_management.featured_hotel.update');
        Route::post('/delete/{id}', 'Admin\HotelManagement\FeaturedHotel\ChargeController@destroy')->name('admin.hotel_management.featured_hotel.charge.delete');
        Route::post('/bulk-delete', 'Admin\HotelManagement\FeaturedHotel\ChargeController@bulkDestroy')->name('admin.hotel_management.featured_hotel.charge.bulk_delete');
      });

      Route::get('/all-requests', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@index')->name('admin.hotel_management.featured_hotel.all_request');
      Route::get('/pending-requests', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@pending')->name('admin.hotel_management.featured_hotel.pending_request');
      Route::get('/approved-requests', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@approved')->name('admin.hotel_management.featured_hotel.approved_request');
      Route::get('/rejected-requests', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@rejected')->name('admin.hotel_management.featured_hotel.rejected_request');
      Route::post('/update-payment-status/{id}', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@updatePaymentStatus')->name('admin.hotel_management.featured_hotel.update_payment_status');
      Route::post('/update-order-status/{id}', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@updateOrderStatus')->name('admin.hotel_management.featured_hotel.update_order_status');
      Route::post('/delete/{id}', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@destroy')->name('admin.hotel_management.featured_hotel.delete');
      Route::post('/bulk-delete-order', 'Admin\HotelManagement\FeaturedHotel\FeatureRequestController@bulkDestroy')->name('admin.hotel_management.featured_hotel.bulk_delete_order');
    });

    // holiday route
    Route::prefix('holidays')->group(function () {
      Route::get('/', 'Admin\HotelManagement\HolidayController@index')->name('admin.hotel_management.hotel.holiday');
      Route::post('/store', 'Admin\HotelManagement\HolidayController@store')->name('admin.hotel_management.hotel.holiday.store');
      Route::post('/delete/{id}', 'Admin\HotelManagement\HolidayController@destroy')->name('admin.hotel_management.hotel.holiday.delete');
      Route::post('/bulke-destory', 'Admin\HotelManagement\HolidayController@blukDestroy')->name('admin.global.holiday.bluk-destroy');
    });

    //end holiday route
  });
  //=====Hotels Management END============

  //========ROOMS MANAGEMENT//
  Route::prefix('rooms-management')->middleware('permission:Rooms Management')->group(function () {

    // settings route
    Route::get('/settings', 'Admin\RoomManagement\RoomController@settings')->name('admin.room_management.settings');
    Route::post('/update-settings', 'Admin\RoomManagement\RoomController@updateSettings')->name('admin.room_management.update_settings');

    Route::prefix('categories')->group(function () {

      Route::get('/', 'Admin\RoomManagement\RoomCategoryController@index')->name('admin.room_management.categories');
      Route::post('/store', 'Admin\RoomManagement\RoomCategoryController@store')->name('admin.room_management.categories.store');
      Route::post('/update', 'Admin\RoomManagement\RoomCategoryController@update')->name('admin.room_management.categories.update');
      Route::post('/delete/{id}', 'Admin\RoomManagement\RoomCategoryController@destroy')->name('admin.room_management.categories.delete');
      Route::post('/bulk-delete', 'Admin\RoomManagement\RoomCategoryController@bulkDestroy')->name('admin.room_management.categories.bulk_delete');
    });

    // coupon route
    Route::prefix('coupons')->group(function () {
      Route::get('/', 'Admin\RoomManagement\CouponController@index')->name('admin.room_management.coupons');
      Route::post('/store', 'Admin\RoomManagement\CouponController@store')->name('admin.room_management.coupon.store');
      Route::post('/update', 'Admin\RoomManagement\CouponController@update')->name('admin.room_management.coupon.update');
      Route::post('/delete/{id}', 'Admin\RoomManagement\CouponController@destroy')->name('admin.room_management.coupon.delete');
      Route::post('/bulk-delete', 'Admin\RoomManagement\CouponController@bulkDestroy')->name('admin.room_management.coupon.bulk_delete');
    });

    //Additional Specification
    Route::prefix('additonal-services')->group(function () {
      Route::get('/', 'Admin\RoomManagement\AdditionalServiceController@index')->name('admin.room_management.additional_services');
      Route::post('/store', 'Admin\RoomManagement\AdditionalServiceController@store')->name('admin.room_management.additional_service.store');
      Route::post('/update', 'Admin\RoomManagement\AdditionalServiceController@update')->name('admin.room_management.additional_service.update');
      Route::post('/delete/{id}', 'Admin\RoomManagement\AdditionalServiceController@destroy')->name('admin.room_management.additional_service.delete');
      Route::post('/bulk-delete', 'Admin\RoomManagement\AdditionalServiceController@bulkDestroy')->name('admin.room_management.additional_service.bulk_delete');
    });


    // Hotel ROOM BOOKING HOUR route
    Route::prefix('booking-hours')->group(function () {
      Route::get('/', 'Admin\RoomManagement\BookingHourController@index')->name('admin.room_management.booking_hours');
      Route::post('/store', 'Admin\RoomManagement\BookingHourController@store')->name('admin.room_management.store_booking_hour');
      Route::post('/update', 'Admin\RoomManagement\BookingHourController@update')->name('admin.room_management.update_booking_hour');
      Route::post('/delete/{id}', 'Admin\RoomManagement\BookingHourController@destroy')->name('admin.room_management.delete_booking_hour');
      Route::post('/bulk-delete', 'Admin\RoomManagement\BookingHourController@bulkDestroy')->name('admin.room_management.bulk_delete_booking_hours');
    });

    Route::get('/', 'Admin\RoomManagement\RoomController@index')->name('admin.room_management.rooms');

    Route::get('/select-vendor', 'Admin\RoomManagement\RoomController@selectVendor')->name('admin.room_management.select_vendor');
    Route::post('take-vendor', 'Admin\RoomManagement\RoomController@findVendor')->name('admin.room_management.find_vendor_id')->withoutMiddleware('Demo');

    Route::get('/create/{vendor_id}', 'Admin\RoomManagement\RoomController@create')->name('admin.room_management.create_room');
    Route::post('store', 'Admin\RoomManagement\RoomController@store')->name('admin.room_management.store_room');

    Route::get('edit-room/{id}', 'Admin\RoomManagement\RoomController@edit')->name('admin.room_management.edit_room');
    Route::post('update/{id}', 'Admin\RoomManagement\RoomController@update')->name('admin.room_management.update_room');

    Route::post('delete/{id}', 'Admin\RoomManagement\RoomController@delete')->name('admin.room_management.delete_room');
    Route::post('bulk_delete', 'Admin\RoomManagement\RoomController@bulkDelete')->name('admin.room_management.bulk_delete.room');

    Route::post('update-status', 'Admin\RoomManagement\RoomController@updateStatus')->name('admin.room_management.update_room_status');

    Route::get('additional-services/{id}', 'Admin\RoomManagement\RoomController@manageAdditionalService')->name('admin.room_management.manage_additional_service');

    Route::post('upadte-additional-service/{id}', 'Admin\RoomManagement\RoomController@updateAdditionalService')->name('admin.room_management.update_additional_service');

    Route::post('/update_featured', 'Admin\RoomManagement\RoomController@updateFeatured')->name('admin.room_management.purchase_feature');

    Route::post('/unfeature/{id}', 'Admin\RoomManagement\RoomController@unfeature')->name('admin.room_management.unfeature');

    Route::get('/tax-amount', 'Admin\BasicSettings\BasicController@hotelTaxAmount')->name('admin.room_management.tax_amount');
    Route::post('/update-tax-amount', 'Admin\BasicSettings\BasicController@updateHotelTaxAmount')->name('admin.room_management.update_tax_amount');

    //#==========ROOM slider image
    Route::post('/img-store', 'Admin\RoomManagement\RoomController@imagesstore')->name('admin.room_management.room.imagesstore');
    Route::post('/img-remove', 'Admin\RoomManagement\RoomController@imagermv')->name('admin.room_management.room.imagermv');
    Route::post('/img-db-remove', 'Admin\RoomManagement\RoomController@imagedbrmv')->name('admin.room_management.room.imgdbrmv');
    //#==========ROOM slider image End


    Route::prefix('/feature-room')->group(function () {

      Route::prefix('/charges')->group(function () {

        Route::get('/', 'Admin\RoomManagement\FeaturedRoom\ChargeController@index')->name('admin.room_management.featured_room.charge');
        Route::post('/charge-store', 'Admin\RoomManagement\FeaturedRoom\ChargeController@store')->name('admin.room_management.featured_room.charge_store');
        Route::post('/update-charge', 'Admin\RoomManagement\FeaturedRoom\ChargeController@update')->name('admin.room_management.featured_room.update');
        Route::post('/delete-charge/{id}', 'Admin\RoomManagement\FeaturedRoom\ChargeController@destroy')->name('admin.room_management.featured_room.charge.delete');
        Route::post('/bulk-delete-charge', 'Admin\RoomManagement\FeaturedRoom\ChargeController@bulkDestroy')->name('admin.room_management.featured_room.charge.bulk_delete');
      });

      Route::get('/all-requests', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@index')->name('admin.room_management.featured_room.all_request');
      Route::get('/pending-requests', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@pending')->name('admin.room_management.featured_room.pending_request');
      Route::get('/approved-requests', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@approved')->name('admin.room_management.featured_room.approved_request');
      Route::get('/rejected-requests', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@rejected')->name('admin.room_management.featured_room.rejected_request');
      Route::post('/update-payment-status/{id}', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@updatePaymentStatus')->name('admin.room_management.featured_room.update_payment_status');
      Route::post('/update-order-status/{id}', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@updateOrderStatus')->name('admin.room_management.featured_room.update_order_status');
      Route::post('/delete/{id}', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@destroy')->name('admin.room_management.featured_room.delete');
      Route::post('/bulk-delete-order', 'Admin\RoomManagement\FeaturedRoom\FeatureRequestController@bulkDestroy')->name('admin.room_management.featured_room.bulk_delete_order');
    });
  });
  //=====Rooms MANAGEMENT END============


  // user management route start
  Route::prefix('/user-management')->middleware('permission:User Management')->group(function () {
    // registered user route
    Route::get('/registered-users', 'Admin\User\UserController@index')->name('admin.user_management.registered_users');

    Route::get('/create', 'Admin\User\UserController@create')->name('admin.user_management.registered_user.create');
    Route::post('/store', 'Admin\User\UserController@store')->name('admin.user_management.registered_user.store');

    Route::prefix('/user/{id}')->group(function () {

      Route::get('/details', 'Admin\User\UserController@view')->name('admin.user_management.registered_user.view');

      Route::get('/edit', 'Admin\User\UserController@edit')->name('admin.user_management.registered_user.edit');
      Route::post('/update', 'Admin\User\UserController@update')->name('admin.user_management.registered_user.update');

      Route::post('/update-account-status', 'Admin\User\UserController@updateAccountStatus')->name('admin.user_management.user.update_account_status');

      Route::post('/update-email-status', 'Admin\User\UserController@updateEmailStatus')->name('admin.user_management.user.update_email_status');

      Route::get('/change-password', 'Admin\User\UserController@changePassword')->name('admin.user_management.user.change_password');

      Route::post('/update-password', 'Admin\User\UserController@updatePassword')->name('admin.user_management.user.update_password');

      Route::post('/delete', 'Admin\User\UserController@destroy')->name('admin.user_management.user.delete');
      Route::get('/secret-login', 'Admin\User\UserController@secret_login')->name('admin.user_management.user.secret-login');
    });

    Route::post('/bulk-delete-user', 'Admin\User\UserController@bulkDestroy')->name('admin.user_management.bulk_delete_user');

    // subscriber route
    Route::get('/subscribers', 'Admin\User\SubscriberController@index')->name('admin.user_management.subscribers');

    Route::post('/subscriber/{id}/delete', 'Admin\User\SubscriberController@destroy')->name('admin.user_management.subscriber.delete');

    Route::post('/bulk-delete-subscriber', 'Admin\User\SubscriberController@bulkDestroy')->name('admin.user_management.bulk_delete_subscriber');

    Route::get('/mail-for-subscribers', 'Admin\User\SubscriberController@writeEmail')->name('admin.user_management.mail_for_subscribers');

    Route::post('/subscribers/send-email', 'Admin\User\SubscriberController@prepareEmail')->name('admin.user_management.subscribers.send_email');

    // push notification route
    Route::prefix('/push-notification')->group(function () {
      Route::get('/settings', 'Admin\User\PushNotificationController@settings')->name('admin.user_management.push_notification.settings');

      Route::post('/update-settings', 'Admin\User\PushNotificationController@updateSettings')->name('admin.user_management.push_notification.update_settings');

      Route::get('/notification-for-visitors', 'Admin\User\PushNotificationController@writeNotification')->name('admin.user_management.push_notification.notification_for_visitors');

      Route::post('/send', 'Admin\User\PushNotificationController@sendNotification')->name('admin.user_management.push_notification.send');
    });
  });
  // user management route end

  // vendor management route start
  Route::prefix('/vendor-management')->middleware('permission:Vendors Management')->group(function () {
    Route::get('/settings', 'Admin\VendorManagementController@settings')->name('admin.vendor_management.settings');
    Route::post('/settings/update', 'Admin\VendorManagementController@update_setting')->name('admin.vendor_management.setting.update');

    Route::get('/add-vendor', 'Admin\VendorManagementController@add')->name('admin.vendor_management.add_vendor');
    Route::post('/save-vendor', 'Admin\VendorManagementController@create')->name('admin.vendor_management.save-vendor');

    Route::get('/registered-vendors', 'Admin\VendorManagementController@index')->name('admin.vendor_management.registered_vendor');

    Route::prefix('/vendor/{id}')->group(function () {

      Route::post('/update-account-status', 'Admin\VendorManagementController@updateAccountStatus')->name('admin.vendor_management.vendor.update_account_status');

      Route::post('/update-email-status', 'Admin\VendorManagementController@updateEmailStatus')->name('admin.vendor_management.vendor.update_email_status');

      Route::get('/details', 'Admin\VendorManagementController@show')->name('admin.vendor_management.vendor_details');

      Route::get('/edit', 'Admin\VendorManagementController@edit')->name('admin.edit_management.vendor_edit');

      Route::post('/update', 'Admin\VendorManagementController@update')->name('admin.vendor_management.vendor.update_vendor');

      Route::post('/update/vendor/balance', 'Admin\VendorManagementController@update_vendor_balance')->name('admin.vendor_management.update_vendor_balance');

      Route::get('/change-password', 'Admin\VendorManagementController@changePassword')->name('admin.vendor_management.vendor.change_password');

      Route::post('/update-password', 'Admin\VendorManagementController@updatePassword')->name('admin.vendor_management.vendor.update_password');

      Route::post('/delete', 'Admin\VendorManagementController@destroy')->name('admin.vendor_management.vendor.delete');
    });

    Route::post('/vendor/current-package/remove', 'Admin\VendorManagementController@removeCurrPackage')->name('vendor.currPackage.remove');

    Route::post('/vendor/current-package/change', 'Admin\VendorManagementController@changeCurrPackage')->name('vendor.currPackage.change');

    Route::post('/vendor/current-package/add', 'Admin\VendorManagementController@addCurrPackage')->name('vendor.currPackage.add');

    Route::post('/vendor/next-package/remove', 'Admin\VendorManagementController@removeNextPackage')->name('vendor.nextPackage.remove');

    Route::post('/vendor/next-package/change', 'Admin\VendorManagementController@changeNextPackage')->name('vendor.nextPackage.change');

    Route::post('/vendor/next-package/add', 'Admin\VendorManagementController@addNextPackage')->name('vendor.nextPackage.add');


    Route::post('/bulk-delete-vendor', 'Admin\VendorManagementController@bulkDestroy')->name('admin.vendor_management.bulk_delete_vendor');

    Route::get('/secret-login/{id}', 'Admin\VendorManagementController@secret_login')->name('admin.vendor_management.vendor.secret_login');
  });
  // vendor management route start

  #====support tickets ============

  Route::prefix('support-ticket')->middleware('permission:Support Tickets')->group(function () {
    Route::get('/setting', 'Admin\SupportTicketController@setting')->name('admin.support_ticket.setting');
    Route::post('/setting/update', 'Admin\SupportTicketController@update_setting')->name('admin.support_ticket.update_setting');
    Route::get('/tickets', 'Admin\SupportTicketController@index')->name('admin.support_tickets');
    Route::get('/message/{id}', 'Admin\SupportTicketController@message')->name('admin.support_tickets.message');
    Route::post('/zip-upload', 'Admin\SupportTicketController@zip_file_upload')->name('admin.support_ticket.zip_file.upload');
    Route::post('/reply/{id}', 'Admin\SupportTicketController@ticketreply')->name('admin.support_ticket.reply');
    Route::post('/closed/{id}', 'Admin\SupportTicketController@ticket_closed')->name('admin.support_ticket.close');
    Route::post('/assign-stuff/{id}', 'Admin\SupportTicketController@assign_stuff')->name('assign_stuff.supoort.ticket');

    Route::get('/unassign-stuff/{id}', 'Admin\SupportTicketController@unassign_stuff')->name('admin.support_tickets.unassign');

    Route::post('/delete/{id}', 'Admin\SupportTicketController@delete')->name('admin.support_tickets.delete');
    Route::post('/bulk-delete', 'Admin\SupportTicketController@bulk_delete')->name('admin.support_tickets.bulk_delete');
  });


  // advertise route start
  Route::prefix('/advertise')->middleware('permission:Advertisements')->group(function () {
    Route::get('/settings', 'Admin\AdvertisementController@advertiseSettings')->name('admin.advertise.settings');

    Route::post('/update-settings', 'Admin\AdvertisementController@updateAdvertiseSettings')->name('admin.advertise.update_settings');

    Route::get('/all-advertisement', 'Admin\AdvertisementController@index')->name('admin.advertise.all_advertisement');

    Route::post('/store-advertisement', 'Admin\AdvertisementController@store')->name('admin.advertise.store_advertisement');

    Route::post('/update-advertisement', 'Admin\AdvertisementController@update')->name('admin.advertise.update_advertisement');

    Route::post('/delete-advertisement/{id}', 'Admin\AdvertisementController@destroy')->name('admin.advertise.delete_advertisement');

    Route::post('/bulk-delete-advertisement', 'Admin\AdvertisementController@bulkDestroy')->name('admin.advertise.bulk_delete_advertisement');
  });
  // advertise route end

  // announcement-popup route start
  Route::prefix('/announcement-popups')->middleware('permission:Announcement Popups')->group(function () {
    Route::get('', 'Admin\PopupController@index')->name('admin.announcement_popups');

    Route::get('/select-popup-type', 'Admin\PopupController@popupType')->name('admin.announcement_popups.select_popup_type');

    Route::get('/create-popup/{type}', 'Admin\PopupController@create')->name('admin.announcement_popups.create_popup');

    Route::post('/store-popup', 'Admin\PopupController@store')->name('admin.announcement_popups.store_popup');

    Route::post('/popup/{id}/update-status', 'Admin\PopupController@updateStatus')->name('admin.announcement_popups.update_popup_status');

    Route::get('/edit-popup/{id}', 'Admin\PopupController@edit')->name('admin.announcement_popups.edit_popup');

    Route::post('/update-popup/{id}', 'Admin\PopupController@update')->name('admin.announcement_popups.update_popup');

    Route::post('/delete-popup/{id}', 'Admin\PopupController@destroy')->name('admin.announcement_popups.delete_popup');

    Route::post('/bulk-delete-popup', 'Admin\PopupController@bulkDestroy')->name('admin.announcement_popups.bulk_delete_popup');
  });
  // announcement-popup route end

  Route::prefix('/settings')->middleware('permission:Settings')->group(function () {
    // Settings favicon route
    Route::get('pwa', 'Admin\BasicSettings\BasicController@pwa')->name('admin.pwa');
    Route::post('/pwa/post', 'Admin\BasicSettings\BasicController@updatepwa')->name('admin.pwa.update');

    Route::get('/favicon', 'Admin\BasicSettings\BasicController@favicon')->name('admin.settings.favicon');

    Route::post('/update-favicon', 'Admin\BasicSettings\BasicController@updateFavicon')->name('admin.settings.update_favicon');

    // Settings logo route
    Route::get('/logo', 'Admin\BasicSettings\BasicController@logo')->name('admin.settings.logo');

    Route::post('/update-logo', 'Admin\BasicSettings\BasicController@updateLogo')->name('admin.settings.update_logo');

    // Settings information route
    Route::get('/information', 'Admin\BasicSettings\BasicController@information')->name('admin.settings.information');

    Route::post('/update-info', 'Admin\BasicSettings\BasicController@updateInfo')->name('admin.settings.update_info');

    Route::get('/general-settings', 'Admin\BasicSettings\BasicController@general_settings')->name('admin.settings.general_settings');

    Route::post('/update-general-settings', 'Admin\BasicSettings\BasicController@update_general_setting')->name('admin.settings.general_settings.update');


    // Settings (theme & home) route
    Route::get('/theme-and-home', 'Admin\BasicSettings\BasicController@themeAndHome')->name('admin.settings.theme_and_home');

    Route::post(
      '/update-theme-and-home',
      'Admin\BasicSettings\BasicController@updateThemeAndHome'
    )->name('admin.settings.update_theme_and_home');

    // Settings currency route
    Route::get('/currency', 'Admin\BasicSettings\BasicController@currency')->name('admin.settings.currency');

    Route::post('/update-currency', 'Admin\BasicSettings\BasicController@updateCurrency')->name('admin.settings.update_currency');

    // Settings appearance route
    Route::get('/appearance', 'Admin\BasicSettings\BasicController@appearance')->name('admin.settings.appearance');

    Route::post('/update-appearance', 'Admin\BasicSettings\BasicController@updateAppearance')->name('admin.settings.update_appearance');

    // Settings mail route start
    Route::get('/mail-from-admin', 'Admin\BasicSettings\BasicController@mailFromAdmin')->name('admin.settings.mail_from_admin');

    Route::post('/update-mail-from-admin', 'Admin\BasicSettings\BasicController@updateMailFromAdmin')->name('admin.settings.update_mail_from_admin');

    Route::get('/mail-to-admin', 'Admin\BasicSettings\BasicController@mailToAdmin')->name('admin.settings.mail_to_admin');

    Route::post('/update-mail-to-admin', 'Admin\BasicSettings\BasicController@updateMailToAdmin')->name('admin.settings.update_mail_to_admin');

    Route::get('/mail-templates', 'Admin\BasicSettings\MailTemplateController@index')->name('admin.settings.mail_templates');

    Route::get('/edit-mail-template/{id}', 'Admin\BasicSettings\MailTemplateController@edit')->name('admin.settings.edit_mail_template');

    Route::post('/update-mail-template/{id}', 'Admin\BasicSettings\MailTemplateController@update')->name('admin.settings.update_mail_template');
    // Settings mail route end

    // payment-gateway route start
    Route::prefix('/payment-gateways')->group(function () {

      Route::get('/online-gateways', 'Admin\PaymentGateway\OnlineGatewayController@index')->name('admin.settings.payment_gateways.online_gateways');
      Route::post('/update-paypal-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePayPalInfo')->name('admin.settings.payment_gateways.update_paypal_info');
      Route::post(
        '/update-instamojo-info',
        'Admin\PaymentGateway\OnlineGatewayController@updateInstamojoInfo'
      )->name('admin.settings.payment_gateways.update_instamojo_info');
      Route::post('/update-paystack-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePaystackInfo')->name('admin.settings.payment_gateways.update_paystack_info');
      Route::post('/update-flutterwave-info', 'Admin\PaymentGateway\OnlineGatewayController@updateFlutterwaveInfo')->name('admin.settings.payment_gateways.update_flutterwave_info');
      Route::post('/update-razorpay-info', 'Admin\PaymentGateway\OnlineGatewayController@updateRazorpayInfo')->name('admin.settings.payment_gateways.update_razorpay_info');
      Route::post('/update-mercadopago-info', 'Admin\PaymentGateway\OnlineGatewayController@updateMercadoPagoInfo')->name('admin.settings.payment_gateways.update_mercadopago_info');
      Route::post('/update-mollie-info', 'Admin\PaymentGateway\OnlineGatewayController@updateMollieInfo')->name('admin.settings.payment_gateways.update_mollie_info');
      Route::post('/update-stripe-info', 'Admin\PaymentGateway\OnlineGatewayController@updateStripeInfo')->name('admin.settings.payment_gateways.update_stripe_info');
      Route::post('/update-paytm-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePaytmInfo')->name('admin.settings.payment_gateways.update_paytm_info');
      Route::post('/update-anet-info', 'Admin\PaymentGateway\OnlineGatewayController@updateAnetInfo')->name('admin.settings.payment_gateways.update_anet_info');
      Route::post('/update-iyzico-info', 'Admin\PaymentGateway\OnlineGatewayController@updateIyzicoInfo')->name('admin.settings.payment_gateways.update_iyzico_info');
      Route::post('/update-midtrans-info', 'Admin\PaymentGateway\OnlineGatewayController@updateMidtransInfo')->name('admin.settings.payment_gateways.update_midtrans_info');
      Route::post(
        '/update-myfatoorah-info',
        'Admin\PaymentGateway\OnlineGatewayController@updateMyFatoorahInfo'
      )->name('admin.settings.payment_gateways.update_myfatoorah_info');
      Route::post('/update-phonepe-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePhonepeInfo')->name('admin.settings.payment_gateways.update_phonepe_info');
      Route::post('/update-yoco-info', 'Admin\PaymentGateway\OnlineGatewayController@updateYocoInfo')->name('admin.settings.payment_gateways.update_yoco_info');
      Route::post(
        '/update-toyyibpay-info',
        'Admin\PaymentGateway\OnlineGatewayController@updateToyyibpayInfo'
      )->name('admin.settings.payment_gateways.update_toyyibpay_info');
      Route::post('/update-paytabs-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePaytabsInfo')->name('admin.settings.payment_gateways.update_paytabs_info');
      Route::post('/update-perfect_money-info', 'Admin\PaymentGateway\OnlineGatewayController@updatePerfectMoneyInfo')->name('admin.settings.payment_gateways.update_perfect_money_info');
      Route::post('/update-zendit-info', 'Admin\PaymentGateway\OnlineGatewayController@updateXenditInfo')->name('admin.settings.payment_gateways.update_xendit_info');


      Route::get('/offline-gateways', 'Admin\PaymentGateway\OfflineGatewayController@index')->name('admin.settings.payment_gateways.offline_gateways');

      Route::post(
        '/store-offline-gateway',
        'Admin\PaymentGateway\OfflineGatewayController@store'
      )->name('admin.settings.payment_gateways.store_offline_gateway');

      Route::post('/update-status/{id}', 'Admin\PaymentGateway\OfflineGatewayController@updateStatus')->name('admin.settings.payment_gateways.update_status');

      Route::post(
        '/update-offline-gateway',
        'Admin\PaymentGateway\OfflineGatewayController@update'
      )->name('admin.settings.payment_gateways.update_offline_gateway');

      Route::post('/delete-offline-gateway/{id}', 'Admin\PaymentGateway\OfflineGatewayController@destroy')->name('admin.settings.payment_gateways.delete_offline_gateway');
    });
    // payment-gateway route end

    // language management route start
    Route::prefix('/language-management')->group(function () {
      Route::get('', 'Admin\LanguageController@index')->name('admin.settings.language_management');

      Route::post('/store', 'Admin\LanguageController@store')->name('admin.settings.language_management.store');

      Route::post('/{id}/make-default-language', 'Admin\LanguageController@makeDefault')->name('admin.settings.language_management.make_default_language');

      Route::post('/update', 'Admin\LanguageController@update')->name('admin.settings.language_management.update');

      Route::get(
        '/{id}/edit-fornt-keyword',
        'Admin\LanguageController@editKeyword'
      )->name('admin.settings.language_management.edit_front_keyword');

      Route::get(
        '/{id}/edit-admin-keyword',
        'Admin\LanguageController@editAdminKeyword'
      )->name('admin.settings.language_management.edit_admin_keyword');

      Route::post('add-keyword-fornt', 'Admin\LanguageController@addKeyword')->name('admin.settings.language_management.add_keyword_front');

      Route::post('add-keyword-admin', 'Admin\LanguageController@addKeywordAdmin')->name('admin.settings.language_management.add_keyword_admin');

      Route::post(
        '/{id}/update-keyword',
        'Admin\LanguageController@updateKeyword'
      )->name('admin.settings.language_management.update_keyword');

      Route::post('/{id}/delete', 'Admin\LanguageController@destroy')->name('admin.settings.language_management.delete');
      Route::get('/{id}/check-rtl', 'Admin\LanguageController@checkRTL');
    });
    // language management route end


    // Settings plugins route start
    Route::get('/plugins', 'Admin\BasicSettings\BasicController@plugins')->name('admin.settings.plugins');

    Route::post('/update-disqus', 'Admin\BasicSettings\BasicController@updateDisqus')->name('admin.settings.update_disqus');

    Route::post('/update-tawkto', 'Admin\BasicSettings\BasicController@updateTawkTo')->name('admin.settings.update_tawkto');

    Route::post('/update-recaptcha', 'Admin\BasicSettings\BasicController@updateRecaptcha')->name('admin.settings.update_recaptcha');

    Route::post('/update-facebook', 'Admin\BasicSettings\BasicController@updateFacebook')->name('admin.settings.update_facebook');

    Route::post('/update-google', 'Admin\BasicSettings\BasicController@updateGoogle')->name('admin.settings.update_google');

    Route::post('/update-google-map-api', 'Admin\BasicSettings\BasicController@updateGoogleMapApi')->name('admin.settings.update_google_map_api');

    Route::post('/update-whatsapp', 'Admin\BasicSettings\BasicController@updateWhatsApp')->name('admin.settings.update_whatsapp');
    // Settings plugins route end

    // Settings maintenance-mode route
    Route::get('/maintenance-mode', 'Admin\BasicSettings\BasicController@maintenance')->name('admin.settings.maintenance_mode');

    Route::post('/update-maintenance-mode', 'Admin\BasicSettings\BasicController@updateMaintenance')->name('admin.settings.update_maintenance_mode');

    // Settings cookie-alert route
    Route::get('/cookie-alert', 'Admin\BasicSettings\CookieAlertController@cookieAlert')->name('admin.settings.cookie_alert');

    Route::post('/update-cookie-alert', 'Admin\BasicSettings\CookieAlertController@updateCookieAlert')->name('admin.settings.update_cookie_alert');

    // basic-settings social-media route
    Route::get('/social-medias', 'Admin\BasicSettings\SocialMediaController@index')->name('admin.settings.social_medias');

    Route::post('/store-social-media', 'Admin\BasicSettings\SocialMediaController@store')->name('admin.settings.store_social_media');

    Route::post('/update-social-media', 'Admin\BasicSettings\SocialMediaController@update')->name('admin.settings.update_social_media');

    Route::post('/delete-social-media/{id}', 'Admin\BasicSettings\SocialMediaController@destroy')->name('admin.settings.delete_social_media');
  });


  // Staffs Management route start
  Route::prefix('/staffs-management')->middleware('permission:Staffs Management')->group(function () {
    // role-permission route
    Route::get('/role-permissions', 'Admin\Administrator\RolePermissionController@index')->name('admin.admin_management.role_permissions');

    Route::post('/store-role', 'Admin\Administrator\RolePermissionController@store')->name('admin.admin_management.store_role');

    Route::get('/role/{id}/permissions', 'Admin\Administrator\RolePermissionController@permissions')->name('admin.admin_management.role.permissions');

    Route::post('/role/{id}/update-permissions', 'Admin\Administrator\RolePermissionController@updatePermissions')->name('admin.admin_management.role.update_permissions');

    Route::post('/update-role', 'Admin\Administrator\RolePermissionController@update')->name('admin.admin_management.update_role');

    Route::post('/delete-role/{id}', 'Admin\Administrator\RolePermissionController@destroy')->name('admin.admin_management.delete_role');

    // registered admin route
    Route::get('/registered-staffs', 'Admin\Administrator\SiteAdminController@index')->name('admin.admin_management.registered_admins');

    Route::post('/store-staffs', 'Admin\Administrator\SiteAdminController@store')->name('admin.admin_management.store_admin');

    Route::post('/update-status/{id}', 'Admin\Administrator\SiteAdminController@updateStatus')->name('admin.admin_management.update_status');

    Route::post('/update-staff', 'Admin\Administrator\SiteAdminController@update')->name('admin.admin_management.update_admin');

    Route::post('/delete-staff/{id}', 'Admin\Administrator\SiteAdminController@destroy')->name('admin.admin_management.delete_admin');
  });
  // Staffs Management route end

  // language management route end

  //website pages all-route
  Route::prefix('pages')->middleware('permission:Pages')->group(function () {
    // home-page route
    Route::prefix('/home-page')->group(function () {

      //section titles
      Route::get('/images-&-texts', 'Admin\HomePage\SectionController@sectionContent')->name('admin.pages.home_page.section_content');
      Route::post('/update/images', 'Admin\HomePage\SectionController@updateImages')->name('admin.pages.home_page.images_and_texts.images_update');

      Route::post('/update/images-&-texts', 'Admin\HomePage\SectionController@updateTexts')->name('admin.pages.home_page.section_content_update');

      //home page custom section
      Route::prefix('additional-sections')->group(function () {
        Route::get('sections', 'Admin\HomePage\AdditionalSectionController@index')->name('admin.pages.home_page.additional_sections');
        Route::get('add-section', 'Admin\HomePage\AdditionalSectionController@create')->name('admin.pages.home_page.additional_section.create');
        Route::post('store-section', 'Admin\HomePage\AdditionalSectionController@store')->name('admin.pages.home_page.home.additional_section.store');
        Route::get('edit-section/{id}', 'Admin\HomePage\AdditionalSectionController@edit')->name('admin.pages.home_page.additional_section.edit');
        Route::post('update/{id}', 'Admin\HomePage\AdditionalSectionController@update')->name('admin.pages.home_page.additional_section.update');
        Route::post('delete/{id}', 'Admin\HomePage\AdditionalSectionController@delete')->name('admin.pages.home_page.additional_section.delete');
        Route::post(
          'bulkdelete',
          'Admin\HomePage\AdditionalSectionController@bulkdelete'
        )->name('admin.pages.home_page.additional_section.bulkdelete');
      });

      Route::prefix('/sliders')->group(function () {
        Route::get('', 'Admin\HomePage\Hero\SliderController@index')->name('admin.pages.home_page.hero_section.slider_version');

        Route::post('/store', 'Admin\HomePage\Hero\SliderController@store')->name('admin.pages.home_page.hero_section.slider_version.store');

        Route::post('/update', 'Admin\HomePage\Hero\SliderController@update')->name('admin.pages.home_page.hero_section.slider_version.update');

        Route::post('/{id}/delete', 'Admin\HomePage\Hero\SliderController@destroy')->name('admin.pages.home_page.hero_section.slider_version.delete');
      });

      //Benifit Section
      Route::prefix('/benifit-section')->group(function () {
        Route::get('', 'Admin\HomePage\BenifitController@index')->name('admin.pages.home_page.benifit_section');
        Route::post('/store', 'Admin\HomePage\BenifitController@store')->name('admin.pages.home_page.benifit_section.store');
        Route::post('/update', 'Admin\HomePage\BenifitController@update')->name('admin.pages.home_page.benifit_section.update');
        Route::post(
          '/{id}/delete',
          'Admin\HomePage\BenifitController@destroy'
        )->name('admin.pages.home_page.benifit_section.delete');
      });

      // section customization
      Route::get('/section-customization', 'Admin\HomePage\SectionController@index')->name('admin.pages.home_page.section_customization');
      Route::post('/update-section-status', 'Admin\HomePage\SectionController@update')->name('admin.pages.home_page.update_section_status');
    });

    // features section
    Route::prefix('/features')->group(function () {
      Route::get('/', 'Admin\HomePage\FeaturedController@index')->name('admin.pages.feature_section');
      Route::post('/add', 'Admin\HomePage\FeaturedController@addFeature')->name('admin.pages.feature_content.store');
      Route::post('/edit', 'Admin\HomePage\FeaturedController@editFeature')->name('admin.pages.feature_content.update');
      Route::post('{id}/delete', 'Admin\HomePage\FeaturedController@destroy')->name('admin.pages.feature_content.delete');
      Route::post('/bulk-delete', 'Admin\HomePage\FeaturedController@bulkDestroy')->name('admin.pages.feature_content.bulk_delete');
    });

    // testimonials
    Route::prefix('/testimonials')->group(function () {

      Route::get('/', 'Admin\HomePage\TestimonialController@index')->name('admin.pages.testimonial_section');
      Route::post('/store', 'Admin\HomePage\TestimonialController@storeTestimonial')->name('admin.pages.store_testimonial');
      Route::post('/update', 'Admin\HomePage\TestimonialController@updateTestimonial')->name('admin.pages.update_testimonial');
      Route::post('{id}/delete', 'Admin\HomePage\TestimonialController@destroyTestimonial')->name('admin.pages.delete_testimonial');
      Route::post('/bulk-delete', 'Admin\HomePage\TestimonialController@bulkDestroyTestimonial')->name('admin.pages.bulk_delete_testimonial');
    });

    // counters
    Route::prefix('/counters')->group(function () {
      Route::get('/', 'Admin\HomePage\CounterController@index')->name('admin.pages.counter_section');
      Route::post('/store', 'Admin\HomePage\CounterController@storeCounter')->name('admin.pages.store_counter');
      Route::post('/update', 'Admin\HomePage\CounterController@updateCounter')->name('admin.pages.update_counter');
      Route::post('{id}/delete', 'Admin\HomePage\CounterController@destroyCounter')->name('admin.pages.delete_counter');
      Route::post('/bulk-delete', 'Admin\HomePage\CounterController@bulkDestroyCounter')->name('admin.pages.bulk_delete_counter');
    });

    //about-us-page route
    Route::prefix('about-us')->group(function () {
      //about us section
      Route::get('/about', 'Admin\AboutUs\AboutSectionController@about_us')->name('admin.pages.about_us.index');

      Route::post('/update-about-us-image', 'Admin\AboutUs\AboutSectionController@updateImage')->name('admin.pages.about_us.update_image');

      Route::post('/update-about-us', 'Admin\AboutUs\AboutSectionController@updateAboutUs')->name('admin.pages.about_us.update');

      Route::get('/customize-section', 'Admin\AboutUs\AboutSectionController@customizeSection')->name('admin.pages.about_us.customize');
      Route::post('/customize-section/update', 'Admin\AboutUs\AboutSectionController@customizeUpdate')->name('admin.about_us.customize_update');

      // features
      Route::post('/store-features', 'Admin\AboutUs\FeaturesController@storeFeatures')->name('admin.about_us.store_features');

      Route::post('/update-features', 'Admin\AboutUs\FeaturesController@updateFeatures')->name('admin.about_us.update_features');

      Route::post('{id}/delete', 'Admin\AboutUs\FeaturesController@destroy')->name('admin.about_us.delete_features');

      Route::post('/bulk-delete', 'Admin\AboutUs\FeaturesController@bulkDestroy')->name('admin.about_us.bulk_delete_features');

      //about page custom section
      Route::prefix('additional-sections')->group(function () {
        Route::get('sections', 'Admin\AboutUs\AdditionalSectionController@index')->name('admin.additional_sections');
        Route::get('add-section', 'Admin\AboutUs\AdditionalSectionController@create')->name('admin.additional_section.create');
        Route::post('store-section', 'Admin\AboutUs\AdditionalSectionController@store')->name('admin.additional_section.store');
        Route::get('edit-section/{id}', 'Admin\AboutUs\AdditionalSectionController@edit')->name('admin.additional_section.edit');
        Route::post('update/{id}', 'Admin\AboutUs\AdditionalSectionController@update')->name('admin.additional_section.update');
        Route::post('delete/{id}', 'Admin\AboutUs\AdditionalSectionController@delete')->name('admin.additional_section.delete');
        Route::post('bulkdelete', 'Admin\AboutUs\AdditionalSectionController@bulkdelete')->name('admin.additional_section.bulkdelete');
      });
      Route::get('/counters', 'Admin\HomePage\CounterController@aboutIndex')->name('admin.pages.about_us.counter_section');
    });

    // faq route start
    Route::prefix('/faqs')->group(function () {
      Route::get('', 'Admin\FaqController@index')->name('admin.pages.faq_management');
      Route::post('/store-faq', 'Admin\FaqController@store')->name('admin.pages.faq_management.store_faq');
      Route::post('/update-faq', 'Admin\FaqController@update')->name('admin.pages.faq_management.update_faq');
      Route::post('/delete-faq/{id}', 'Admin\FaqController@destroy')->name('admin.pages.faq_management.delete_faq');
      Route::post('/bulk-delete-faq', 'Admin\FaqController@bulkDestroy')->name('admin.pages.faq_management.bulk_delete_faq');
    });

    // blog route start
    Route::prefix('/blog')->group(function () {
      // blog category route
      Route::prefix('/categories')->group(function () {

        Route::get('/', 'Admin\Journal\CategoryController@index')->name('admin.pages.blog.categories');
        Route::post('/store', 'Admin\Journal\CategoryController@store')->name('admin.pages.blog.store_category');
        Route::post('/update', 'Admin\Journal\CategoryController@update')->name('admin.pages.blog.update_category');
        Route::post('/delete/{id}', 'Admin\Journal\CategoryController@destroy')->name('admin.pages.blog.delete_category');
        Route::post('/bulk-delete', 'Admin\Journal\CategoryController@bulkDestroy')->name('admin.pages.blog.bulk_delete_category');
      });

      Route::prefix('/posts')->group(function () {
        Route::get('/', 'Admin\Journal\BlogController@index')->name('admin.pages.blog.blogs');
        Route::post('update-status', 'Admin\Journal\BlogController@updateStatus')->name('admin.pages.blog.update_blog_status');
        Route::get('/add-post', 'Admin\Journal\BlogController@create')->name('admin.pages.blog.create_blog');
        Route::post('/store-post', 'Admin\Journal\BlogController@store')->name('admin.pages.blog.store_blog');
        Route::get('/edit-post/{id}', 'Admin\Journal\BlogController@edit')->name('admin.pages.blog.edit_blog');
        Route::post('/update-post/{id}', 'Admin\Journal\BlogController@update')->name('admin.pages.blog.update_blog');
        Route::post('/delete-post/{id}', 'Admin\Journal\BlogController@destroy')->name('admin.pages.blog.delete_blog');
        Route::post('/bulk-delete-post', 'Admin\Journal\BlogController@bulkDestroy')->name('admin.pages.blog.bulk_delete_blog');
      });
    });
    // blog route end

    // footer route start
    Route::prefix('/footer')->group(function () {
      // logo & image route
      Route::get('/logo-and-image', 'Admin\Footer\ImageController@index')->name('admin.pages.footer.logo_and_image');
      Route::post('/update-logo', 'Admin\Footer\ImageController@updateLogo')->name('admin.pages.footer.update_logo');
      Route::post('/update-background-image', 'Admin\Footer\ImageController@updateImage')->name('admin.pages.footer.update_background_image');

      // content route
      Route::get('/content', 'Admin\Footer\ContentController@index')->name('admin.pages.footer.content');
      Route::post('/update-content', 'Admin\Footer\ContentController@update')->name('admin.pages.footer.update_content');

      // quick link route
      Route::get('/quick-links', 'Admin\Footer\QuickLinkController@index')->name('admin.pages.footer.quick_links');
      Route::post('/store-quick-link', 'Admin\Footer\QuickLinkController@store')->name('admin.pages.footer.store_quick_link');
      Route::post('/update-quick-link', 'Admin\Footer\QuickLinkController@update')->name('admin.pages.footer.update_quick_link');
      Route::post('/delete-quick-link/{id}', 'Admin\Footer\QuickLinkController@destroy')->name('admin.pages.footer.delete_quick_link');
    });
    // footer route end

    // seo route
    Route::get('/seo', 'Admin\BasicSettings\SEOController@index')->name('admin.settings.seo');
    Route::post('/update-seo', 'Admin\BasicSettings\SEOController@update')->name('admin.settings.update_seo');

    // // breadcrumb route
    Route::prefix('breadcrumbs')->group(function () {
      Route::get('/image', 'Admin\BasicSettings\BasicController@breadcrumb')->name('admin.pages.breadcrumb.image');
      Route::post('/update-breadcrumb', 'Admin\BasicSettings\BasicController@updateBreadcrumb')->name('admin.pages.breadcrumb.image.update');

      Route::get('/headings', 'Admin\BasicSettings\PageHeadingController@pageHeadings')->name('admin.pages.breadcrumb.headings');
      Route::post('/update-page-headings', 'Admin\BasicSettings\PageHeadingController@updatePageHeadings')->name('admin.pages.breadcrumb.headings.update');
    });
    //contact page route
    Route::get('/contact-page', 'Admin\BasicSettings\BasicController@contact_page')->name('admin.pages.contact_page');

    Route::post('/update-contact-page', 'Admin\BasicSettings\BasicController@update_contact_page')->name('admin.pages.contact_page.update');

    // additional-pages route
    Route::prefix('/additional-pages')->group(function () {
      Route::get('/all-pages', 'Admin\CustomPageController@index')->name('admin.pages.additional_pages');
      Route::get('/create-page', 'Admin\CustomPageController@create')->name('admin.pages.additional_pages.create_page');
      Route::post('/store-page', 'Admin\CustomPageController@store')->name('admin.pages.additional_pages.store_page');
      Route::get('/edit-page/{id}', 'Admin\CustomPageController@edit')->name('admin.pages.additional_pages.edit_page');
      Route::post('/update-page/{id}', 'Admin\CustomPageController@update')->name('admin.pages.additional_pages.update_page');
      Route::post('/delete-page/{id}', 'Admin\CustomPageController@destroy')->name('admin.pages.additional_pages.delete_page');
      Route::post('/bulk-delete-page', 'Admin\CustomPageController@bulkDestroy')->name('admin.pages.additional_pages.bulk_delete_page');
    });
    // additional-pages route end
  });
});
