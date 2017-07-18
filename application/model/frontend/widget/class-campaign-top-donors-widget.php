<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;

class Campaign_Top_Donors_Widget extends PeerRaiser_Widget {

	public function __construct() {
		$widget_title = apply_filters( 'peerraiser-campaign-top-donors-widget-title', __( 'Campaign Top Donors', 'peerraiser' ) );
		$widget_options = apply_filters( 'peerraiser-campaign-top-donors-widget-options', array(
			'classname' => 'peerraiser-campaign-top-donors',
			'description' => __( 'Display a list of the top donors by campaign (or all)', 'peerraiser' ),
		) );

		parent::__construct('peerraiser_campaign_top_donors', $widget_title, $widget_options );
	}

	public function widget( $args, $instance ) {
		if ( $instance['campaign'] === 'auto' ) {
			$campaign = peerraiser_get_current_campaign();
			$top_donors = peerraiser_get_top_donors( $instance['list_size'], array( 'campaign_id' => $campaign->ID ) );
		} elseif( $instance['campaign'] == 'all' || empty( $instance['campaign'] ) ) {
			$top_donors = peerraiser_get_top_donors( $instance['list_size'] );
		} else {
			$campaign = new Campaign( $instance['campaign']);
			$top_donors = peerraiser_get_top_donors( $instance['list_size'], array( 'campaign_id' => $campaign->ID ) );
		}

		$this->assign( 'args', $args );
		$this->assign( 'top_donors', $top_donors );
		$this->assign( 'instance', $instance );

		echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-top-donors' );
	}

	public function form( $instance ) {
		$campaign_model = new Campaign();

		$view_args = array(
			'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Donors', 'peerraiser' ),
			'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
			'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
			'campaigns' => $campaign_model->get_campaigns(),
		);
		$this->assign( 'peerraiser', $view_args );

		echo $this->get_text_view( 'backend/widget/peerraiser-campaign-top-donors' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
		$instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;
		$instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

		return $instance;
	}
}