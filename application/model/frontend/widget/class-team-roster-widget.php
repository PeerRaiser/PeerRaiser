<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Team;

class Team_Roster_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-team-roster-widget-title', __( 'Team Roster', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-team-roster-widget-options', array(
            'classname' => 'peerraiser-team-roster',
            'description' => __( 'Displays a list of the team members', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_team_roster', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        $options = array(
            'number' => $instance['list_size'],
        );

        if ( $instance['team'] === 'auto' ) {
            $team = peerraiser_get_current_team();
        } else {
            $team = new Team( $instance['team']);
        }

        $fundraisers = peerraiser_get_team_fundraisers( $team->ID, $options );

        $this->assign( 'args', $args );
        $this->assign( 'instance', $instance );
        $this->assign( 'fundraisers', $fundraisers );

        echo $this->get_text_view( 'frontend/widget/peerraiser-team-roster' );
    }

    public function form( $instance ) {
        $team_model = new Team();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Team Roster', 'peerraiser' ),
            'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : -1,
            'team' => ! empty( $instance['team'] ) ? $instance['team'] : 'auto',
            'teams' => $team_model->get_teams(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-team-roster' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : -1;
        $instance['team'] = ! empty( $new_instance['team'] ) ? esc_attr( $new_instance['team'] ) : 'auto';

        return $instance;
    }
}