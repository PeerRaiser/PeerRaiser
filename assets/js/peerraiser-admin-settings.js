(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminSettings(){
        var $o = {
            $form             : $('form.cmb-form'),
            submitButton      : {
                $element      : $('form.cmb-form .ladda-button'),
                laddaInstance : undefined,
                timeout       : undefined,
            },
            nonce             : $('input[id^="nonce_CMB2php"]'),
            xhrRequests       : [],

            select2Fields : {
                thank_you_page        : $("#thank_you_page"),
                login_page            : $("#login_page"),
                signup_page           : $("#signup_page"),
                registration_page     : $("#registration_page"),
                participant_dashboard : $("#participant_dashboard"),
                donation_page         : $("#donation_page"),
            },

            select2Options : {
                thank_you_page : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['page'],
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    },
                    allowClear: false
                },
                login_page : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['page']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    },
                    allowClear: false
                },
                signup_page : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['page']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    },
                    allowClear: false
                },
                registration_page : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['page']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    },
                    allowClear: false
                },
                participant_dashboard : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['page']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    },
                    allowClear: false
                },
                donation_page : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['page']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    },
                    allowClear: false
                },
            }
        },

        init = function(){
            bindEvents();
            createLaddaInstance();
            renderSelect();
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

        renderSelect = function() {
            for ( var key in $o.select2Fields ) {
                if ( $o.select2Fields[key].length ){
                    $o.select2Fields[key].renderSelect($o.select2Options[key]);
                }
            }
        },

        handle_submit = function( l ){
            // If it's already loading, abort
            if ( l.isLoading() )
                return;

            if ( $o.submitButton.$element.timeout ) {
                clearTimeout($o.submitButton.$element.timeout);
            }
            $o.submitButton.$element.removeClass('success');

            // Hack to fix issue with Visual tab not saving.
            // Need to switch to Text tab and back first
            if ( $('.wp-editor-wrap').length ){
                $('.wp-editor-wrap').each(function(){
                    if ( $(this).hasClass('tmce-active') ){
                        $(this).find('.switch-html').click();
                        $(this).find('.switch-tmce').click();
                    }
                });
            }

            var postData = {
                'action'     : 'peerraiser_update_settings',
                '_wpnonce'   : $o.nonce.val(),
                'nonce_name'  : $o.nonce.attr('id'),
                'formData'   : $o.$form.serializeArray(),
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

                displayNotice();

                $o.submitButton.$element.timeout = setTimeout(function(){
                    $o.submitButton.$element.removeClass('success').find('.ladda-label').text('Save Settings');
                }, 1500);
                // $o.submitButton.$element.find('.ladda-label').text('Save Settings');
            });

            return jqxhr;
        },

        displayNotice = function() {
            var $message_container = $('#peerraiser-js-message'),
                revert_classes = $message_container.attr('class');

            $message_container.attr('class', 'notice notice-info').find('p').text('Settings updated successfully.');

            $message_container.slideDown('fast').delay(3000).slideUp( 'fast', function(){
                $(this).attr('class', revert_classes );
            });
        };

        init();

    }

    // Kick it all off
    peerRaiserAdminSettings();

    // The window has loaded
    $( window ).load(function() {

    });

});})(jQuery);