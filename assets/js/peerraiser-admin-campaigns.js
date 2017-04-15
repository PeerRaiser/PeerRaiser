(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminCampaigns(){
        var $o = {
            dashboardTab            : $('#toplevel_page_peerraiser-dashboard'),
            dasboardTabLink         : $('#toplevel_page_peerraiser-dashboard > a'),
            fundraiserLink          : $('#toplevel_page_peerraiser-dashboard a[href$="pr_campaign"]'),
            campaignStatusContainer : $('.misc-pub-section.campaign-status'),
            editCampaignStatus      : $('.edit-campaign-status'),
            campaignStatusSelect    : $('#campaign-status-select'),
            campaignStatusCancel    : $('#campaign-status-select .cancel'),
            campaignStatusSave      : $('#campaign-status-select .save'),

            select2Fields           : {
                thank_you_page      : $("#_peerraiser_thank_you_page"),
                participants        : $("#_peerraiser_campaign_participants")
            },

            select2Options         : {
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
            },
        },

        init = function(){
            bindEvents();
            renderSelect();
        },

        bindEvents = function() {
            // Edit Campaign Status
            $o.editCampaignStatus.on('click', function(e){
                e.preventDefault();

                $(this).hide();
                $o.campaignStatusSelect.slideDown('fast');
            });

            // Cancel Edit Campaign Status
            $o.campaignStatusCancel.on('click', function(e){
                e.preventDefault();

                $o.campaignStatusSelect.slideUp('fast', function(){
                    $o.editCampaignStatus.show();
                });

                var value = $o.campaignStatusSelect.find('input[type=hidden]').val();

                $o.campaignStatusSelect.find('select option[value="'+value+'"]').attr('selected', true);
            });

            // Save Edit Campaign Status
            $o.campaignStatusSave.on('click', function(e){
                e.preventDefault();

                $o.campaignStatusSelect.slideUp('fast', function(){
                    $o.editCampaignStatus.show();
                });

                var value = $o.campaignStatusSelect.find('select option:selected').val(),
                    label = $o.campaignStatusSelect.find('select option:selected').text();

                $('.misc-pub-section.campaign-status').attr('class', 'misc-pub-section campaign-status ' + value );

                $o.campaignStatusSelect.find('input[type=hidden]').val( value );
                $o.campaignStatusContainer.find('strong').text( label );
            });
        },

        renderSelect = function() {
            for ( var key in $o.select2Fields ) {
                if ( $o.select2Fields[key].length ){
                    $o.select2Fields[key].renderSelect($o.select2Options[key]);
                }
            }
        };

        init();

    }

    // Kick it off
    peerRaiserAdminCampaigns();

});})(jQuery);
