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
                            page: params.page,
                            peerraiser_group: 'participants',
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

                $('#mm').val($('#hidden_mm').val());
                $('#jj').val($('#hidden_jj').val());
                $('#aa').val($('#hidden_aa').val());
            });

            // Save Edit Donation Status
            $o.teamDateSave.on('click', function(e){
                e.preventDefault();

                var aa = $('#aa').val(), mm = $('#mm').val(), jj = $('#jj').val();

                $o.teamDateWrap.parent().slideUp('fast', function(){
                    $o.editTeamDate.show();
                });

                $o.teamDateContainer.find('.timestamp strong').html(
                    "%1$s %2$s, %3$s"
                        .replace( '%1$s', $( 'option[value="' + mm + '"]', '#mm' ).attr( 'data-text' ) )
                        .replace( '%2$s', parseInt( jj, 10 ) )
                        .replace( '%3$s', aa )
                );

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
