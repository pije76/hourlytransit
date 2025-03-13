<div class="col-lg-5">
  <table class="table table-striped border">
    <thead>
      <tr>
        <th scope="col">{{ __('BB Code') }}</th>
        <th scope="col">{{ __('Meaning') }}</th>
      </tr>
    </thead>
    <tbody>
      @if ($templateInfo->mail_type == 'verify_email')
        <tr>
          <td>{username}</td>
          <td scope="row">{{ __('Username of User') }}</td>
        </tr>
      @endif

      @if ($templateInfo->mail_type == 'verify_email')
        <tr>
          <td>{verification_link}</td>
          <td scope="row">{{ __('Email Verification Link') }}</td>
        </tr>
      @endif


      @if (
          $templateInfo->mail_type == 'reset_password' ||
              $templateInfo->mail_type == 'product_order' ||
              $templateInfo->mail_type == 'payment_received_for_room_booking' ||
              $templateInfo->mail_type == 'payment_cancelled_for_room_booking' ||
              $templateInfo->mail_type == 'room_booking' ||
              $templateInfo->mail_type == 'inform_vendor_about_room_booking')
        <tr>
          <td>{customer_name}</td>
          <td scope="row">{{ __('Name of The Customer') }}</td>
        </tr>
      @endif
      @if ($templateInfo->mail_type == 'payment_accepted_for_featured_online_gateway')
        <tr>
          <td>{payment_via}</td>
          <td scope="row">{{ __('Payment Method Name') }}</td>
        </tr>
        <tr>
          <td>{package_price}</td>
          <td scope="row">{{ __('Pament Amount') }}</td>
        </tr>
      @endif




      @if ($templateInfo->mail_type == 'balance_add' || $templateInfo->mail_type == 'balance_subtract')
        <tr>
          <td>{amount}</td>
          <td scope="row">{{ __('Balance add/substract  amount') }}</td>
        </tr>
      @endif

      @if ($templateInfo->mail_type == 'reset_password')
        <tr>
          <td>{password_reset_link}</td>
          <td scope="row">{{ __('Password Reset Link') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'product_order' ||
              $templateInfo->mail_type == 'room_booking' ||
              $templateInfo->mail_type == 'inform_vendor_about_room_booking')
        <tr>
          <td>{order_number}</td>
          <td scope="row">{{ __('Order Number') }}</td>
        </tr>
      @endif

      @if ($templateInfo->mail_type == 'product_order')
        <tr>
          <td>{order_link}</td>
          <td scope="row">{{ __('Link to View Order Details') }}</td>
        </tr>
      @endif



      @if (
          $templateInfo->mail_type != 'verify_email' &&
              $templateInfo->mail_type != 'reset_password' &&
              $templateInfo->mail_type != 'product_order' &&
              $templateInfo->mail_type != 'payment_received_for_room_booking' &&
              $templateInfo->mail_type != 'payment_cancelled_for_room_booking' &&
              $templateInfo->mail_type != 'room_booking')
        <tr>
          <td>{username}</td>
          <td scope="row">{{ __('Username of Vendor') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'admin_removed_next_package' ||
              $templateInfo->mail_type == 'admin_removed_current_package')
        <td>{removed_package_title}</td>
        <td scope="row">{{ __('Package Name') }}</td>
      @endif

      @if (
          $templateInfo->mail_type == 'withdrawal_request_rejected' ||
              $templateInfo->mail_type == 'withdrawal_request_approved')
        <tr>
          <td>{withdraw_id}</td>
          <td scope="row">{{ __('withdraw Id') }}</td>
        </tr>
        <tr>
          <td>{current_balance}</td>
          <td scope="row">{{ __('Current Balance') }}</td>
        </tr>
      @endif

      @if ($templateInfo->mail_type == 'withdrawal_request_approved')
        <tr>
          <td>{withdraw_amount}</td>
          <td scope="row">{{ __('Withdraw Amount') }}</td>
        </tr>
        <tr>
          <td>{charge}</td>
          <td scope="row">{{ __('Charge') }}</td>
        </tr>
        <tr>
          <td>{payable_amount}</td>
          <td scope="row">{{ __('Payable Amount') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'hotel_feature_request_rejected' ||
              $templateInfo->mail_type == 'hotel_feature_request_approved' ||
              $templateInfo->mail_type == 'payment_to_feature_hotel_accepted_(_offline_payment_gateway_)' ||
              $templateInfo->mail_type == 'payment_to_feature_hotel_accepted_(_offline_payment_gateway_)')
        <tr>
          <td>{hotel_title}</td>
          <td scope="row">{{ __('Hotel Title') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'room_feature_request_rejected' ||
              $templateInfo->mail_type == 'room_feature_request_approved' ||
              $templateInfo->mail_type == 'payment_to_feature_room_accepted_(_offline_payment_gateway_)' ||
              $templateInfo->mail_type == 'payment_to_feature_room_rejected_(_offline_payment_gateway_)')
        <tr>
          <td>{room_title}</td>
          <td scope="row">{{ __('Room Title') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'payment_to_feature_hotel_rejected_(_offline_payment_gateway_)' ||
              $templateInfo->mail_type == 'payment_to_feature_hotel_accepted_(_offline_payment_gateway_)' ||
              $templateInfo->mail_type == 'payment_to_feature_room_accepted_(_offline_payment_gateway_)' ||
              $templateInfo->mail_type == 'payment_to_feature_room_rejected_(_offline_payment_gateway_)')
        <tr>
          <td>{package_price}</td>
          <td scope="row">{{ __('Price for Feature') }}</td>
        </tr>
        <tr>
          <td>{payment_via}</td>
          <td scope="row">{{ __('Payment Method') }}</td>
        </tr>
      @endif
      @if ($templateInfo->mail_type == 'hotel_feature_request_approved' || $templateInfo->mail_type == 'room_feature_request_approved')
        <tr>
          <td>{days}</td>
          <td scope="row">{{ __('Number of Days') }}</td>
        </tr>
        <tr>
          <td>{activation_date}</td>
          <td scope="row">{{ __('Activation Date') }}</td>
        </tr>
        <tr>
          <td>{end_date}</td>
          <td scope="row">{{ __('Expire Date') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'admin_changed_current_package' ||
              $templateInfo->mail_type == 'admin_changed_next_package')
        <tr>
          <td>{replaced_package}</td>
          <td scope="row">{{ __('Replace Package Name') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'admin_changed_current_package' ||
              $templateInfo->mail_type == 'admin_added_current_package' ||
              $templateInfo->mail_type == 'admin_changed_next_package' ||
              $templateInfo->mail_type == 'admin_added_next_package' ||
              $templateInfo->mail_type == 'membership_extend' ||
              $templateInfo->mail_type == 'registration_with_premium_package' ||
              $templateInfo->mail_type == 'payment_rejected_for_membership_(_offline_gateway_)' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_(_offline_gateway_)' ||
              $templateInfo->mail_type == 'registration_with_trial_package' ||
              $templateInfo->mail_type == 'registration_with_free_package' ||
              $templateInfo->mail_type == 'subscription_package_purchase' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_extension_offline_gateway' ||
              $templateInfo->mail_type == 'payment_accepted_for_registration_offline_gateway' ||
              $templateInfo->mail_type == 'payment_rejected_for_membership_extension_offline_gateway' ||
              $templateInfo->mail_type == 'payment_rejected_for_registration_offline_gateway')
        <tr>
          <td>{package_title}</td>
          <td scope="row">{{ __('Package Name') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'admin_changed_current_package' ||
              $templateInfo->mail_type == 'admin_added_current_package' ||
              $templateInfo->mail_type == 'payment_rejected_for_membership_(_offline_gateway_)' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_(_offline_gateway_)' ||
              $templateInfo->mail_type == 'admin_changed_next_package' ||
              $templateInfo->mail_type == 'admin_added_next_package' ||
              $templateInfo->mail_type == 'subscription_package_purchase' ||
              $templateInfo->mail_type == 'membership_extend' ||
              $templateInfo->mail_type == 'registration_with_premium_package' ||
              $templateInfo->mail_type == 'registration_with_trial_package' ||
              $templateInfo->mail_type == 'registration_with_free_package' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_extension_offline_gateway' ||
              $templateInfo->mail_type == 'payment_accepted_for_registration_offline_gateway' ||
              $templateInfo->mail_type == 'payment_rejected_for_membership_extension_offline_gateway' ||
              $templateInfo->mail_type == 'payment_rejected_for_registration_offline_gateway')
        <tr>
          <td>{package_price}</td>
          <td scope="row">{{ __('Price of Package') }}</td>
        </tr>
      @endif

      @if ($templateInfo->mail_type == 'registration_with_premium_package')
        <tr>
          <td>{discount}</td>
          <td scope="row">{{ __('Discount Amount') }}</td>
        </tr>
      @endif
      @if ($templateInfo->mail_type == 'registration_with_premium_package')
        <tr>
          <td>{total}</td>
          <td scope="row">{{ __('Total Paid Amount') }}</td>
        </tr>
      @endif

      @if (
          $templateInfo->mail_type == 'admin_changed_current_package' ||
              $templateInfo->mail_type == 'admin_added_current_package' ||
              $templateInfo->mail_type == 'admin_changed_next_package' ||
              $templateInfo->mail_type == 'admin_added_next_package' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_(_offline_gateway_)' ||
              $templateInfo->mail_type == 'membership_extend' ||
              $templateInfo->mail_type == 'subscription_package_purchase' ||
              $templateInfo->mail_type == 'registration_with_premium_package' ||
              $templateInfo->mail_type == 'registration_with_trial_package' ||
              $templateInfo->mail_type == 'registration_with_free_package' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_extension_offline_gateway' ||
              $templateInfo->mail_type == 'payment_accepted_for_registration_offline_gateway')
        <tr>
          <td>{activation_date}</td>
          <td scope="row">{{ __('Package activation date') }}</td>
        </tr>
      @endif
      @if (
          $templateInfo->mail_type == 'admin_changed_current_package' ||
              $templateInfo->mail_type == 'admin_added_current_package' ||
              $templateInfo->mail_type == 'admin_changed_next_package' ||
              $templateInfo->mail_type == 'admin_added_next_package' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_(_offline_gateway_)' ||
              $templateInfo->mail_type == 'membership_extend' ||
              $templateInfo->mail_type == 'subscription_package_purchase' ||
              $templateInfo->mail_type == 'registration_with_premium_package' ||
              $templateInfo->mail_type == 'registration_with_trial_package' ||
              $templateInfo->mail_type == 'registration_with_free_package' ||
              $templateInfo->mail_type == 'payment_accepted_for_membership_extension_offline_gateway' ||
              $templateInfo->mail_type == 'payment_accepted_for_registration_offline_gateway')
        <tr>
          <td>{expire_date}</td>
          <td scope="row">{{ __('Package expire date') }}</td>
        </tr>
      @endif

      @if ($templateInfo->mail_type == 'membership_expiry_reminder')
        <tr>
          <td>{last_day_of_membership}</td>
          <td scope="row">{{ __('Package expire last date') }}</td>
        </tr>
      @endif
      @if ($templateInfo->mail_type == 'membership_expiry_reminder' || $templateInfo->mail_type == 'membership_expired')
        <tr>
          <td>{login_link}</td>
          <td scope="row">{{ __('Login Url') }}</td>
        </tr>
      @endif

      <tr>
        <td>{website_title}</td>
        <td scope="row">{{ __('Website Title') }}</td>
      </tr>
    </tbody>
  </table>
</div>
