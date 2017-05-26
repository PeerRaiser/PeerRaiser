<?php

namespace PeerRaiser\Model\Frontend;

class Top_Fundraisers_Widget extends PeerRaiser_Widget {

	public function __construct() {
	    $widget_title = apply_filters( 'peerraiser-top-fundraisers-widget-title', __( 'Top Fundraisers', 'peerraiser' ) );
	    $widget_options = apply_filters( 'peerraiser-top-fundraisers-widget-options', array(
		    'classname' => 'peerraiser-top-fundraisers',
		    'description' => __( 'Display a list of the top fundraisers for the current campaign', 'peerraiser' ),
        ) );

		parent::__construct('peerraiser_top_fundraisers', $widget_title, $widget_options );
	}

	public function widget( $args, $instance ) {
		$plugin_options = get_option( 'peerraiser_options', array() );

		$view_args = array(

		);
		$this->assign( 'peerraiser', $view_args );
		$this->assign( 'campaign', peerraiser_get_current_campaign() );
		$this->assign( 'args', $args );
		$this->assign( 'instance', $instance );

		echo $this->get_text_view( 'frontend/widget/peerraiser-top-fundraisers' );
	}

	public function form( $instance ) {
		$view_args = array(
			'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Fundraisers', 'peerraiser' ),
			'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
		);
		$this->assign( 'peerraiser', $view_args );

		echo $this->get_text_view( 'backend/widget/peerraiser-top-fundraisers' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;

		return $instance;
	}
}