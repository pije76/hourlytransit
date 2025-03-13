"use strict";
let geocoder;
let isSubmitting = false;

window.initMap = function () {

  geocoder = new google.maps.Geocoder();
  let input = document.getElementById('location');

  // Listen for 'Enter' key on the input field
  if (input) {
    let searchBox = new google.maps.places.SearchBox(input);
    input.addEventListener('keyup', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        handleSearch();
      }
    });
    // Listen for place changes in the search box
    searchBox.addListener('places_changed', function () {
      const places = searchBox.getPlaces();
      if (places.length === 0) {
        return;
      }

      // Get the last selected place
      const place = places[places.length - 1];

      if (!place.geometry) {
        alert("Returned place contains no geometry");
        return;
      }
      const formattedAddress = decodeURIComponent(place.formatted_address);
      document.getElementById('location_val').value = formattedAddress;
      handleSearch();
    });
  }
}


// Function to update URL and submit form
function updateUrl(data) {

  let newUrl = new URL(window.location);
  if (data === "location_val") {
    newUrl.searchParams.set('location', $('#location_val').val());
  } else {
    newUrl.searchParams.delete('location');
  }
  window.history.replaceState({}, '', newUrl);

  // Submit the form and prevent multiple submissions
  if (!isSubmitting) {
    isSubmitting = true;
    $('#searchForm').submit();
    $(".request-loader").addClass("show");
  }
}

// Function to handle the search process
function handleSearch() {
  const locationValue = $('#location').val().trim();

  // Check if the form is already submitting
  if (isSubmitting) {
    return;
  }

  if (!locationValue && !isSubmitting) {
    $('#location_val').val('');
    updateUrl(); // Reset URL if location is blank
    isSubmitting = true;
  } else if (locationValue && !isSubmitting) {
    document.getElementById('location_val').value = locationValue;
    updateUrl("location_val");
  }
}

// Geocode latitude and longitude to get the address
function geocodeLatLng(latLng) {
  geocoder.geocode({ location: latLng }, function (results, status) {
    if (status === 'OK') {
      if (results[0]) {

        $('#location').val(results[0].formatted_address);
        $('#location_val').val(results[0].formatted_address);
        updateUrl("location_val");

      } else {
        console.log('No results found');
      }
    } else {
      console.log('Geocoder failed due to: ' + status);
    }
  });
}

// Get the user's current location
function getCurrentLocation() {

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
      const latLng = { lat: position.coords.latitude, lng: position.coords.longitude };
      // alert(latLng);
      console.log(latLng);
      geocodeLatLng(latLng);
    }, function (error) {
      alert("Unable to retrieve your location. Error: " + error.message);
    });
  } else {
    alert("Geolocation is not supported by this browser.");
  }
}

// Reset the isSubmitting flag when the form submission is completed
$('#searchForm').on('submit', function () {
  setTimeout(() => {
    isSubmitting = false
  }, 300);
});
if (typeof google !== "undefined" && google.maps) {
  if (typeof initMap === "function") {
    initMap();
  } else {
    // Retry after a slight delay
    setTimeout(() => initMap && initMap(), 100);
  }
}

