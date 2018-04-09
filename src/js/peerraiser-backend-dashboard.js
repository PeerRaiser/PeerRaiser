(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminDashboard(){
        var $o = {
            welcome_message : {
                close_link : $('.peerraiser-wrap.dashboard-wrap .welcome-message .close'),
            }
        },

        init = function(){
            bindEvents();
        },

        bindEvents = function() {
            $o.welcome_message.close_link.on('click', function(e){
                e.preventDefault();
                dismissMessage( this );
            });
        },

        dismissMessage = function( element ){
            // Hide the message container
            $(element).parent().remove();

            // Save the setting so it stays dismissed
            $.ajax({
                type : "post",
                dataType : "json",
                url : window.pr_dashboard_variables.ajax_url,
                data: {
                    action : "peerraiser_dismiss_message",
                    message_type : $(element).data('message-type'),
                    nonce : $(element).data('nonce'),
                },
                success: function( response ){
                    if ( response.success ){
                        console.log(response);
                    } else {
                        console.log(response);
                    }
                }
            });
        };

        init();

    }

    // Kick it off
    peerRaiserAdminDashboard();

});})(jQuery);