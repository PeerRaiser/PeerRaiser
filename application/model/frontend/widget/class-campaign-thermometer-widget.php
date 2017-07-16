<?php

namespace PeerRaiser\Model\Frontend\Widget;

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
		$plugin_options = get_option( 'peerraiser_options', array() );
		$campaign = peerraiser_get_current_campaign();

		$view_args = array(
			'goal_percentage' => round( ( $campaign->donation_value / $campaign->campaign_goal) * 100 )
		);
		$this->assign( 'peerraiser', $view_args );
		$this->assign( 'campaign', $campaign );
		$this->assign( 'args', $args );
		$this->assign( 'instance', $instance );

		echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-thermometer' );
	}

	public function form( $instance ) {
		$view_args = array(
			'title' => ! empty( $instance['title'] ) ? $instance['title'] : '',
		);
		$this->assign( 'peerraiser', $view_args );

		echo $this->get_text_view( 'backend/widget/peerraiser-campaign-thermometer' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';

		return $instance;
	}
}