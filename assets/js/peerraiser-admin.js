;(function ( $, window, document, undefined ) {

    "use strict";

    // Plugin constructor
    function Plugin ( element, options ) {
        // The plugin name
        this._name = 'PeerRaiser';

        // The default options
        this._defaults = $.PeerRaiser.defaults;

        // Merge the default options with whatever options were passed (if any)
        this.options = $.extend( {}, this._defaults, options );

        // Make it easier to use 'this' to call methods
        self = this;

        // The starting point for all the plugin logic
        this.init();
    }

    $.extend(Plugin.prototype, {

        // Initialization logic
        init: function () {
        },
        render_select: function( $element, options ) {

            var settings = $.extend( {}, this.options.select2, options);

            var select2_args = {
                width: settings.width,
                allowClear: settings.allowClear,
                placeholder: settings.placeholder,
                escapeMarkup: settings.escapeMarkup,
                ajax: {
                    type: 'POST',
                    url: window.peerraiser_object.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    cache: settings.cache,
                    data: function ( params ) {
                        var data = $.extend(
                            {
                                action: 'peerraiser_get_posts',
                                s: params.term,
                                page: params.page,
                            },
                            settings.data
                        );
                        return data;
                    },
                    processResults: function( data, params ){

                        setTimeout(function(){

                            var $prev_options = null,
                                $prev_group = null;

                            $('.select2-results__option[role="group"]').each(function(){
                                var $options = $(this).children('ul'),
                                    $group = $(this).children('strong');

                                if ( $prev_group !== null && $group.text() == $prev_group.text() ) {
                                    $prev_options.append( $options.children() );
                                    $(this).remove();
                                    return;
                                }

                                $prev_options = $options;
                                $prev_group = $group;
                            });

                        }, 1);

                        return {
                            results: self.decode_data(data),
                            pagination: {
                                more: (self.count_data(data) >= 20)
                            }
                        };

                    },
                },
                templateResult: settings.templateResult,
                templateSelection: settings.templateSelection,
            };

            $element.select2( select2_args );
        },

        decode_data: function( data ){
            // Return if no data
            if( !data ) return [];

            $.each( data, function( k, v ){
                data[ k ].text = self.decode( v.text );
                if( typeof v.children !== 'undefined' ) {
                    data[ k ].children = self.decode_data(v.children);
                }
            });

            return data;
        },

        decode: function( string ){
            return $('<div/>').html( string ).text();
        },

        count_data: function( data ) {
            var i = 0;

            // Return if no data
            if ( !data ) return i;

            $.each(data, function(k, v){
                i++;
                if( typeof v.children !== 'undefined' ) {
                    i += v.children.length;
                }
            });

            return i;
        },

        // Combines real data with a template
        formatTemplate: function(data) {
            return this.options.template.replace(/{(\d+)}/g, function(match, number) {
                return typeof data[number] != 'undefined' ? data[number] : match;
            });
        },
        onComplete: function() {
            // Cache onComplete option
            var onComplete = this.options.onComplete;

            if ( typeof onComplete === 'function' ) {
                onComplete.call(this.element);
            }
        }

    });

    $.PeerRaiser = function ( options ) {
        return new Plugin( this, options );
    };

    // Default Plugin Options
    $.PeerRaiser.defaults = {
        select2 : {
            width: '100%',
            allowClear: true,
            placeholder: {
                id: "",
                placeholder: ""
            },
            escapeMarkup: function( m ){ return m; },
            cache: false,
            processResults: $.noop(),
            templateResult: $.noop(),
            templateSelection: $.noop(),
            data: {
                action: 'peerraiser_get_posts',
            },
            multiple: false,
        }
    };

})( jQuery, window, document );