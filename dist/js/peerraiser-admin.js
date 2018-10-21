/**
 * PeerRaiser Admin
 *
 * This is the main admin script that applies to the WordPress admin as a whole
 */

"use strict";

if ( typeof(peerraiser) === 'undefined' ) {
    peerraiser = {};
} else if ( typeof(peerraiser.admin) ) {
    peerraiser.admin = {};
}

peerraiser.admin = function ( $ ) {
    // Private Variables
    let $window = $(window),
        $doc    = $(document),
        $body   = $('body'),
        $elements = {
            peerraiserForm : $('form.peerraiser-form'),
        },
        temp_data = {},
        validation_object = {},
        self;

    return {

        // Kick everything off
        init: function() {
            self = peerraiser.admin;

            $body
                .on( 'select2:closing', '.peerraiser-form .select2-hidden-accessible', self.check_validation )
                .on( 'click', '.peerraiser-form .deletion, .peerraiser-list-table .delete a', self.handle_deletion )
                .on( 'select2:opening', self.maybe_add_peerraiser_error )
                .on( 'click', '.peerraiser-form .edit-slug', self.edit_permalink )
                .on( 'click', '.peerraiser-form #edit-slug-buttons .cancel', self.cancel_edit_permalink )
                .on( 'click', '.peerraiser-form #edit-slug-buttons .save', self.save_edit_permalink );

            self.render_tooltips();
            self.denote_required_fields();

            // If a PeerRaiser form is present, use jQuery validate
            if ( $elements.peerraiserForm.length ) {
                self.validation_setup();
            }

            // Display errors, if there are any
            if ( window.peerraiser_field_errors !== undefined ) {
                self.display_errors( window.peerraiser_field_errors );
            }

            if ( $elements.peerraiserForm.find( '#editable-post-name' ).length ) {
                $elements.edit_slug_box      = $elements.peerraiserForm.find('#edit-slug-box');
                $elements.editable_post_name = $elements.peerraiserForm.find('#editable-post-name');
                $elements.real_slug          = $elements.peerraiserForm.find('#post_name');
                $elements.permalink          = $elements.peerraiserForm.find('#sample-permalink');
                $elements.buttons            = $elements.peerraiserForm.find('#edit-slug-buttons');
                $elements.post_name_full     = $elements.peerraiserForm.find('#editable-post-name-full');
            }

            // Add 'last-of-type-visible' class to cmb2 rows
            $('.cmb2-metabox > .cmb-row:visible:last').addClass('last-of-type-visible');
        },

        check_validation: function() {
            $(this).valid();
        },

        handle_deletion: function() {
            let delete_this = confirm(window.peerraiser_admin_object.i10n.confirm_delete);

            if ( ! delete_this ) {
                event.preventDefault();
            }
        },

        maybe_add_peerraiser_error: function(arg) {
            let elem = $(arg.target);

            if ($("#s2id_" + elem.attr("id") + " ul").hasClass("peerraiser-error")) {
                $(".select2-drop ul").addClass("peerraiser-error");
            } else {
                $(".select2-drop ul").removeClass("peerraiser-error");
            }
        },

        validation_setup: function() {
            validation_object = $elements.peerraiserForm.validate({
                errorClass: "peerraiser-error",
                errorPlacement: function (error, element) {
                    let elem             = $(element);
                    let desc_box         = elem.parent().find('.cmb2-metabox-description'),
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
                    let elem = $(element);
                    elem.parents('.cmb-row').addClass(errorClass);
                },
                unhighlight: function (element, errorClass, validClass) {
                    let elem = $(element);
                    elem.parents('.cmb-row').removeClass(errorClass);
                }
            });
        },

        render_tooltips: function() {
            $('.cmb-td input, .cmb-td select, .cmb-td textarea').each(function(){
                let tooltip = $(this).data('tooltip');

                if ( tooltip !== undefined ) {
                    $(this).parents('.cmb-row').find('.cmb-th label').append('<span class="pr_tooltip"><i class="pr_icon fa fa-question-circle"></i><span class="pr_tip">'+tooltip+'</span></span>');
                }
            });
        },

        denote_required_fields: function() {
            $('[data-rule-required="true"]').each(function(){
                $(this).parents('.cmb-row').find('.cmb-th label').append('<span class="required"></span>');
            });
        },

        edit_permalink: function() {
            // Save the current slug so it can be reverted if canceled
            temp_data.slug_revert = $elements.peerraiserForm.find('#editable-post-name').text();

            // Remove the anchor tag
            $elements.peerraiserForm.find('#sample-permalink a').contents().unwrap();

            // Change the buttons
            $elements.buttons.html( '<button type="button" class="save button button-small">' + window.peerraiser_admin_object.i10n.ok + '</button> <button type="button" class="cancel button-link">' + window.peerraiser_admin_object.i10n.cancel + '</button>' );

            // Insert the input box
            $elements.peerraiserForm.find('#editable-post-name').html( '<input type="text" id="new-post-slug" value="' + $elements.post_name_full.text() + '" autocomplete="off">' ).children( 'input' ).keydown( function( e ) {
                let key = e.which;

                // On [enter], just save the new slug, don't save the post.
                if ( 13 === key ) {
                    e.preventDefault();
                    $elements.buttons.children( '.save' ).click();
                }

                // On [esc] cancel the editing.
                if ( 27 === key ) {
                    $elements.buttons.children( '.cancel' ).click();
                }
            } ).keyup( function() {
                $elements.real_slug.val( this.value );
            }).focus();
        },

        cancel_edit_permalink: function() {
            // Remove the input box
            $elements.peerraiserForm.find('#editable-post-name').html(temp_data.slug_revert);

            // Wrap permalink in an anchor tag
            $elements.peerraiserForm.find('#sample-permalink').contents().wrapAll('<a href="'+$elements.permalink.text()+'"></a>');

            // Change the buttons back
            $elements.buttons.html('<button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="Edit permalink">'+window.peerraiser_admin_object.i10n.edit+'</button>');
        },

        save_edit_permalink: function() {
            let new_slug = $elements.editable_post_name.find('input').val();

            if ( new_slug === $elements.post_name_full.text() || new_slug === '' ) {
                $elements.buttons.children('.cancel').click();

                return;
            }

            $elements.post_name_full.text( new_slug );

            $.post(
                window.peerraiser_admin_object.ajax_url,
                {
                    action:      'peerraiser_get_slug',
                    new_slug:    new_slug,
                    object_id:   $elements.peerraiserForm.data('object-id'),
                    object_type: $elements.peerraiserForm.data('object-type'),
                    nonce: 		 $elements.edit_slug_box.data('edit-slug-nonce')
                },
                function(data) {
                    data = JSON.parse(data);
                    $elements.peerraiserForm.find('#editable-post-name').html( data.slug_abridged );
                    $elements.peerraiserForm.find('#slug').val( data.new_slug );
                    $elements.post_name_full.text( data.new_slug );
                }
            );

            // Wrap permalink in an anchor tag
            $elements.peerraiserForm.find('#sample-permalink').contents().wrapAll('<a href="'+$elements.permalink.text()+'"></a>');

            // Change the buttons back
            $elements.buttons.html('<button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="Edit permalink">'+window.peerraiser_admin_object.i10n.edit+'</button>');
        },

        display_errors: function( errors ) {
            for ( let key in errors ) {
                if ( ! errors.hasOwnProperty( key ) ) continue;

                let elem  = $('[name=' + key + ']' ),
                    error = errors[key];

                error = '<label id="' + key + '-error" class="peerraiser-error" for="' + key + '">' + errors[key] +'</label>';

                let desc_box         = elem.parent().find('.cmb2-metabox-description'),
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
        }
    }
} ( jQuery );

jQuery(function( $ ) {
    peerraiser.admin.init();

    // Plugin constructor creates a new instance of the plugin for each DOM node
    // that the plugin is called on
    function RenderSelect(element, options) {
        self = this;
        // The DOM node(s) that called the plugin
        this.element = element;
        // The default options
        this._defaults = $.fn.renderSelect.defaults;
        // Merge the default options with whatever options were passed (if any)
        this.options = $.extend({}, this._defaults, options);
        // The "init" function is the starting point for all the plugin logic
        this.init();
    }

    $.extend(RenderSelect.prototype, {
        init: function () {
            this.render_select();
        },
        render_select: function () {
            let select2_args = {
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

            $(this.element).pr_select2(select2_args);
        },
        decode_data: function (data) {
            // Return if no data
            if (!data) return [];

            $.each(data, function (k, v) {
                data[k].text = self.decode(v.text);
                if (typeof v.children !== 'undefined') {
                    data[k].children = self.decode_data(v.children);
                }
            });

            return data;
        },
        decode: function (string) {
            return $('<div/>').html(string).text();
        },
        count_data: function (data) {
            let i = 0;

            // Return if no data
            if (!data) return i;

            $.each(data, function (k, v) {
                i++;
                if (typeof v.children !== 'undefined') {
                    i += v.children.length;
                }
            });

            return i;
        },
    });

    // Extend jQuery
    $.fn.renderSelect = function (options) {
        this.each(function () {
            $.data(this, "plugin_renderSelect", new RenderSelect(this, options));
        });

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
        escapeMarkup: function (m) {
            return m;
        },
        processResults: function (data, params) {
            setTimeout(function () {
                let $prev_options = null,
                    $prev_group = null;

                $('.select2-results__option[role="group"]').each(function () {
                    let $options = $(this).children('ul'),
                        $group = $(this).children('strong');

                    if ($prev_group !== null && $group.text() === $prev_group.text()) {
                        $prev_options.append($options.children());
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
});
/**
 * Aardvark
 */
