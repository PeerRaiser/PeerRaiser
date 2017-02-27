(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminDonations(){
        var $o = {
            dashboardTab    : $('#toplevel_page_peerraiser-dashboard'),
            dasboardTabLink : $('#toplevel_page_peerraiser-dashboard > a'),
            donationLink    : $('#toplevel_page_peerraiser-dashboard a[href$="pr_donation"]'),

            select2Fields : {
                donor      : $("#_donor"),
                campaign   : $("#_campaign"),
                fundraiser : $("#_fundraiser"),
            },

            select2Options : {
                donor : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_donors',
                            s: params.term,
                            page: params.page
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Donor ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    }
                },
                campaign : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_campaigns',
                            s: params.term,
                            page: params.page
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Campaign ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    }
                },
                fundraiser : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['fundraiser']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Fundraiser ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        var text = data.text;
                        if ( typeof text === 'string' ) {
                            text = text.replace(/^(- )*/g, '');
                        }
                        return text;
                    }
                },
            },

        },

        init = function(){
            bindEvents();
            renderSelect();
            activateSubmenu();
            renderTooltips();
        },

        bindEvents = function() {
            $o.select2Fields.campaign.on('change', function(){
                var id = $(this).val();
                if ( id !== null ) {
                    $o.select2Fields.fundraiser.prop('disabled', false);
                    $o.select2Fields.fundraiser.val('');

                    $o.select2Options.fundraiser.data = function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['fundraiser'],
                            taxonomy : ['peerraiser_campaign:'+id],
                            term_id : id
                        };
                    };
                    $o.select2Fields.fundraiser.renderSelect($o.select2Options.fundraiser);
                } else {
                    $o.select2Fields.fundraiser.prop('disabled', 'disabled');
                }
            });
        },

        renderSelect = function() {
            for ( var key in $o.select2Fields ) {
                if ( $o.select2Fields[key].length ){
                    $o.select2Fields[key].renderSelect($o.select2Options[key]);
                }
            }
        },

        renderTooltips = function() {
            $('.cmb-td input, .cmb-td select, .cmb-td textarea').each(function(){
                var tooltip = $(this).data('tooltip');
                if ( tooltip !== undefined ) {
                    $(this).parents('.cmb-row').find('.cmb-th').append('<span class="pr_tooltip"><i class="pr_icon fa fa-question-circle"></i><span class="pr_tip">'+tooltip+'</span></span>');
                }
            });
        },

        // WordPress doesn't display submenus correctly if they're a post type. This is the workaround...
        activateSubmenu= function() {
            $o.dashboardTab.removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
            $o.dasboardTabLink.addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
            $o.donationLink.addClass('current').parent().addClass('current');
        };

        init();

    }

    // Kick it off
    peerRaiserAdminDonations();

});})(jQuery);