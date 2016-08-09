(function( $ ) {$(function() {
    'use strict';

    function peerRaiserFrontend(){
        var $o = {
            dashboard : $('.peerraiser-dashboard'),
            alertBox  : $('.peerraiser-dashboard .alert'),
            nonces    : {
                avatarUpload : $('#peerraiser_upload_avatar_nonce').val()
            }
        },

        settings = {
            allowed : {
                maxSize : 1000000,
                extension : [ 'gif', 'jpg', 'jpeg', 'png' ]
            }
        },

        status = {
            uploading : false
        },

        init = function(){
            bindEvents();
        },

        bindEvents = function() {
            $o.dashboard.find('.profile-image-upload .file-input').on( 'change', function(){
                // Hide error messages if there are any
                $o.alertBox.removeClass('alert-danger').hide();

                if ( this.files[0] ) {
                    handleFile( this.files[0] );
                } else {
                    $o.dashboard.find('.profile-image-upload .file-label span').text( '' );
                }
            });
        },

        handleFile = function( file ) {

            // Display the file name
            $o.dashboard.find('.profile-image-upload .file-label span').text( file.name );

            // Validate the file first
            var validationResults = validateFile( file );
            if ( validationResults.errors > 0 ) {
                displayError( validationResults.messages );
                return;
            }

            uploadFile( file );

        },

        validateFile = function( file ) {

            var validation = {
                errors   : 0,
                messages : []
            };
            var fileSize = ( file.size || file.fileSize ) ? file.fileSize || file.size : 0;
            var extension = file.name.split('.').pop();

            // Is the image more than 1MB?
            if ( fileSize > settings.allowed.maxSize ) {
                validation.messages[ validation.errors++ ] = "File size is too large.";
            }

            // Is the image an allowed file type?
            if ( settings.allowed.extension.indexOf( extension.toLowerCase() ) < 0 ) {
                validation.messages[ validation.errors++ ] = "'" + extension.toUpperCase() + "' is not a supported file type.";
            }

            return validation;
        },

        displayError = function( message ) {
            var errorMessage = '';
            if ( message.length > 1 ) {
                for (var i = 0; i < message.length; i++) {
                    errorMessage += message[i] + ' ';
                }
            } else {
                errorMessage = message;
            }
            $o.alertBox.addClass('alert-danger').text( errorMessage ).show();
        },

        uploadFile = function( file ) {
            if ( !status.uploading ) {
                status.uploading = true;
            } else {
                return;
            }

            // Disable the submit button while upload is in progress
            $o.dashboard.find('input[type="submit"]').prop('disabled', true);

            // Display the loading image
            $o.dashboard.find('.peerraiser-image-upload .loading').show();

            var formData = new FormData();
            formData.append('action', 'peerraiser_update_avatar');
            formData.append('_wpnonce', $o.nonces.avatarUpload);
            formData.append('files', file);

            $.ajax({
                type: 'POST',
                url: window.peerraiser_variables.ajaxUrl,
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    status.uploading = false;

                    // If successful, update the avatar image
                    if ( response.success === true ) {
                        $o.dashboard.find('.peerraiser-image-upload .avatar').attr('src', response.image_url);
                    }

                    // Enable the submit button
                    $o.dashboard.find('input[type="submit"]').prop('disabled', false);

                    // Hide the loading image
                    $o.dashboard.find('.peerraiser-image-upload .loading').hide();
                }
            });

        };

        init();

    }

    // Kick it off
    peerRaiserFrontend();

});})(jQuery);