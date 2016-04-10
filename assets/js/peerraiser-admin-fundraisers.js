(function( $ ) {
    'use strict';

    // The page is ready
    $(function() {
        // Add/Remove classes so the PeerRaiser > Fundraisers submenu is displayed correctly
        $('#toplevel_page_peerraiser-dashboard-tab').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
        $('#toplevel_page_peerraiser-dashboard-tab > a').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
        $('#toplevel_page_peerraiser-dashboard-tab a[href$="fundraiser"]').addClass('current').parent().addClass('current');

        $("#_fundraiser_campaign, #_fundraiser_team").select2({width: '100%'});
        $("#_fundraiser_participant").select2({
            width: '100%',
            allowClear: true,
            placeholder: {
                id: "",
                placeholder: ""
            },
            ajax: {
                type: 'POST',
                url: window.peerraiser_object.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
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
                cache: true,
            },
            templateResult: function(data) {
                // return "<strong>"+data.text+"</strong>";
                // return $('<span>').append($('span').html(data.text)).append($('<span>').html(data.id));
                var html = '<span class="display_name">' + data.text + '</span>';
                if ( data.id ) {
                    html += '<span class="user_id">User ID: ' + data.id + '</span>';
                }
                return $('<span>').html(html);
            },
            templateSelection: function(data) {
                return data.text;
            },
        });

        var $PeerRaiser = $.PeerRaiser(),
            select2_options = {};

        select2_options.campaign = {
            data: {
                post_type: ['pr_campaign']
            },
            templateResult: function(data) {
                var html = '<span class="pr_name">' + data.text + '</span>';
                if ( data.id ) {
                    html += '<span class="pr_id">Campaign ID: ' + data.id + '</span>';
                }
                return $('<span>').html(html);
            }
        };
        select2_options.team = {
            data: {
                post_type: ['pr_team']
            },
            templateResult: function(data) {
                var html = '<span class="pr_name">' + data.text + '</span>';
                if ( data.id ) {
                    html += '<span class="pr_id">Team ID: ' + data.id + '</span>';
                }
                return $('<span>').html(html);
            }
        };
        $PeerRaiser.render_select( $("#_fundraiser_campaign"), select2_options.campaign );
        $PeerRaiser.render_select( $("#_fundraiser_team"), select2_options.team );

    });

    // The window has loaded
    $( window ).load(function() {

    });

})( jQuery );