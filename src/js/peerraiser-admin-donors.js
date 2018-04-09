(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminDonors(){
        var $o = {
            select2Fields : {
                donor_user_account : $("#user_id"),
            },

            select2Options : {
                donor_user_account : {
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
        }

        init();

    }

    // Kick it off
    peerRaiserAdminDonors();

});})(jQuery);
