"use strict";

$(document).ready(function () {
  $('#stripe-element').addClass('d-none');

  $('#coupon-code').on('keypress', function (e) {
    let key = e.which;

    if (key == 13) {
      applyCoupon(e);
    }
  });
})

///////////APPLY COUPON  //////////////
function applyCoupon(event) {
  event.preventDefault();

  let code = $('#coupon-code').val();
  let subtotal = $('#subtotal-amount').text();
  let id = $('#room-id').text();

  if (code) {
    let url = baseURL + '/room/apply-coupon';
    let data = {
      coupon: code,
      initTotal: subtotal,
      roomId: id,
      _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    $.post(url, data, function (response) {
      if ('success' in response) {
        $('#coupon-code').val('');
        $("#couponReload").load(location.href + " #couponReload");
        toastr['success'](response.success);
      } else if ('error' in response) {
        toastr['error'](response.error);
      }
    });
  } else {
    toastr['error']('Please enter your coupon code.');
  }
}

/////////////////////////ADIITIONAL SERVICE

$('input[name="additional_service[]').on('click', function () {
  var serviceCharge = 0;
  var services = [];

  $("input[name='additional_service[]']:checked").each(function () {
    serviceCharge += parseFloat($(this).data('shipping_charge'));
    services.push($(this).val());
  });
  var takeService = services.join(',');

  additionalService(serviceCharge, takeService);
});
function additionalService(serviceCharge, takeService) {

  let url = baseURL + '/room/add-additional-service';
  let data = {
    serviceCharge: serviceCharge,
    takeService: takeService,
    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  };

  $.post(url, data, function (response) {
    if ('success' in response) {
      $("#couponReload").load(location.href + " #couponReload");
      toastr['success'](response.success);
    } else if ('error' in response) {
      $("#couponReload").load(location.href + " #couponReload");
      toastr['error'](response.error);
    }
  });

}

$('select[name="gateway"]').on('change', function () {
  let value = $(this).val();

  let dataType = parseInt(value);

  if (isNaN(dataType)) {
    // hide offline gateway informations
    if ($('.offline-gateway-info').hasClass('d-block')) {
      $('.offline-gateway-info').removeClass('d-block');
    }

    $('.offline-gateway-info').addClass('d-none');
    // show or hide authorize card inputs

    if (value == 'authorize.net') {
      $("#tab-anet").show();
      $("#tab-anet input").removeAttr('disabled');

    } else {
      $("#tab-anet").hide();
    }

    // show or hide stripe card inputs
    if (value == 'stripe') {
      $('#stripe-element').removeClass('d-none');
      $('.iyzico-element').addClass('d-none');
    } else if (value == 'iyzico') {
      $('.iyzico-element').removeClass('d-none');
      $('#stripe-element').addClass('d-none');
    } else {
      $('#stripe-element').addClass('d-none');
      $('.iyzico-element').addClass('d-none');
    }
  } else {
    // hide stripe gateway card inputs
    if (!$('#stripe-element').hasClass('d-none')) {
      $('#stripe-element').addClass('d-none');
      $('#stripe-element').removeClass('d-block');
    }

    if (!$('#iyzico-element').hasClass('d-none')) {
      $('.iyzico-element').addClass('d-none');
    }

    // hide offline gateway informations
    if ($('.offline-gateway-info').hasClass('d-block')) {
      $('.offline-gateway-info').removeClass('d-block');
    }

    $('.offline-gateway-info').addClass('d-none');

    // show particular offline gateway informations
    $('#offline-gateway-' + value).removeClass('d-none');
  }
});
function sendPaymentDataToAnet() {
  // Set up authorisation to access the gateway.
  var authData = {};
  authData.clientKey = public_key;
  authData.apiLoginID = login_id;

  var cardData = {};
  cardData.cardNumber = document.getElementById("anetCardNumber").value;
  cardData.month = document.getElementById("anetExpMonth").value;
  cardData.year = document.getElementById("anetExpYear").value;
  cardData.cardCode = document.getElementById("anetCardCode").value;

  // Now send the card data to the gateway for tokenisation.
  // The responseHandler function will handle the response.
  var secureData = {};
  secureData.authData = authData;
  secureData.cardData = cardData;
  Accept.dispatchData(secureData, responseHandler);
}

function responseHandler(response) {
  if (response.messages.resultCode === "Error") {
    var i = 0;
    let errorLists = ``;
    while (i < response.messages.message.length) {
      errorLists += `<li class="text-danger">${response.messages.message[i].text}</li>`;

      i = i + 1;
    }
    $("#anetErrors").show();
    $("#anetErrors").html(errorLists);
  } else {
    paymentFormUpdate(response.opaqueData);
  }
}

function paymentFormUpdate(opaqueData) {
  document.getElementById("opaqueDataDescriptor").value = opaqueData.dataDescriptor;
  document.getElementById("opaqueDataValue").value = opaqueData.dataValue;
  document.getElementById("payment-form").submit();
}

// Set your Stripe public key
if (stripe_key) {
  var stripe = Stripe(stripe_key);

  // Create a Stripe Element for the card field
  var elements = stripe.elements();
  var cardElement = elements.create('card', {
    style: {
      base: {
        iconColor: '#454545',
        color: '#454545',
        fontWeight: '500',
        lineHeight: '50px',
        fontSmoothing: 'antialiased',
        backgroundColor: '#f2f2f2',
        ':-webkit-autofill': {
          color: '#454545',
        },
        '::placeholder': {
          color: '#454545',
        },
      }
    },
  });

  // Add an instance of the card Element into the `card-element` div
  cardElement.mount('#stripe-element');
}


// Handle form submission
var form = document.getElementById('payment-form');
form.addEventListener('submit', function (event) {
  event.preventDefault();
  if ($('#gateway').val() == 'stripe') {
    stripe.createToken(cardElement).then(function (result) {
      if (result.error) {
        // Display errors to the customer
        var errorElement = document.getElementById('stripe-errors');
        errorElement.textContent = result.error.message;
      } else {
        // Send the token to your server
        stripeTokenHandler(result.token);
      }
    });
  } else if ($('#gateway').val() == 'authorize.net') {
    sendPaymentDataToAnet();
  } else {
    $('#payment-form').submit();
  }
});

// Send the token to your server
function stripeTokenHandler(token) {
  // Add the token to the form data before submitting to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);

  // Submit the form to your server
  form.submit();
}
