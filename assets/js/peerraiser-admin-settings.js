(function( $ ) {
    'use strict';

    // The page is ready
    $(function() {

        var l = Ladda.create( document.querySelector( '.ladda-button' ) );
        $('.ladda-button').on('click', function(e){
            e.preventDefault();
            l.start();
            $(this).find('.ladda-label').text('Saving Settings...');
        });


    });

    // The window has loaded
    $( window ).load(function() {

    });

})( jQuery );