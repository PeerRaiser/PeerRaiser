<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Team;

class Team_Thermometer_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-team-thermometer-widget-title', __( 'Team Thermometer', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-team-thermometer-widget-options', array(
            'classname' => 'peerraiser-team-thermometer',
            'description' => __( 'Display a thermometer that shows the progress of the team goal', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_team_thermometer', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['team'] === 'auto' || empty( $instance['team'] ) ) {
            $team = peerraiser_get_current_team();
        } else {
            $team = new Team( $instance['team']);
        }

        $this->assign( 'goal_percentage', round( ( $team->donation_value / $team->team_goal) * 100 ) );
        $this->assign( 'team', $team );
        $this->assign( 'args', $args );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-team-thermometer' );
    }

    public function form( $instance ) {
        $team_model = new Team();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : '',
            'team' => ! empty( $instance['team'] ) ? $instance['team'] : 'auto',
            'teams' => $team_model->get_teams(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-team-thermometer' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['team'] = ! empty( $new_instance['team'] ) ? esc_attr( $new_instance['team'] ) : 'auto';

        return $instance;
    }
}