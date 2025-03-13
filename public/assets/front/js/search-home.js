"use strict";
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
$("#searchBtn2").on('click', function (e) {
  e.preventDefault();

  var formData = $('#searchForm2').serializeArray();
  var queryParams = [];

  $.each(formData, function (index, input) {
    if (input.value !== '') {
      queryParams.push(encodeURIComponent(input.name) + '=' + encodeURIComponent(input.value));
    }
  });

  var queryString = queryParams.join('&');
  var newUrl = baseURL + '/rooms';

  if (queryString !== '') {
    newUrl += '?' + queryString;
  }

  // Update the browser URL without reloading the page
  window.location.href = newUrl;
}); // <-- Added closing parenthesis and semicolon
$('input[name="checkInTimes"]').daterangepicker({
  opens: 'left',
  timePicker: true,
  "singleDatePicker": true,
  timePickerIncrement: 1,
  timePicker24Hour: timePicker,
  locale: {
    format: timeFormate
  }
}).on('show.daterangepicker', function (ev, picker) {
  picker.container.find(".calendar-table").hide();
})

$('input[name="checkInTimes"]').on('apply.daterangepicker', function (ev, picker) {
  $(this).val(picker.startDate.format(timeFormate));
});

$('input[name="checkInTimes"]').on('cancel.daterangepicker', function (ev, picker) {
  $(this).val('');
});
// Check-in
$('input[name="checkInDates"]').daterangepicker({
  "singleDatePicker": true,
  autoUpdateInput: true,
  minDate: moment().format('MM/DD/YYYY'),
});
$('input[name="checkInDates"]').on('apply.daterangepicker', function (ev, picker) {
  $(this).val(picker.startDate.format('MM/DD/YYYY'));
});
$('input[name="checkInDates"]').on('cancel.daterangepicker', function (ev, picker) {
  $(this).val('');
});
