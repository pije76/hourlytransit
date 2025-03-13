"use strict";
$(document).ready(function () {

  // Initialize daterangepicker on input field
  $('#checkInDatetime').daterangepicker({
    singleDatePicker: true,
    timePicker: true,
    timePicker24Hour: timePicker,
    timePickerIncrement: 1,
    minDate: moment().format('MM/DD/YYYY'),
    autoUpdateInput: false,
    showDropdowns: true,
    locale: {
      format: `MM/DD/YYYY ${timeFormate}`,
      applyLabel: 'Apply',
      cancelLabel: 'Cancel'
    },
    alwaysShowCalendars: true
  });

  // Ensure calendar opens when field is focused or clicked
  $('#checkInDatetime').on('focus click', function () {
    $(this).data('daterangepicker').show();
  });


  $('#checkInDatetime').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format(`MM/DD/YYYY ${timeFormate}`));

    var selectedDate = picker.startDate.format('MM/DD/YYYY');
    var selectedTime = picker.startDate.format(timeFormate); 

    var convertedTime = convertTo24Hour(selectedTime);

    $('#checkInDates').val(selectedDate);
    $('#checkInTimes').val(convertedTime);

    $('#page').val(1);
    updateUrl();
  });

  function convertTo24Hour(time) {
    var timeArray = time.split(':');
    var hours = parseInt(timeArray[0]);
    var minutes = parseInt(timeArray[1]);
    var isPM = time.toLowerCase().indexOf('pm') > -1;

    if (isPM && hours < 12) {
      hours += 12;
    } else if (!isPM && hours == 12) {
      hours = 0;
    }

    return padZero(hours) + ':' + padZero(minutes) + ':00';
  }

  function padZero(num) {
    return (num < 10) ? '0' + num : num;
  }


  $('body').on('keypress', '#searchBytTitle', function (event) {
    if (event.which === 13) {
      $('#title').val($(this).val());
      $('#page').val(1);
      updateUrl();
    }
  });
  if (googleApiStatus === 0) {
    $('body').on('keydown', '#location', function (event) {
      if (event.keyCode === 13) {
        $('#location_val').val($(this).val());
        updateUrl("location_val");
      }
    });
  }

  $('body').on('click', '.hour', function () {
    $('#hour').val(($(this).val()));
    $('#page').val(1);

    updateUrl();
  });

  $('body').on('click', '.page-link', function () {

    var page = $(this).data('page');
    $('#page').val(page);
    updateUrl();
  });

  $('.category-toggle').on('click', function () {
    $('#category').val($(this).attr('id'));
    updateUrl();
  });

  $('body').on('change', '.vendorDropdown', function () {
    var selectedVendorId = $(this).val();
    $('#vendor').val(selectedVendorId);
    $('#page').val(1);
    updateUrl();
  });

  $('body').on('change', '.countryDropdown', function () {
    var id = $(this).val();
    $('#country').val(id);
    $('#state').val('');
    $('#city').val('');
    $('#page').val(1);
    updateUrl();


    $('#stateDropdown option').remove();
    $('#cityDropdown   option').remove();

    $.ajax({
      type: 'POST',
      url: getStateUrl,
      data: {
        id: id,
      },
      success: function (data) {

        console.log(data);

        if (data) {
          if (data.states && data.states.length > 0) {

            $('.hide_state').show();

            $('#stateDropdown').append($('<option>', {
              value: '',
              text: 'Select State',
              disabled: true,
              selected: true
            }));
            $('#stateDropdown').append($('<option>', {
              value: '',
              text: 'All',
              disabled: false,
              selected: false
            }));

            $.each(data.states, function (key, value) {
              $('#stateDropdown').append($('<option></option>').val(value.id).html(value
                .name));
            });
            $('#cityDropdown').append($('<option>', {
              value: '',
              text: 'Select City',
              disabled: true,
              selected: true
            }));
          } else {
            $('.hide_state').hide();

            $('#cityDropdown').append($('<option>', {
              value: '',
              text: 'Select City',
              disabled: true,
              selected: true
            }));
            $.each(data.cities, function (key, value) {
              $('#cityDropdown').append($('<option></option>').val(value.id).html(value
                .name));
            });
          }
        } else {
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error: " + status, error);
      },
      async: true,
    });
  });

  $('body').on('change', '.stateDropdown', function () {
    var selectedStateId = $(this).val();
    $('#state').val(selectedStateId);
    $('#city').val('');
    $('#page').val(1);
    updateUrl();

    $('#cityDropdown  option').remove();
    $.ajax({
      type: 'POST',
      url: getCityUrl,
      data: {
        id: selectedStateId,
      },
      success: function (data) {
        if (data && data.length > 0) {
          $('#cityDropdown').append($('<option>', {
            value: '',
            text: 'Select Cities',
            disabled: true,
            selected: true
          }));
          $('#cityDropdown').append($('<option>', {
            value: '',
            text: 'All'
          }));
          $.each(data, function (key, value) {
            $('#cityDropdown').append($('<option></option>').val(value.id).html(value.name));
          });
        } else {
          $('#cityDropdown').append($('<option>', {
            value: '',
            text: 'Select City',
            disabled: true,
            selected: true
          }));
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error: " + status, error);
      },
      async: true,
    });
  });

  $('body').on('change', '.cityDropdown', function () {
    var selectedCityId = $(this).val();
    $('#city').val(selectedCityId);
    $('#page').val(1);
    updateUrl();
  });

  $('body').on('change', '.ratingDropdown', function () {
    var selectedRating = $(this).val();
    $('#ratings').val(selectedRating);
    $('#page').val(1);
    updateUrl();
  });

  $('body').on('change', '.starsDropdown', function () {
    var selectedStars = $(this).val();
    $('#stars').val(selectedStars);
    $('#page').val(1);
    updateUrl();
  });


  $('body').on('change', '#select_sort', function () {
    $('#sort').val($(this).val());
    $('#page').val(1);
    updateUrl();
  });

  $('body').on('click', '.input-checkbox', function () {
    var selectedValues = [];

    $(".input-checkbox:checked").each(function () {
      selectedValues.push($(this).val());
    });

    var selectedValuesString = selectedValues.join(',');

    $("#amenitie").val(selectedValuesString);
    $('#page').val(1);
    updateUrl();
  });


  function updateUrl() {
    $('#searchForm').submit();
    $(".request-loader").addClass("show");
  }

  $('#searchForm').on('submit', function (e) {

    e.preventDefault();
    var fd = $(this).serialize();
    $('.search-container').html('');
    $.ajax({
      url: searchUrl,
      method: "get",
      data: fd,
      contentType: false,
      processData: false,
      success: function (response) {
        $('.request-loader').removeClass('show');
        $('.search-container').html(response);

        $('#countryDropdown').select2();
        $('#stateDropdown').select2();
        $('#cityDropdown').select2();

        if (clusters) {
          map.removeLayer(clusters);
          clusters.clearLayers();
        }
        map.off();
        map.remove();

        var featured_content = featured_contents;
        var hotel_content = hotel_contentss;
        mapInitialize(featured_content, hotel_content);
      },
      error: function (xhr) {
        console.log(xhr);
      }
    });
  });
});


