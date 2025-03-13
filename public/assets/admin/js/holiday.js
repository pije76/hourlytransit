function updateHoliday(holidayId, holidayValue) {

  $.ajax({
    url: storeHoliday,
    type: "POST",
    data: {
      'holidayId': holidayId,
      'holiday': holidayValue,
    },
    success: function (response) {
     
      location.reload();
    },
    error: function (xhr, status, error) {
      alert('Something wrong!');
    }
  });
}
