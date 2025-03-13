
"use strict";
$(document).ready(function () {
  
  $(".selectgroup-input").on('click', function () {
    var val = $(this).val();
    if (val == 'Amenities') {
      if ($(this).is(":checked")) {
        $(".amenities-box").removeClass('d-none');
      } else {
        $(".amenities-box").addClass('d-none');
      }
    }
    if (val == 'Social Links') {
      if ($(this).is(":checked")) {
        $(".social-links-box").removeClass('d-none');
      } else {
        $(".social-links-box").addClass('d-none');
      }
    }
    if (val == 'Additional Specification') {
      if ($(this).is(":checked")) {
        $(".additional-specification-box").removeClass('d-none');
      } else {
        $(".additional-specification-box").addClass('d-none');
      }
    }
    if (val == 'FAQ') {
      if ($(this).is(":checked")) {
        $(".FAQ-box").removeClass('d-none');
      } else {
        $(".FAQ-box").addClass('d-none');
      }
    }
    if (val == 'Products') {
      if ($(this).is(":checked")) {
        $("#productEnquiryFormLabel").removeClass('d-none');
        $(".Products-box").removeClass('d-none');
        $(".image-product-box").removeClass('d-none');
      } else {
        $("#productEnquiryFormLabel").addClass('d-none');
        $(".Products-box").addClass('d-none');
        $(".image-product-box").addClass('d-none');
      }
    }
  });
});

