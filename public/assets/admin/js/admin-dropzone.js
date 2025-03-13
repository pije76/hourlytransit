(function ($) {
  "use strict";

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  // Dropzone initialization
  Dropzone.options.myDropzone = {
    acceptedFiles: '.png, .jpg, .jpeg',
    url: storeUrl,
    maxFiles: galleryImages,
    success: function (file, response) {
      if (response.status == "success") {
        // alert(response.status);
        $("#sliders").append(`<input type="hidden" name="slider_images[]" id="slider${response.file_id}" value="${response.file_id}">`);


        // Create the remove button
        var removeButton = Dropzone.createElement("<button class='rmv-btn'><i class='fa fa-times'></i></button>");

        // Capture the Dropzone instance as closure.
        var _this = this;
        // Listen to the click event
        removeButton.addEventListener("click", function (e) {
          // Make sure the button click doesn't submit the form:
          e.preventDefault();
          e.stopPropagation();

          _this.removeFile(file);

          rmvimg(response.file_id);
        });

        // Add the button to the file preview element.
        file.previewElement.appendChild(removeButton);

        if (typeof response.error != 'undefined') {
          if (typeof response.file != 'undefined') {
            document.getElementById('errpreimg').innerHTML = response.file[0];
          }
        }
      }
    }
  };

  function rmvimg(fileid) {
    // you can do the AJAX request here.
    $(".request-loader").addClass("show");

    $.ajax({
      url: removeUrl,
      type: 'POST',
      data: {
        fileid: fileid
      },
      success: function (data) {
        $(".request-loader").removeClass("show");
        $("#slider" + fileid).remove();
      }
    });

  }

  //remove existing images
  $(document).on('click', '.rmvbtndb', function () {
    let indb = $(this).data('indb');
    $(".request-loader").addClass("show");
    $.ajax({
      url: rmvdbUrl,
      type: 'POST',
      data: {
        fileid: indb
      },
      success: function (data) {
        $(".request-loader").removeClass("show");
          if (data.status == 'success') {
            $('.request-loader').removeClass('show');

            location.reload();

          }
      }
    });
  });
})(jQuery);
