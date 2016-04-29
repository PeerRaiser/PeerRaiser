(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminTeam(){
        var $o = {
            dashboardTab           : $('#toplevel_page_peerraiser-dashboard'),
            dasboardTabLink        : $('#toplevel_page_peerraiser-dashboard > a'),
            fundraiserLink         : $('#toplevel_page_peerraiser-dashboard a[href$="pr_team"]'),

            select2Fields          : {
                team_leader        : $("#_team_leader"),
                campaign           : $("#_team_campaign"),
                fundraisers        : $("#_team_fundraisers"),
            },

            select2Options         : {
                team_leader        : {
                    data           : function (params) {
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
                campaign           : {
                    data           : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['pr_campaign']
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
                fundraisers : {
                    data : function (params) {
                        return {
                            action: 'peerraiser_get_posts',
                            q: params.term,
                            page: params.page,
                            post_type  : ['fundraiser']
                        };
                    },
                    templateResult: function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Fundraiser ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    },
                    multiple: true,
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
    peerRaiserAdminTeam();

});})(jQuery);