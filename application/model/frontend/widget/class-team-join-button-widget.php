<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Team;

class Team_Join_Button_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-team-join-button-widget-title', __( 'Team Join Button', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-team-join-button-widget-options', array(
            'classname' => 'peerraiser-team-join-button',
            'description' => __( 'Renders a "join team" button for a team', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_team_join_button', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['team'] === 'auto' || empty( $instance['team'] ) ) {
            $team = peerraiser_get_current_team();
        } else {
            $team = new Team( $instance['team']);
        }

        $campaign = new Campaign( $team->campaign_id );

        $plugin_options = get_option( 'peerraiser_options', array() );

        $view_args = array(
            'join_team_url' => trailingslashit( get_permalink( $plugin_options[ 'registration_page' ] ) ) . $campaign->campaign_slug . '/individual/?team=' . $team->team_slug,
            'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : wp_kses_post( 'Join Team', 'peerraiser' ),
        );
        $this->assign( 'peerraiser', $view_args );
        $this->assign( 'team', $team );
        $this->assign( 'args', $args );

        echo $this->get_text_view( 'frontend/widget/peerraiser-team-join-button' );
    }

    public function form( $instance ) {
        $team_model = new Team();

        $view_args = array(
            'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : esc_attr__( 'Join Team', 'peerraiser' ),
            'team' => ! empty( $instance['team'] ) ? $instance['team'] : 'auto',
            'teams' => $team_model->get_teams(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-team-join-button' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['button_label'] = ( ! empty( $new_instance['button_label'] ) ) ? strip_tags( $new_instance['button_label'] ) : '';
        $instance['team'] = ! empty( $new_instance['team'] ) ? esc_attr( $new_instance['team'] ) : 'auto';

        return $instance;
    }
}