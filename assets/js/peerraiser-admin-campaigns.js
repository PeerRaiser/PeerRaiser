(function( $ ) {
    'use strict';

    // The page is ready
    $(function() {
        $('#toplevel_page_peerraiser-dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
        $('#toplevel_page_peerraiser-dashboard > a').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
        $('#toplevel_page_peerraiser-dashboard a[href$="pr_campaign"]').addClass('current').parent().addClass('current');
    });

    var select2_options = {
        thank_you_page : {
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
            }
        },
        participants : {
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
            multiple: true,
        },
    };

    $("#_thank_you_page").renderSelect(select2_options.thank_you_page);
    $("#_campaign_participants").renderSelect(select2_options.participants);

    // The window has loaded
    $( window ).load(function() {

    });

})( jQuery );