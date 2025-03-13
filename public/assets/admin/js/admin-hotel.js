"use strict";
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
$(document).ready(function () {
  $('.js-example-basic-single1').select2();
  $('.js-example-basic-single2').select2();
  $('.js-example-basic-single3').select2();
  $('.js-example-basic-single4').select2();
  $('.js-example-basic-single5').select2();
  $('.js-example-basic-single6').select2();
  $('.js-example-basic-single7').select2();
  $('.js-example-basic-single8').select2();
});

$('body').on('change', '.js-example-basic-single3', function () {
  var id = $(this).val();
  var lang = $(this).attr('data-code');
  var added = lang + "_country_state_id";
  var hh = lang + "_hide_state";
  var added2 = lang + "_state_city_id";

  $('.' + added + ' option').remove();
  $('.' + added2 + ' option').remove();
  $.ajax({
    type: 'POST',
    url: getStateUrl,
    data: {
      id: id,
      lang: lang
    },
    success: function (data) {
      if (data) {
        console.log(data);
        if (data.states && data.states.length > 0) {

          $('.' + hh).removeClass('d-none');

          $('.' + added).append($('<option>', {
            value: '',
            text: 'Select State',
            disabled: true,
            selected: true
          }));

          $.each(data.states, function (key, value) {
            $('.' + added).append($('<option></option>').val(value.id).html(value
              .name));
          });
          $('.' + added2).append($('<option>', {
            value: '',
            text: 'Select City',
            disabled: true,
            selected: true
          }));
        } else {
          $('.' + hh).addClass('d-none');

          $('.' + added2).append($('<option>', {
            value: '',
            text: 'Select City',
            disabled: true,
            selected: true
          }));
          $.each(data.cities, function (key, value) {
            $('.' + added2).append($('<option></option>').val(value.id).html(value
              .name));
          });
        }
      } else {

      }
    }
  });
});

$('body').on('change', '.js-example-basic-single4', function () {
  var id = $(this).val();
  var lang = $(this).attr('data-code');
  var added = lang + "_state_city_id";

  $('.' + added + ' option').remove();
  $.ajax({
    type: 'POST',
    url: getCityUrl,
    data: {
      id: id,
      lang: lang
    },
    success: function (data) {

      if (data && data.length > 0) {
        console.log(data);

        $('.' + added).append($('<option>', {
          value: '',
          text: 'Select City',
          disabled: true,
          selected: true
        }));

        $.each(data, function (key, value) {
          $('.' + added).append($('<option></option>').val(value.id).html(value
            .name));
        });
      } else {
        $('.' + added).append($('<option>', {
          value: '',
          text: 'No cities available',
          disabled: true,
          selected: true
        }));
      }
    }
  });
});



function bootnotify(message, title, type) {
  var content = {};

  content.message = message;
  content.title = title;
  content.icon = 'fa fa-bell';

  $.notify(content, {
    type: type,
    placement: {
      from: 'top',
      align: 'right'
    },
    showProgressbar: true,
    time: 1000,
    allow_dismiss: true,
    delay: 4000
  });
}

$('#hotelForm').on('submit', function (e) {

  e.preventDefault();
  let can_hotel_add = $('button[type=submit]').data('can_hotel_add');

  if (can_hotel_add == 0) {
    bootnotify(PleaseBuyaplantoaddaHotel, Alert, 'warning');
    return false;
  } else if (can_hotel_add == 2) {

    $("#checkLimitModal").modal('show');

    bootnotify(Hotellimitreachedorexceeded, Alert, 'warning');
    return false;
  }
  $('.request-loader').addClass('show');

  let action = $(this).attr('action');
  let fd = new FormData($(this)[0]);

  //if iconpecker

  if ($(".aaa").length > 0) {
    var textarea = $("<textarea></textarea>");

    // You can set attributes or properties for the textarea if needed
    textarea.attr("name", "icons");
    textarea.attr("id", "icons");
    textarea.attr("class", "form-control");
    textarea.attr("type", "hidden");

    // Append the textarea to the form with the id "myForm"
    $(this).append(textarea);
    var iconArray = [];
    $('.aaa').each(function () {
      var icon = $(this).find('i').attr('class');
      iconArray.push(icon);
    })
    console.log(iconArray);
    $("#icons").html(iconArray);
    fd.delete($('#icons').attr('name'));
    fd.append($('#icons').attr('name'), iconArray);
  }


  //if summernote has then get summernote content
  $('.form-control').each(function (i) {
    let index = i;

    let $toInput = $('.form-control').eq(index);

    if ($(this).hasClass('summernote')) {
      let tmcId = $toInput.attr('id');
      let content = tinyMCE.get(tmcId).getContent();
      fd.delete($(this).attr('name'));
      fd.append($(this).attr('name'), content);
    }
  });

  $.ajax({
    url: action,
    method: 'POST',
    data: fd,
    contentType: false,
    processData: false,
    success: function (data) {
      if (data.limit_error == true) {
        location.reload();
      }

      $('.request-loader').removeClass('show');

      if (data.status == 'success') {
        location.reload();
      } else if (data.status == 'error') {

        location.reload();
      }

      if (data == "downgrade") {
        $('.modal').modal('hide');

        var content = {};
        content.message = "Your Package limit reached or exceeded!"
        content.title = "Warning";
        content.icon = 'fa fa-bell';

        $.notify(content, {
          type: 'warning',
          placement: {
            from: 'top',
            align: 'right'
          },
          showProgressbar: true,
          time: 1000,
          delay: 4000,
        });
        $("#checkLimitModal").modal('show');
      }

    },
    error: function (error) {
      let errors = ``;

      for (let x in error.responseJSON.errors) {
        errors += `<li>
                <p class="text-danger mb-0">${error.responseJSON.errors[x][0]}</p>
              </li>`;
      }

      $('#hotelErrors ul').html(errors);
      $('#hotelErrors').show();

      $('.request-loader').removeClass('show');

      $('html, body').animate({
        scrollTop: $('#hotelErrors').offset().top - 100
      }, 1000);
    }
  });
});



$('body').on('click', '.input-checkbox', function () {

  var selectedValues = [];

  let code = $(this).data('code');
  let languageId = $(this).data('language_id');
  let hotelId = $(this).data('listing_id');
  var checkboxClass = code + "_input-checkbox";

  $("." + checkboxClass + ":checked").each(function () {
    selectedValues.push($(this).val());
  });

  var selectedValuesString = selectedValues.join(',');

  $.ajax({
    url: updateAminitie,
    method: 'POST',
    data: {
      aminities: selectedValuesString,
      languageId: languageId,
      hotelId: hotelId,
    },

    success: function (data) {

      if (data.status == 'success') {

        $('.request-loader').removeClass('show');
        location.reload();
      }
    },
    error: function (error) {
      if (data.status == 'success') {

        var content = {};

        content.message = 'Something went worng!';
        content.title = "Warning";
        content.icon = 'fa fa-bell';

        $.notify(content, {
          type: 'warning',
          placement: {
            from: 'top',
            align: 'right'
          },
          showProgressbar: true,
          time: 1000,
          delay: 4000,
        });
      }
    }
  });
});
//remove existing images
$(document).on('click', '.videoimagermvbtndb', function () {
  let indb = $(this).data('indb');
  $(".request-loader").addClass("show");
  $.ajax({
    url: videormvdbUrl,
    type: 'POST',
    data: {
      fileid: indb
    },
    success: function (data) {
      if (data.status == 'success') {
        $('.request-loader').removeClass('show');

        location.reload();

      }
    }
  });
});

//video image preview and remove
$('.video-img-input').on('change', function (event) {
  let file = event.target.files[0];
  let reader = new FileReader();

  reader.onload = function (e) {
    $('.uploaded-img2').attr('src', e.target.result);
    $('.remove-img2').show();
  };

  reader.readAsDataURL(file);
});

$('.remove-img2').on('click', function () {
  $('.video-img-input').val('');
  $('.uploaded-img2').attr('src', '{{ asset("assets/img/noimage.jpg") }}');
  $(this).hide();
});


