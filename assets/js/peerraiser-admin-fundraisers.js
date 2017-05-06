(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminFundraising(){
        var $o = {
            dashboardTab           : $('#toplevel_page_peerraiser-dashboard'),
            dasboardTabLink        : $('#toplevel_page_peerraiser-dashboard > a'),
            fundraiserLink         : $('#toplevel_page_peerraiser-dashboard a[href$="fundraiser"]'),

            select2Fields          : {
                campaign           : $("#_peerraiser_fundraiser_campaign"),
                participant        : $("#_peerraiser_fundraiser_participant"),
                team               : $("#_peerraiser_fundraiser_team"),
            },

            select2Options         : {
                campaign           : {
                    data           : function ( params ) {
                        return {
                            action: 'peerraiser_get_campaigns',
                            s: params.term,
                            page: params.page
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="pr_id">Campaign ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    }
                },
                participant        : {
                    data           : function (params) {
                        return {
                            action: 'peerraiser_get_users',
                            q: params.term,
                            page: params.page,
                            peerraiser_group: 'participants',
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
                team               : {
                    data           : function ( params ) {
                        return {
                            action: 'peerraiser_get_teams',
                            s: params.term,
                            page: params.page
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="pr_id">Team ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    }
                },
            },
        },

        init = function(){
            bindEvents();
            renderSelect();
            activateSubmenu();
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

        // WordPress doesn't display submenus correctly if they're a post type. This is the workaround...
        activateSubmenu= function() {
            $o.dashboardTab.removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
            $o.dasboardTabLink.addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
            $o.fundraiserLink.addClass('current').parent().addClass('current');
        };

        init();

    }

    // Kick it off
    peerRaiserAdminFundraising();

});})(jQuery);
