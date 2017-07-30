<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;

class Campaign_Thermometer_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-campaign-thermometer-widget-title', __( 'Campaign Thermometer', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-campaign-thermometer-widget-options', array(
            'classname' => 'peerraiser-campaign-thermometer',
            'description' => __( 'Display a thermometer that shows the progress of the campaign goal', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_campaign_thermometer', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['campaign'] === 'auto' || empty( $instance['campaign'] ) ) {
            $campaign = peerraiser_get_current_campaign();
        } else {
            $campaign = new Campaign( $instance['campaign']);
        }

        $this->assign( 'goal_percentage', round( ( $campaign->donation_value / $campaign->campaign_goal) * 100 ) );
        $this->assign( 'campaign', $campaign );
        $this->assign( 'args', $args );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-thermometer' );
    }

    public function form( $instance ) {
        $campaign_model = new Campaign();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : '',
            'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
            'campaigns' => $campaign_model->get_campaigns(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-campaign-thermometer' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

        return $instance;
    }
}