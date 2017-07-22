<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Fundraiser;

class Fundraiser_Top_Donors_Widget extends PeerRaiser_Widget {

	public function __construct() {
		$widget_title = apply_filters( 'peerraiser-fundraiser-top-donors-widget-title', __( 'Fundraiser Top Donors', 'peerraiser' ) );
		$widget_options = apply_filters( 'peerraiser-fundraiser-top-donors-widget-options', array(
			'classname' => 'peerraiser-fundraiser-top-donors',
			'description' => __( 'Display a list of the top donors by fundraiser', 'peerraiser' ),
		) );

		parent::__construct('peerraiser_fundraiser_top_donors', $widget_title, $widget_options );
	}

	public function widget( $args, $instance ) {
		if ( $instance['fundraiser'] === 'auto' || empty( $instance['fundraiser'] ) ) {
			$fundraiser = peerraiser_get_current_fundraiser();
			$top_donors = peerraiser_get_top_donors_to_fundraiser( $fundraiser->ID, $instance['list_size'] );
		} else {
			$fundraiser = new Fundraiser( $instance['fundraiser']);
			$top_donors = peerraiser_get_top_donors_to_fundraiser( $fundraiser->ID, $instance['list_size'] );
		}

		$this->assign( 'args', $args );
		$this->assign( 'top_donors', $top_donors );
		$this->assign( 'instance', $instance );

		echo $this->get_text_view( 'frontend/widget/peerraiser-fundraiser-top-donors' );
	}

	public function form( $instance ) {
		$fundraiser_model = new Fundraiser();

		$view_args = array(
			'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Donors', 'peerraiser' ),
			'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
			'fundraiser' => ! empty( $instance['fundraiser'] ) ? $instance['fundraiser'] : 'auto',
			'fundraisers' => $fundraiser_model->get_fundraisers(),
		);
		$this->assign( 'peerraiser', $view_args );

		echo $this->get_text_view( 'backend/widget/peerraiser-fundraiser-top-donors' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;
		$instance['fundraiser'] = ! empty( $new_instance['fundraiser'] ) ? esc_attr( $new_instance['fundraiser'] ) : 'auto';

		return $instance;
	}
}