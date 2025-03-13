$('thead').on('click', '.addRow', function (e) {
  e.preventDefault();
  var tr = `<tr>
                <td>
                  ${labels}
                </td>
                <td>
                  ${values}
                </td>
                <td>
                  <a href="javascript:void(0)" class="btn btn-danger  btn-sm deleteRow">
                    <i class="fas fa-minus"></i></a>
                </td>
              </tr>`;
  $('#tbody').append(tr);
});

$('tbody').on('click', '.deleteRow', function () {
  $(this).parent().parent().remove();
});
$('body').on('click', '.deleteCounter', function () {
  let feature = $(this).data('counter');
  $('.request-loader').addClass('show');
  $(this).parent().parent().remove();
  $.ajax({
    url: featureRmvUrl,
    method: 'POST',
    data: {
      spacificationId: feature,
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
