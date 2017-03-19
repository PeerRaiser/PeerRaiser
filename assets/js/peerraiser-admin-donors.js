(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminDonors(){
        var $o = {
            dashboardTab    : $('#toplevel_page_peerraiser-dashboard'),
            dasboardTabLink : $('#toplevel_page_peerraiser-dashboard > a'),
            donationLink    : $('#toplevel_page_peerraiser-dashboard a[href$="pr_donor"]'),
            profileImage    : $('#donor-card img'),

            select2Fields : {
                donor_user_acount      : $("#_donor_user_account"),
            },

            select2Options : {
                donor_user_acount : {
                    data : function (params) {
                        return {
                            action: 'peerraiser_get_users',
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    templateResult: function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">User ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    },
                    templateSelection: function(data) {
                        return data.text;
                    },
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
    peerRaiserAdminDonors();

});})(jQuery);
