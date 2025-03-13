//===================================================
$('#vendorSelect').on('sumbit', function (e) {
  e.preventDefault();
  let vendor_id = $('#vendor_id').val();

  $('.request-loader').addClass('show');

  $.ajax({
    url: baseUrl + '/take-vendor/',
    method: 'get',
    data: { vendor_id: vendor_id },
    success: function (res) {
      $('.request-loader').removeClass('show');
    },
    error: function (error) {


      console.log("erfwer");
    }
  });
});
