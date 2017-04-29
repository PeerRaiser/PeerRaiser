;(function ( $, window, document, undefined ) {

    "use strict";

	function peerRaiserAdmin(){
		var $o = {
				peerraiserForm : $('form.peerraiser-form'),
			},
			validationObject = {},

			init = function(){
				bindEvents();
				renderTooltips();
				denoteRequiredFields();

				// If a PeerRaiser form is present, use jQuery validate
				if ( $o.peerraiserForm.length ) {
					validationSetup();
				}

				// Display errors, if there are any
				if ( window.peerraiser_field_errors !== undefined ) {
					displayErrors( window.peerraiser_field_errors );
				}
			},

			bindEvents = function() {
				$(document).on("select2:closing", ".peerraiser-form .select2-hidden-accessible", function () {
					$(this).valid();
				});

				$(document).on( 'click', '.peerraiser-form .deletion, .peerraiser-list-table .delete a', function( event ) {
                    var delete_this = confirm(window.peerraiser_admin_object.i10n.confirm_delete);

                    if ( ! delete_this ) {
                        event.preventDefault();
					}
                });

				$(document).on("select2:opening", function (arg) {
					var elem = $(arg.target);
					if ($("#s2id_" + elem.attr("id") + " ul").hasClass("peerraiser-error")) {
						//jquery checks if the class exists before adding.
						$(".select2-drop ul").addClass("peerraiser-error");
					} else {
						$(".select2-drop ul").removeClass("peerraiser-error");
					}
				});
            },

			validationSetup = function() {
				validationObject = $o.peerraiserForm.validate({
					errorClass: "peerraiser-error",
					errorPlacement: function (error, element) {
						var elem             = $(element);
						var desc_box         = elem.parent().find('.cmb2-metabox-description'),
						    select2Container = elem.parent().find('.select2-container');
						if ( desc_box.length ) {
							desc_box.after( error );
						} else {
							if ( select2Container.length ) {
                                select2Container.after( error );
							} else {
                                elem.after( error );
                            }
						}
					},
					highlight: function (element, errorClass, validClass) {
						var elem = $(element);
						elem.parents('.cmb-row').addClass(errorClass);
					},
					unhighlight: function (element, errorClass, validClass) {
						var elem = $(element);
						elem.parents('.cmb-row').removeClass(errorClass);
					}
				});
			},

			renderTooltips = function() {
				$('.cmb-td input, .cmb-td select, .cmb-td textarea').each(function(){
					var tooltip = $(this).data('tooltip');
					if ( tooltip !== undefined ) {
						$(this).parents('.cmb-row').find('.cmb-th label').append('<span class="pr_tooltip"><i class="pr_icon fa fa-question-circle"></i><span class="pr_tip">'+tooltip+'</span></span>');
					}
				});
			},

			denoteRequiredFields = function() {
                $('[data-rule-required="true"]').each(function(){
                    $(this).parents('.cmb-row').find('.cmb-th label').append('<span class="required"></span>');
                });
			},

            displayErrors = function( errors ) {
				for ( var key in errors ) {
                    if ( ! errors.hasOwnProperty( key ) ) continue;

                    var elem  = $('[name=' + key + ']' ),
                    	error = errors[key];

                    error = '<label id="' + key + '-error" class="peerraiser-error" for="' + key + '">' + errors[key] +'</label>';

                    var desc_box         = elem.parent().find('.cmb2-metabox-description'),
                        select2Container = elem.parent().find('.select2-container');

                    if ( desc_box.length ) {
                        desc_box.after( error );
                    } else {
                        if ( select2Container.length ) {
                            select2Container.after( error );
                        } else {
                            elem.after( error );
                        }
                    }
				}
			};

		init();

	}
	// Kick it off
	peerRaiserAdmin();

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

			$(this.element).pr_select2( select2_args );
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
