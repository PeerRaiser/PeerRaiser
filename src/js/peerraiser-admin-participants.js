(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminParticipants(){
        var $o = {
            select2Fields : {
                participant_user_account : $("#user_id"),
            },

            select2Options : {
                participant_user_account : {
                    data : function (params) {
                        return {
                            action: 'peerraiser_get_users',
                            q: params.term,
                            page: params.page,
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
            },

        },

        init = function(){
            bindEvents();
            renderSelect();
            renderTooltips();
        },

        bindEvents = function() {
            $('.cmb2-id--account-type select').on('change', showAccountTypeFields);
        },

        renderSelect = function() {
            for ( var key in $o.select2Fields ) {
                if ( $o.select2Fields[key].length ){
                    $o.select2Fields[key].renderSelect($o.select2Options[key]);
                }
            }
        },

        renderTooltips = function() {
            $('.cmb-td input, .cmb-td select, .cmb-td textarea').each(function(){
                var tooltip = $(this).data('tooltip');
                if ( tooltip !== undefined ) {
                    $(this).parents('.cmb-row').find('.cmb-th').append('<span class="pr_tooltip"><i class="pr_icon fa fa-question-circle"></i><span class="pr_tip">'+tooltip+'</span></span>');
                }
            });
        },

        showAccountTypeFields = function( event ) {
            var account_type = $(event.target).val();

            $("#cmb2-metabox-peerraiser-account-info > .cmb-row").not('.cmb2-id--account-type').hide();
            $("#cmb2-metabox-peerraiser-account-info [data-account-type='"+account_type+"']").parents('.cmb-row').show();

            redoLastOfType();
        },

        redoLastOfType = function() {
            $('.cmb2-metabox > .cmb-row').removeClass('last-of-type-visible');
            $('.cmb2-metabox > .cmb-row:visible:last').addClass('last-of-type-visible');
        }

        init();

    }

    // Kick it off
    peerRaiserAdminParticipants();

});})(jQuery);
