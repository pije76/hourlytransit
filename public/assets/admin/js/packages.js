
"use strict";
$(document).ready(function () {
  $(".amenities-box").hide();
  $(".social-links-box").hide();
  $(".additional-specification-box").hide();
  $(".FAQ-box").hide();
  $("#productEnquiryFormLabel").hide();
  $(".Products-box").hide();
  $(".image-product-box").hide();

  $(".selectgroup-input").on('click', function () {
    var val = $(this).val()
    if (val == 'Amenities') {
      if ($(this).is(":checked")) {
        $(".amenities-box").show();
      } else {
        $(".amenities-box").hide();
      }
    }
    if (val == 'Social Links') {
      if ($(this).is(":checked")) {
        $(".social-links-box").show();
      } else {
        $(".social-links-box").hide();
      }
    }
    if (val == 'Additional Specification') {
      if ($(this).is(":checked")) {
        $(".additional-specification-box").show();
      } else {
        $(".additional-specification-box").hide();
      }
    }
    if (val == 'FAQ') {
      if ($(this).is(":checked")) {
        $(".FAQ-box").show();
      } else {
        $(".FAQ-box").hide();
      }
    }
    if (val == 'Products') {
      if ($(this).is(":checked")) {
        $("#productEnquiryFormLabel").show();
        $(".Products-box").show();
        $(".image-product-box").show();
      } else {
        $("#productEnquiryFormLabel").hide();
        $(".Products-box").hide();
        $(".image-product-box").hide();
      }
    }
  })
});
