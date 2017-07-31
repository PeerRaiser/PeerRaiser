(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminDonations(){
        var $o = {
            donationStatusContainer : $('.donation-status'),
            donationTypeContainer   : $('.donation-type'),
            editDonationStatus      : $('.edit-donation-status'),
            editDonationType        : $('.edit-donation-type'),
            donationStatusSelect    : $('#donation-status-select'),
            donationTypeSelect      : $('#donation-type-select'),
            donationStatusCancel    : $('#donation-status-select .cancel'),
            donationTypeCancel      : $('#donation-type-select .cancel'),
            donationStatusSave      : $('#donation-status-select .save'),
            donationTypeSave        : $('#donation-type-select .save'),

            select2Fields : {
                donor      : $(".peerraiser-form #donor"),
                campaign   : $(".peerraiser-form #campaign"),
                fundraiser : $(".peerraiser-form #fundraiser"),
            },

            select2Options : {
                donor : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_donors',
                            s: params.term,
                            page: params.page
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Donor ID: ' + data.id + '</span>';
                        }
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
                campaign : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_campaigns',
                            s: params.term,
                            page: params.page
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Campaign ID: ' + data.id + '</span>';
                        }
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
                fundraiser : {
                    data : function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['fundraiser']
                        };
                    },
                    templateResult : function(data) {
                        var html = '<span class="display_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="user_id">Fundraiser ID: ' + data.id + '</span>';
                        }
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
            },

        },

        init = function(){
            bindEvents();
            renderSelect();
            renderTooltips();
        },

        bindEvents = function() {
            // Show/hide the fundraiser if campaign selected or not
            $o.select2Fields.campaign.on('change', function(){
                var id = $(this).val();
                if ( id !== null ) {
                    $o.select2Fields.fundraiser.prop('disabled', false);
                    $o.select2Fields.fundraiser.val('');

                    $o.select2Options.fundraiser.data = function ( params ) {
                        return {
                            action: 'peerraiser_get_posts',
                            s: params.term,
                            page: params.page,
                            post_type  : ['fundraiser'],
                            taxonomy : ['peerraiser_campaign:'+id],
                            term_id : id
                        };
                    };
                    $o.select2Fields.fundraiser.renderSelect($o.select2Options.fundraiser);
                } else {
                    $o.select2Fields.fundraiser.prop('disabled', 'disabled');
                }
            });

            // Edit Donation Status
            $o.editDonationStatus.on('click', function(e){
                e.preventDefault();

                $(this).hide();
                $o.donationStatusSelect.slideDown('fast');
            });

            // Cancel Edit Donation Status
            $o.donationStatusCancel.on('click', function(e){
                e.preventDefault();

                $o.donationStatusSelect.slideUp('fast', function(){
                    $o.editDonationStatus.show();
                });

                var value = $o.donationStatusSelect.find('input[type=hidden]').val();

                $o.donationStatusSelect.find('select option[value="'+value+'"]').attr('selected', true);
            });

            // Save Edit Donation Status
            $o.donationStatusSave.on('click', function(e){
                e.preventDefault();

                $o.donationStatusSelect.slideUp('fast', function(){
                    $o.editDonationStatus.show();
                });

                var value = $o.donationStatusSelect.find('select option:selected').val(),
                    label = $o.donationStatusSelect.find('select option:selected').text();

                if ( $('.donation-status.misc-pub-section').length ) {
                    $('.donation-status.misc-pub-section').attr('class', 'misc-pub-section donation-status ' + value );
                } else {
                    $('.donation-status').attr('class', 'donation-status ' + value );
                }

                $o.donationStatusSelect.find('input[type=hidden]').val( value );
                $o.donationStatusContainer.find('strong').text( label );
            });

            // Edit Donation Type
            $o.editDonationType.on('click', function(e){
                e.preventDefault();

                $(this).hide();
                $o.donationTypeSelect.slideDown('fast');
            });

            // Cancel Edit Donation Type
            $o.donationTypeCancel.on('click', function(e){
                e.preventDefault();

                $o.donationTypeSelect.slideUp('fast', function(){
                    $o.editDonationType.show();
                });

                var value = $o.donationTypeSelect.find('input[type=hidden]').val();

                $o.donationTypeSelect.find('select option[value="'+value+'"]').attr('selected', true);
            });

            // Save Edit Donation Type
            $o.donationTypeSave.on('click', function(e){
                e.preventDefault();

                $o.donationTypeSelect.slideUp('fast', function(){
                    $o.editDonationType.show();
                });

                var value = $o.donationTypeSelect.find('select option:selected').val(),
                    label = $o.donationTypeSelect.find('select option:selected').text();

                $o.donationTypeSelect.find('input[type=hidden]').val( value );
                $o.donationTypeContainer.find('strong').text( label );
            });
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
        };

        init();

    }

    // Kick it off
    peerRaiserAdminDonations();

});})(jQuery);