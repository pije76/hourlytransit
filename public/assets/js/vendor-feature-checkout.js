"use strict";

$(document).ready(function () {

  $('body').on('click', '.featurePaymentModal', function () {
    var listing = $(this).data('id');

    var Gateway = "gateway_" + listing;
    var errGateway = "err_gateway_" + listing;
    var anetCardNumber = "anetCardNumber_" + listing;
    var anetExpMonth = "anetExpMonth_" + listing;
    var anetExpYear = "anetExpYear_" + listing;
    var anetCardCode = "anetCardCode_" + listing;
    var anetErrors = "anetErrors_" + listing;
    var paymentForm = "payment-form_" + listing;
    var stripelement = "stripe-element_" + listing;

    $('#' + stripelement).addClass('d-none');

    $('body').on('change', '#' + Gateway, function () {
      var listingId = $(this).data('listing_id');
      var offLineGatewayInfo = "offline-gateway-info_" + listingId;
      var tabAnet = "tab-anet_" + listingId;


      console.log(stripelement);


      let value = $(this).val();

      let dataType = parseInt(value);

      if (isNaN(dataType)) {
        // hide offline gateway informations
        if ($('.' + offLineGatewayInfo).hasClass('d-block')) {
          $('.' + offLineGatewayInfo).removeClass('d-block');

        }

        $('.' + offLineGatewayInfo).addClass('d-none');
        // show or hide authorize card inputs

        if (value == 'authorize.net') {
          $("#" + tabAnet).show();
          $('#' + tabAnet + ' input').removeAttr('disabled');
        } else {
          $("#" + tabAnet).hide();
        }

        // show or hide stripe card inputs
        if (value == 'stripe') {
          $('#' + stripelement).removeClass('d-none');
        } else {
          $('#' + stripelement).addClass('d-none');
        }
      } else {
        // hide stripe gateway card inputs
        if (!$('#' + stripelement).hasClass('d-none')) {
          $('#' + stripelement).addClass('d-none');
          $('#' + stripelement).removeClass('d-block');
        }

        // hide offline gateway informations
        if ($('.' + offLineGatewayInfo).hasClass('d-block')) {
          $('.' + offLineGatewayInfo).removeClass('d-block');
        }

        $('.' + offLineGatewayInfo).addClass('d-none');

        // show particular offline gateway informations
        $('.' + offLineGatewayInfo).removeClass('d-none');
        $("#" + tabAnet).hide();
      }
    });

    var form = document.getElementById(paymentForm);
    form.addEventListener('submit', function (event) {
      event.preventDefault();


      if (($('#' + Gateway).val() == null)) {

        $('#' + errGateway).html('');
        document.getElementById(errGateway).innerHTML = 'Please select a payment method';

      } else {

        if ($('#' + Gateway).val() == 'stripe') {
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
        } else if ($('#' + Gateway).val() == 'authorize.net') {
          sendPaymentDataToAnet();
        } else {

          $('#' + paymentForm).submit();
        }
      }

    });

    function paymentFormUpdate(opaqueData) {
      document.getElementById("opaqueDataDescriptor").value = opaqueData.dataDescriptor;
      document.getElementById("opaqueDataValue").value = opaqueData.dataValue;
      document.getElementById(paymentForm).submit();
    }
    function sendPaymentDataToAnet() {
      // Set up authorisation to access the gateway.
      var authData = {};
      authData.clientKey = public_key;
      authData.apiLoginID = login_id;

      var cardData = {};
      cardData.cardNumber = document.getElementById(anetCardNumber).value;
      cardData.month = document.getElementById(anetExpMonth).value;
      cardData.year = document.getElementById(anetExpYear).value;
      cardData.cardCode = document.getElementById(anetCardCode).value;

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
        $("#" + anetErrors).show();
        $("#" + anetErrors).html(errorLists);
      } else {
        paymentFormUpdate(response.opaqueData);
      }
    }

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
      cardElement.mount('#' + stripelement);
    }

    // Send the token to your server
    function stripeTokenHandler(token) {
      // Add the token to the form data before submitting to the server
      var form = document.getElementById(paymentForm);
      var hiddenInput = document.createElement('input');
      hiddenInput.setAttribute('type', 'hidden');
      hiddenInput.setAttribute('name', 'stripeToken');
      hiddenInput.setAttribute('value', token.id);
      form.appendChild(hiddenInput);

      // Submit the form to your server
      form.submit();
    }
  });
})
