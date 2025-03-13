"use strict";
let geocoder;
let isSubmitting = false;

function initMap(service_id = null) {
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
      document.getElementById('address').value = formattedAddress;
      handleSearch();
    });
  }
}


// Function to update URL and submit form
function updateUrl(data) {

  let newUrl = new URL(window.location);
  if (data === "address") {
    newUrl.searchParams.set('address', $('#address').val());
    newUrl.searchParams.set('sort', 'nearest');
  } else {
    newUrl.searchParams.delete('address');
    newUrl.searchParams.delete('sort');
  }
  window.history.replaceState({}, '', newUrl);

  // Submit the form and prevent multiple submissions
  if (!isSubmitting) {
    isSubmitting = true;
    $('#searchForm').submit();
  }
}

// Function to handle the search process
function handleSearch() {
  const locationValue = $('#address').val().trim();

  // Check if the form is already submitting
  if (isSubmitting) {
    return;
  }

  if (!locationValue && !isSubmitting) {
    $('#address').val('');
    updateUrl(); // Reset URL if location is blank
    isSubmitting = true;
  } else if (locationValue && !isSubmitting) {
    document.getElementById('address').value = locationValue;
    updateUrl("address");
  }
}


// Geocode latitude and longitude to get the address
function geocodeLatLng(latLng) {
  geocoder.geocode({ location: latLng }, function (results, status) {
    if (status === 'OK') {
      if (results[0]) {
        $('#location').val(results[0].formatted_address);
        $('#address').val(results[0].formatted_address);
        updateUrl();
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
$(document).ready(function () {
  initMap();
});
$(document).ready(function () {
  if (typeof google !== "undefined" && google.maps) {
    initMap();
  }
});
