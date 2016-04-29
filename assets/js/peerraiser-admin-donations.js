(function( $ ) {
    'use strict';

    // The page is ready
    $(function() {
        $('#toplevel_page_peerraiser-dashboard').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
        $('#toplevel_page_peerraiser-dashboard > a').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
        $('#toplevel_page_peerraiser-dashboard a[href$="pr_donation"]').addClass('current').parent().addClass('current');
    });


    // The window has loaded
    $( window ).load(function() {

    });

})( jQuery );