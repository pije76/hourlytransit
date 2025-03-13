"use strict";
$(document).ready(function () {
  var data = {
    room_id: room_id
  }
  $.get(visitor_store_url, data, function () { });
})
