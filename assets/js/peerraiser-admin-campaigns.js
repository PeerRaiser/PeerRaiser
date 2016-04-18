(function( $ ) {
    'use strict';

    // The page is ready
    $(function() {
        $('#toplevel_page_peerraiser-dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
        $('#toplevel_page_peerraiser-dashboard > a').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
        $('#toplevel_page_peerraiser-dashboard a[href$="pr_campaign"]').addClass('current').parent().addClass('current');
    });

    var select2_options = {
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
    };

    $("#_thank_you_page").renderSelect(select2_options);

    // The window has loaded
    $( window ).load(function() {

    });

})( jQuery );