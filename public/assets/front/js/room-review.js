"use strict";
// get the rating (star) value in integer

$('.review-value span').on('click', function () {
  let ratingValue = $(this).attr('data-ratingVal');

  // first, remove '#FBA31C' color and add '#777777' color to the star
  $('.review-value span').css('color', '#777777');

  // second, add '#FBA31C' color to the selected parent class
  let parentClass = 'review-' + ratingValue;
  $('.' + parentClass + ' span').css('color', '#FBA31C');

  // finally, set the rating value to a hidden input field
  $('#rating-id').val(ratingValue);
});

// get the rating (star) value in integer
$('.review-value span').on('click', function () {
  let ratingValue = $(this).attr('data-ratingVal');

  // first, remove '#FBA31C' color and add '#777777' color to the star
  $('.review-value span').css('color', '#777777');

  // second, add '#FBA31C' color to the selected parent class
  let parentClass = 'review-' + ratingValue;
  $('.' + parentClass + ' span').css('color', '#FBA31C');

  // finally, set the rating value to a hidden input field
  $('#rating-id').val(ratingValue);
});
