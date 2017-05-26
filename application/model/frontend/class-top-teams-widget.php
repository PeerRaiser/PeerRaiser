<?php

namespace PeerRaiser\Model\Frontend;

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
		$plugin_options = get_option( 'peerraiser_options', array() );

		$view_args = array(

		);
		$this->assign( 'peerraiser', $view_args );
		$this->assign( 'campaign', peerraiser_get_current_campaign() );
		$this->assign( 'args', $args );
		$this->assign( 'instance', $instance );

		echo $this->get_text_view( 'frontend/widget/peerraiser-top-teams' );
	}

	public function form( $instance ) {
		$view_args = array(
			'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Teams', 'peerraiser' ),
			'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
		);
		$this->assign( 'peerraiser', $view_args );

		echo $this->get_text_view( 'backend/widget/peerraiser-top-teams' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;

		return $instance;
	}
}