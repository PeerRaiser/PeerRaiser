;(function ( $, window, document, undefined ) {

    "use strict";

    // Plugin constructor creates a new instance of the plugin for each DOM node
    // that the plugin is called on
    function RenderSelect( element, options ) {
        self = this;
        // The DOM node(s) that called the plugin
        this.element = element;
        // The default options
        this._defaults = $.fn.renderSelect.defaults;
        // Merge the default options with whatever options were passed (if any)
        this.options = $.extend( {}, this._defaults, options );
        // The "init" function is the starting point for all the plugin logic
        this.init();
    }
    $.extend(RenderSelect.prototype, {
        init: function(){
            // var self = this;
            this.render_select();
        },
        render_select: function() {
            var select2_args = {
                width: this.options.width,
                allowClear: this.options.allowClear,
                placeholder: this.options.placeholder,
                escapeMarkup: this.options.escapeMarkup,
                ajax: {
                    type: 'POST',
                    url: window.peerraiser_object.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    cache: this.options.cache,
                    data: this.options.data,
                    processResults: this.options.processResults
                },
                templateResult: this.options.templateResult,
                templateSelection: this.options.templateSelection,
                multiple: this.options.multiple,
            };
            $(this.element).select2( select2_args );
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
    });

    // Extend jQuery
    $.fn.renderSelect = function ( options ) {
        this.each(function() {
            $.data( this, "plugin_renderSelect", new RenderSelect( this, options ) );
        });
        // Return "this" allows additional jQuery chaining
        return this;
    };

    // Default Options
    $.fn.renderSelect.defaults = {
        width: '100%',
        allowClear: true,
        placeholder: {
            id: "",
            placeholder: ""
        },
        escapeMarkup: function( m ){ return m; },
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
        templateResult: $.noop(),
        templateSelection: $.noop(),
        data: {
            action: 'peerraiser_get_posts',
        },
        multiple: false,
        cache: false,
    };

})( jQuery, window, document );