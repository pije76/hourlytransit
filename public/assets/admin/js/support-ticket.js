"use strict";
// Function to handle file upload
function handleFileUpload() {
  var fileInput = document.getElementById('zip_file');
  var progressBar = document.querySelector('.progress-bar');
  var progressContainer = document.querySelector('.progress');
  var errorMessage = document.getElementById('errfile');

  if (fileInput.files.length > 0) {
    var file = fileInput.files[0];
    var formData = new FormData();
    formData.append('file', file);

    // Ajax request to upload the file
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fileInput.getAttribute('data-href'));

    // Update progress bar on progress event
    xhr.upload.addEventListener('progress', function (event) {
      if (event.lengthComputable) {
        var percentComplete = (event.loaded / event.total) * 100;
        progressBar.style.width = percentComplete + '%';
        progressBar.setAttribute('aria-valuenow', percentComplete);
      }
    });

    // Handle successful upload
    xhr.onload = function () {
      if (xhr.status === 200) {
        // File uploaded successfully
        // You can perform any further actions here
      } else {
        // Handle upload error
        errorMessage.textContent = 'Upload failed. Please try again.';
      }
    };

    // Handle upload errors
    xhr.onerror = function () {
      errorMessage.textContent = 'Upload failed. Please try again.';
    };

    // Send the FormData
    xhr.send(formData);

    // Show progress bar
    progressContainer.classList.remove('d-none');
  }
}

// Attach event listener to file input
document.getElementById('zip_file').addEventListener('change', handleFileUpload);
