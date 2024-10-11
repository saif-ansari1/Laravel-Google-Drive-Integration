@extends('layouts.app')
<style>
    .loader {
    display: none; /* Initially hidden */
    text-align: center;
    margin-bottom: 20px;
    font-weight: bold;
}
    </style>
@section('content')
<div class="container">
    <h1>Welcome to File Upload</h1>
    <p>Use the button below to attach files from Google Drive.</p>
    
     <!-- Check if the user is connected to Google Drive -->
@if(!session('google_drive_token'))
     <!-- Button to Connect to Google Drive -->
     <a href="{{ route('google.drive.connect') }}" class="btn btn-primary">
         Connect to Google Drive
     </a>
 @else

 
     <!-- Button to Attach Files from Google Drive -->
     <button id="googleDrivePickerBtn" class="btn btn-success">
        Add from Google Drive 
    </button>
     
     <!-- Div to show Google Drive folder structure -->
     <br>
     <br>
     <br>
     <br>
     <div id="selectedFiles" style="display:none;">
         <h2>Google Drive Files</h2>
         <ul id="fileList"></ul>

         <button id="uploadFilesBtn" class="btn btn-primary" style="display:none;">Upload Selected Files</button>
     </div>

     <div id="loader" style="display: none;">
        <p>Uploading images, please wait...</p>
    </div>

     <div id="recentImages" style="margin-top: 20px; display: none;">
        <h3>Recent Images:</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>File Name</th>
                </tr>
            </thead>
            <tbody id="recentImagesBody">
                <!-- Recent images will be displayed here -->
            </tbody>
        </table>
    </div>

    {{-- <div id="loader" style="display: none; text-align: center; margin-bottom: 20px;">
        <p>Uploading images, please wait...</p>
    </div> --}}
 @endif
</div>
 {{-- <script>
    $(document).ready(function() {
        // Handle Attach Files from Google Drive button click
        $('#googleDrivePickerBtn').click(function() {
            // Make an AJAX call to get the folder structure from the controller
            $.ajax({
                url: "{{ route('google.drive.folders') }}",
                method: 'GET',
                success: function(data) {
                    // Show the folder structure and populate it
                    $('#driveFolders').show();
                    $('#folderList').empty();

                    data.files.forEach(function(file) {
                        $('#folderList').append('<li>' + file.name + '</li>');
                    });
                }
            });
        });
    });
</script>  --}}
<script src="https://apis.google.com/js/api.js"></script>
<script>
    let accessToken = "{{ $accessToken ?? '' }}"; // Use session to access the token
    let isPickerLoaded = false; // Track if the Picker API is loaded
    let selectedFiles = []; // Store selected files
    console.log(accessToken);
 
    // Function to load Google API and Picker
    function loadGoogleApi() {
        gapi.load('client:auth2', {callback: onAuthApiLoad});
        gapi.load('picker', {callback: pickerApiLoaded});
    }
 
    function onAuthApiLoad() {
        if (accessToken) {
            console.log('Auth API loaded');
            createPicker(accessToken);
        } else {
            console.error('No access token available');
        }
    }
 
    function pickerApiLoaded() {
        console.log('Picker API loaded');
        isPickerLoaded = true;
    }
 
    function createPicker(accessToken) {
        if (isPickerLoaded) {
            const picker = new google.picker.PickerBuilder()
                .addView(google.picker.ViewId.DOCS)
                .enableFeature(google.picker.Feature.MULTISELECT_ENABLED) // Enable multi-select
                .setOAuthToken(accessToken)
                .setDeveloperKey('{{ env("GOOGLE_DEVELOPER_KEY") }}')
                .setCallback(pickerCallback)
                .build();
            picker.setVisible(true);
        } else {
            console.log('Picker API not loaded yet');
        }
    }
 
    // function pickerCallback(data) {
    //     if (data[google.picker.Response.ACTION] === google.picker.Action.PICKED) {
    //         const doc = data[google.picker.Response.DOCUMENTS][0];
    //         const id = doc[google.picker.Document.ID];
    //         const name = doc[google.picker.Document.NAME];
    //         console.log('Selected file: ' + name + ' (ID: ' + id + ')');
    //     }
    // }
    function pickerCallback(data) {
        if (data[google.picker.Response.ACTION] === google.picker.Action.PICKED) {
            // Loop through selected documents
            data[google.picker.Response.DOCUMENTS].forEach(doc => {
                const id = doc[google.picker.Document.ID];
                const name = doc[google.picker.Document.NAME];
                const url = `https://drive.google.com/uc?id=${id}&export=download`;

                // Add the file details to selectedFiles array
                selectedFiles.push({ id, name, url });

                // Display file names in a list
                $('#selectedFiles').show();
                $('#fileList').append('<li>' + name + '</li>');
            });

            // Show upload button
            if (selectedFiles.length > 0) {
                $('#uploadFilesBtn').show();
            }
        }
    }
   
    $('#uploadFilesBtn').on('click', function() {
        if (selectedFiles.length > 0) {
            // Send selected files to the server
            $('#loader').show();
            console.log("loader show");
            $.ajax({
                url: "{{ route('store.selected.images') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    images: selectedFiles
                },
                success: function(response) {
                    console.log(response.message);
                    // Fetch recent images after upload
                    fetchRecentImages();
                },
                error: function(xhr, status, error) {
                    console.error('Error uploading images:', error);
                }
            });
        }
    });

    function fetchRecentImages() {
    $.ajax({
        url: "{{ route('recent.images') }}",
        method: 'GET',
        success: function(images) {
            $('#recentImagesBody').empty(); // Clear existing rows
            
            if (images.length > 0) {
                // If images are present, show the recent images div
                $('#recentImages').show();
                images.forEach(function(image) {
                    $('#recentImagesBody').append(`
                        <tr>
                            <td><img src="${image.file_url}" alt="${image.file_name}" width="100"></td>
                            <td>${image.file_name}</td>
                        </tr>
                    `);
                });
            } else {
                // If no images, hide the recent images div
                $('#recentImages').hide();
            }
            
            // Hide the loader after fetching recent images
            $('#loader').hide();
        },
        error: function(xhr, status, error) {
            console.error('Error fetching recent images:', error);
            $('#loader').hide(); // Hide loader in case of error
        }
    });
}
        
    // Trigger Picker on button click
    $('#googleDrivePickerBtn').on('click', function() {
        if (!isPickerLoaded) {
            loadGoogleApi(); // Load the Google API on the first click
        } else {
            createPicker(accessToken); // Show the picker if it's already loaded
        }
    });

     // Handle upload button click
    //  $('#uploadFilesBtn').on('click', function() {
    //     uploadSelectedFiles();
    // });
 
    // Automatically load Google API when page is loaded
    window.onload = function() {
        loadGoogleApi(); // Ensures the API is loaded on page load
    };

    // Load recent images when the page loads
    $(document).ready(function() {
        fetchRecentImages();
    });
 </script>
@endsection
