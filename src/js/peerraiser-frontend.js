(function( $ ) {$(function() {
    'use strict';

    function peerRaiserFrontend(){
        var $o = {
            peerraiserForm : $('.peerraiser-form').parent(),
            dashboard : $('.peerraiser-dashboard'),
            alertBox  : $('.peerraiser-dashboard .alert'),
            nonces    : {
                avatarUpload : $('#peerraiser_upload_avatar_nonce').val()
            },
        },

        validationObject = {},

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

            // If a PeerRaiser form is present, use jQuery validate
            if ( $o.peerraiserForm.length ) {
                validationSetup();
            }

            if ( $('.peerraiser-registration-select-campaign select').length > 0 ) {
                $('.peerraiser-registration-select-campaign select').val('');
            }
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

            $('.peerraiser-donation-amounts .peerraiser-donation-input').on( 'focus', function(){
                $('.peerraiser-donation-amounts .peerraiser-donation-amount-buttons input[type=radio]').prop('checked', false);
            });

            $('.peerraiser-donation-amounts .peerraiser-donation-amount-buttons input[type=radio]').on( 'change', function(){
                if ( this.checked ) {
                    $('.peerraiser-donation-amounts .peerraiser-donation-input').val('');
                }
            });

            $('.peerraiser-donation-form #peerraiser_campaign').on('change', function(){
                if ( $(this).val() !== '' ) {
                    getFundraiserOptions( $(this).val() );
                }
            });

            $('.peerraiser-registration-select-campaign select').on('change', function() {
                var campaign_slug = $(this).val();

                if ( campaign_slug === '' ) {
                    return;
                }

                window.location.href = './' + campaign_slug;
            });

            $('.peerraiser-donation-form #peerraiser_anonymous').on('change', function(){
                $('.peerraiser-donation-form #peerraiser_public_name').attr( 'disabled', $(this).is(':checked') ).val('');
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

            // Disable the submit buttons while upload is in progress
            $o.dashboard.find('input[type="submit"]').each(function(){
               $(this).prop('disabled', true);
            });

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
                    $o.dashboard.find('input[type="submit"]').each(function(){
                       $(this).prop('disabled', false);
                    });

                    // Hide the loading image
                    $o.dashboard.find('.peerraiser-image-upload .loading').hide();
                }
            });

        },

        getFundraiserOptions = function( campaign_slug ){
            $.ajax({
                url : window.peerraiser_variables.ajaxUrl,
                type : 'post',
                dataType: 'json',
                data : {
                    nonce         : $('#get_fundraisers_nonce').val(),
                    action        : 'peerraiser_get_fundraisers',
                    campaign_slug : campaign_slug
                },
                success : function( response ) {
                    if ( ! response.success ) {
                        return;
                    }

                    // Unselect any previously selected fundraisers
                    $('.peerraiser-donation-form #fundraiser_select option:selected').prop("selected", false)

                    if ( response.fundraisers.length < 1) {
                        $('.peerraiser-donation-form .peerraiser-fundraiser-selection').addClass('hide');
                        $('.peerraiser-donation-form #fundraiser_select').find("option:gt(0)").remove();
                        return;
                    }

                    $.each(response.fundraisers, function(key, value) {
                        $('.peerraiser-donation-form #fundraiser_select')
                            .append($("<option></option>")
                                .attr("value",response.fundraisers[key].slug)
                                .text(response.fundraisers[key].name));
                    });

                    $('.peerraiser-donation-form .peerraiser-fundraiser-selection').removeClass('hide');

                }
            });
        },

        validationSetup = function() {
            validationObject = $o.peerraiserForm.validate({
                errorClass: "peerraiser-error",
                errorPlacement: function (error, element) {
                    var elem             = $(element);
                    var desc_box         = elem.parent().find('.cmb2-metabox-description'),
                        select2Container = elem.parent().find('.select2-container');
                    if ( desc_box.length ) {
                        desc_box.after( error );
                    } else {
                        if ( select2Container.length ) {
                            select2Container.after( error );
                        } else {
                            elem.parents('.cmb-td').append( error );
                        }
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    var elem = $(element);
                    elem.parents('.cmb-row').addClass(errorClass);
                },
                unhighlight: function (element, errorClass, validClass) {
                    var elem = $(element);
                    elem.parents('.cmb-row').removeClass(errorClass);
                }
            });
        };

        init();

    }

    // Kick it off
    peerRaiserFrontend();

});})(jQuery);