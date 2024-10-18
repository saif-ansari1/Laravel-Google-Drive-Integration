<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="container">
                         <!-- Display flash message -->
                         @if(session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        <h1>Welcome to File Upload</h1>

                        <!-- Display flash message -->
                        {{-- @if(session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif --}}

                        <br>
                        <p>Use the button below to attach files from Google Drive.</p>
                        
                         <!-- Check if the user is connected to Google Drive -->
                         @if(auth()->user()->google_drive_token)
                         <div class="d-flex justify-content-between mb-3">
                            <!-- Button to Attach Files from Google Drive on the left -->
                            <button id="googleDrivePickerBtn" class="btn btn-success">
                                Add from Google Drive 
                            </button>
                            
                            <!-- Button to Disconnect Google Drive on the right -->
                            <form action="{{ route('google.drive.disconnect') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">Remove Connection</button>
                            </form>
                        </div>
                            
                            
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
                                            <th>Download Image</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentImagesBody">
                                        <!-- Recent images will be displayed here -->
                                    </tbody>
                                </table>
                            </div>

                           
                        @else
                            <!-- Button to Connect to Google Drive -->
                            <a href="{{ route('google.drive.connect') }}" class="btn btn-primary">
                                Connect to Google Drive
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Modal for selecting format and size -->
<div id="downloadOptionsModal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download asset</h5>
            </div>
            <div class="modal-body">
                <form id="downloadOptionsForm">
                    <input type="hidden" id="imageId" name="imageId" value="">

                    <div class="form-group">
                        <label>Select Format:</label><br>
                        <div class="form-check format-check">
                            <input class="form-check-input" type="radio" name="format" id="formatJpg" value="jpg" checked>
                            <label class="form-check-label" for="formatJpg">JPG</label>
                        </div>
                        <div class="form-check format-check">
                            <input class="form-check-input" type="radio" name="format" id="formatPng" value="png">
                            <label class="form-check-label" for="formatPng">PNG</label>
                        </div>
                        <div class="form-check format-check">
                            <input class="form-check-input" type="radio" name="format" id="formatWebp" value="webp">
                            <label class="form-check-label" for="formatWebp">WEBP</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Select Size:</label><br>
                        <div class="form-check size-check">
                            <input class="form-check-input" type="radio" name="size" id="sizeTiny" value="tiny" checked>
                            <label class="form-check-label" for="sizeTiny">Tiny - (100x69px)</label>
                        </div>
                        <div class="form-check size-check">
                            <input class="form-check-input" type="radio" name="size" id="sizeSmall" value="small">
                            <label class="form-check-label" for="sizeSmall">Small - (250x171px)</label>
                        </div>
                        <div class="form-check size-check">
                            <input class="form-check-input" type="radio" name="size" id="sizeMedium" value="medium">
                            <label class="form-check-label" for="sizeMedium">Medium - (500x341px)</label>
                        </div>
                        <div class="form-check size-check">
                            <input class="form-check-input" type="radio" name="size" id="sizeLarge" value="large">
                            <label class="form-check-label" for="sizeLarge">Large - (1000x681px)</label>
                        </div>
                        <div class="form-check size-check">
                            <input class="form-check-input" type="radio" name="size" id="sizeOriginal" value="original">
                            <label class="form-check-label" for="sizeOriginal">Original size - </label>
                        </div>
                        <div class="form-check size-check">
                            <input class="form-check-input" type="radio" name="size" id="sizeCustom" value="custom">
                            <label class="form-check-label" for="sizeCustom">Custom size</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="downloadImageBtn">Download</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <script src="https://apis.google.com/js/api.js"></script>
<script>
    let accessToken = "{{ auth()->user()->google_drive_token ?? '' }}"; // Use session to access the token
    let isPickerLoaded = false; // Track if the Picker API is loaded
    let selectedFiles = []; // Store selected files
    console.log(accessToken);

    function checkAccessTokenAndProceed() {
        let tokenExpiration = "{{ auth()->user()->token_expires_at ?? '' }}"; // Use the stored expiration time

        // Convert token expiration time to a Date object
        let tokenExpirationDate = new Date(tokenExpiration);

        if (new Date() >= tokenExpirationDate) {
            console.log("Access token expired. Refreshing...");
            refreshAccessToken();
        } else {
            console.log("Access token is valid. Proceeding...");
            createPicker(accessToken);  
        }
    }

    function refreshAccessToken() {
        let refreshUrl = "{{ route('google.drive.refresh.token') }}";  

        
        fetch(refreshUrl)
            .then(response => response.json())
            .then(data => {
                if (data.access_token) {
                    accessToken = data.access_token;  
                    console.log("Access token refreshed successfully:", accessToken);
                    createPicker(accessToken);  
                } else {
                    console.error("Failed to refresh token:", data.error);
                }
            })
            .catch(error => console.error("Error refreshing token:", error));
    }
 
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

                    clearFileList();
                    $('#selectedFiles').hide(); 
                },
                error: function(xhr, status, error) {
                    console.error('Error uploading images:', error);
                }
            });
        }
    });

    // Function to clear the file list
    function clearFileList() {
        // Select the file list and empty it
        const fileList = document.getElementById('fileList');
        fileList.innerHTML = ''; // Clear the file list
        selectedFiles = []; // Reset the selected files array
    }

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
                            <td><button class="btn btn-primary downloadImageBtn" data-image-id="${ image.id }" data-image-width="${ image.width }" data-image-height="${ image.height }">
                                     Download Image
                            </button></td>
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
            checkAccessTokenAndProceed();
            // createPicker(accessToken); // Show the picker if it's already loaded
        }
    });

    // Open the modal on button click
        // $(document).on('click', '.downloadImageBtn', function() {
        //     // alert('dd');
        //     const imageId = $(this).data('image-id');
        //     $('#imageId').val(imageId);  // Pass the image ID to the form
        //     $('#downloadOptionsModal').modal('show');
        // });

        $(document).on('click', '.downloadImageBtn', function() {
            const imageId = $(this).data('image-id'); 
            const imageWidth = $(this).data('image-width'); 
            const imageHeight = $(this).data('image-height'); 

            $('#imageId').val(imageId);

            
            $('input[name="size"]').prop('checked', false);
            $('#sizeTiny').prop('checked', true); 
            $('.size-check').hide();  

            
            if (imageWidth >= 100 && imageHeight >= 69) {
                $('#sizeTiny').closest('.size-check').show();
            }
            if (imageWidth >= 250 && imageHeight >= 171) {
                $('#sizeSmall').closest('.size-check').show();
            }
            if (imageWidth >= 500 && imageHeight >= 341) {
                $('#sizeMedium').closest('.size-check').show();
            }
            if (imageWidth >= 1000 && imageHeight >= 681) {
                $('#sizeLarge').closest('.size-check').show();
            }
            
            $('#sizeOriginal').closest('.size-check').show();
            $('#sizeOriginal').siblings('label').text(`Original size - (${imageWidth}x${imageHeight}px)`);
            $('#sizeCustom').closest('.size-check').show();

            
            $('#downloadOptionsModal').modal('show');
        });

        // $('#downloadImageBtn').on('click', function() {
        //     const formData = $('#downloadOptionsForm').serialize(); // Get the format and size

        //     // Send AJAX request to download image
        //     $.ajax({
        //         url: "{{ route('download.image') }}",  // Backend route for image download
        //         method: 'POST',
        //         data: formData,
        //         success: function(response) {
        //             // Trigger image download
        //             const link = document.createElement('a');
        //             link.href = response.file_url;
        //             link.download = response.file_name;
        //             document.body.appendChild(link);
        //             link.click();
        //             document.body.removeChild(link);
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Error downloading image:', error);
        //         }
        //     });

        //     $('#downloadOptionsModal').modal('hide');
        // });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Download button functionality
        $('#downloadImageBtn').on('click', function() {
            const formData = $('#downloadOptionsForm').serialize(); // Get the format and size

            // Send AJAX request to download image
            $.ajax({
                url: "{{ route('download.image') }}", // Adjust this route as needed
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Trigger image download
                    const link = document.createElement('a');
                    link.href = response.file_url;
                    link.download = response.file_name;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    console.error('Error downloading image:', error);
                }
            });

            $('#downloadOptionsModal').modal('hide');
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
</x-app-layout>
