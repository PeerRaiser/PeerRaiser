<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;

class Top_Teams_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-top-teams-widget-title', __( 'Top Teams', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-top-teams-widget-options', array(
            'classname' => 'peerraiser-top-teams',
            'description' => __( 'Display a list of the top teams for the current campaign', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_top_teams', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['campaign'] === 'auto' ) {
            $campaign = peerraiser_get_current_campaign();
            $top_teams = peerraiser_get_top_teams( $instance['list_size'], array( 'campaign_id' => $campaign->ID ) );
        } elseif( $instance['campaign'] == 'all' || empty( $instance['campaign'] ) ) {
            $top_teams = peerraiser_get_top_teams( $instance['list_size'] );
        } else {
            $campaign = new Campaign( $instance['campaign']);
            $top_teams = peerraiser_get_top_teams( $instance['list_size'], array( 'campaign_id' => $campaign->ID ) );
        }

        $this->assign( 'args', $args );
        $this->assign( 'top_teams', $top_teams );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-top-teams' );
    }

    public function form( $instance ) {
        $campaign_model = new Campaign();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Teams', 'peerraiser' ),
            'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
            'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
            'campaigns' => $campaign_model->get_campaigns(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-top-teams' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;
        $instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

        return $instance;
    }
}