(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminSettings(){
        var $o = {
            $form             : $('form#peerraiser-settings'),
            submitButton      : {
                $element      : $('form#peerraiser-settings .ladda-button'),
                laddaInstance : undefined,
                timeout       : undefined,
            },
            nonce             : $('#nonce_CMB2phppeerraiser-settings'),
            xhrRequests       : [],
        },

        init = function(){
            bindEvents();
            createLaddaInstance();
        },

        bindEvents = function() {
            $o.$form.on('submit', function(e){
                e.preventDefault();
                handle_submit( $o.submitButton.laddaInstance );
            });
        },

        createLaddaInstance = function(){
            $o.submitButton.laddaInstance = Ladda.create( $o.submitButton.$element[0] );
        },

        handle_submit = function( l ){
            // If it's already loading, abort
            if ( l.isLoading() )
                return;

            if ( $o.submitButton.$element.timeout ) {
                clearTimeout($o.submitButton.$element.timeout);
            }
            $o.submitButton.$element.removeClass('success');

            var postData = {
                'action'     : 'peerraiser_update_settings',
                '_wpnonce'   : $o.nonce.val(),
                'formData'   : $o.$form.serializeArray()
            },
            jqxhr;

            jqxhr = $.ajax({
                'url'       : peerraiser_object.ajax_url,
                'async'     : true,
                'method'    : 'POST',
                'data'      : postData,
                beforeSend: function(jqXHR) {
                    $o.xhrRequests.push(jqXHR);
                    l.start();
                    $o.submitButton.$element.find('.ladda-label').text('Saving Settings...');
                },
                complete: function(jqXHR) {
                    var index = $o.xhrRequests.indexOf(jqXHR);
                    if (index > -1) {
                        $o.xhrRequests.splice(index, 1);
                    }
                }
            });

            jqxhr.done(function(data) {
                data = JSON.parse(data);
                if (!data || !data.success) {
                    return;
                }
                console.log(data);
                l.stop();
                $o.submitButton.$element.addClass('success');
                $o.submitButton.$element.find('.ladda-label').text('Settings Saved').append('<i class="fa fa-check" aria-hidden="true"></i>');
                $o.submitButton.$element.timeout = setTimeout(function(){
                    $o.submitButton.$element.removeClass('success').find('.ladda-label').text('Save Settings');
                }, 1500);
                // $o.submitButton.$element.find('.ladda-label').text('Save Settings');
            });

            return jqxhr;
        };

        init();

    }

    // Kick it all off
    peerRaiserAdminSettings();

    // The window has loaded
    $( window ).load(function() {

    });

});})(jQuery);