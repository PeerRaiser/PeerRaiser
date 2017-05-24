<?php

namespace PeerRaiser\Model\Frontend;

class Campaign_Donate_Button_Widget extends PeerRaiser_Widget {

	public function __construct() {
	    $widget_title = apply_filters( 'peerraiser-campaign-donate-button-widget-title', __( 'Campaign Donate Button', 'peerraiser' ) );
	    $widget_options = apply_filters( 'peerraiser-campaign-donate-button-widget-options', array(
		    'classname' => 'peerraiser-campaign-donate-button',
		    'description' => __( 'Renders a donation button for campaigns', 'peerraiser' ),
        ) );

		parent::__construct('peerraiser_campaign_donate', $widget_title, $widget_options );
	}

	public function widget( $args, $instance ) {
		$plugin_options        = get_option( 'peerraiser_options', array() );

		$view_args = array(
			'donation_page' => get_permalink( $plugin_options[ 'donation_page' ] ),
			'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : wp_kses_post( 'Donate to this campaign', 'peerraiser' ),
		);
		$this->assign( 'peerraiser', $view_args );
		$this->assign( 'campaign', peerraiser_get_current_campaign() );
		$this->assign( 'args', $args );

		echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-donate-button' );
	}

	public function form( $instance ) {
		$view_args = array(
			'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : esc_attr__( 'Donate to this campaign', 'peerraiser' ),
		);
		$this->assign( 'peerraiser', $view_args );

		echo $this->get_text_view( 'backend/widget/peerraiser-campaign-donate-button' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['button_label'] = ( ! empty( $new_instance['button_label'] ) ) ? strip_tags( $new_instance['button_label'] ) : '';

		return $instance;
	}
}