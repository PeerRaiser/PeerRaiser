(function( $ ) {$(function() {
    'use strict';

    function peerRaiserAdminTeam(){
        var $o = {
            teamDateContainer : $('.misc-pub-section.team-date'),
            editTeamDate      : $('.edit-team-date'),
            teamDateWrap      : $('.team-date .timestamp-wrap'),
            teamDateCancel    : $('.team-date .cancel-timestamp'),
            teamDateSave      : $('.team-date .save-timestamp'),

            select2Fields : {
                team_leader : $("#_peerraiser_team_leader"),
                campaign    : $("#_peerraiser_campaign_id"),
            },

            select2Options : {
                team_leader : {
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
                        var html = '<span class="pr_name">' + data.text + '</span>';
                        if ( data.id ) {
                            html += '<span class="pr_id">Campaign ID: ' + data.id + '</span>';
                        }
                        return $('<span>').html(html);
                    }
                },
            },
        },

        init = function(){
            bindEvents();
            renderSelect();
        },

        bindEvents = function() {
            // Edit Donation Status
            $o.editTeamDate.on('click', function(e){
                e.preventDefault();

                $(this).hide();
                $o.teamDateWrap.parent().slideDown('fast');
            });

            // Cancel Edit Donation Status
            $o.teamDateCancel.on('click', function(e){
                e.preventDefault();

                $o.teamDateWrap.parent().slideUp('fast', function(){
                    $o.editTeamDate.show();
                });

                var value = $o.teamDateWrap.find('input[type=hidden]').val();

                $o.teamDateWrap.find('select option[value="'+value+'"]').attr('selected', true);
            });

            // Save Edit Donation Status
            $o.teamDateSave.on('click', function(e){
                e.preventDefault();

                $o.teamDateWrap.parent().slideUp('fast', function(){
                    $o.editTeamDate.show();
                });

                var value = $o.teamDateWrap.find('select option:selected').val(),
                    label = $o.teamDateWrap.find('select option:selected').text();

                $('.misc-pub-section.donation-status').attr('class', 'misc-pub-section donation-status ' + value );

                $o.teamDateWrap.find('input[type=hidden]').val( value );
                $o.teamDateContainer.find('strong').text( label );
            });
        },

        renderSelect = function() {
            for ( var key in $o.select2Fields ) {
                if ( $o.select2Fields[key].length && $o.select2Fields[key].is( 'select' ) ){
                    $o.select2Fields[key].renderSelect($o.select2Options[key]);
                }
            }
        };

        init();

    }

    // Kick it off
    peerRaiserAdminTeam();

});})(jQuery);
