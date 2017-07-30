<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;

class Top_Fundraisers_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-top-fundraisers-widget-title', __( 'Top Fundraisers', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-top-fundraisers-widget-options', array(
            'classname' => 'peerraiser-top-fundraisers',
            'description' => __( 'Display a list of the top fundraisers for a campaign', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_top_fundraisers', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['campaign'] === 'auto' ) {
            $campaign = peerraiser_get_current_campaign();
            $top_fundraisers = peerraiser_get_top_fundraisers( $instance['list_size'], array( 'campaign_id' => $campaign->ID ) );
        } elseif( $instance['campaign'] == 'all' || empty( $instance['campaign'] ) ) {
            $top_fundraisers = peerraiser_get_top_fundraisers( $instance['list_size'] );
        } else {
            $campaign = new Campaign( $instance['campaign']);
            $top_fundraisers = peerraiser_get_top_fundraisers( $instance['list_size'], array( 'campaign_id' => $campaign->ID ) );
        }

        $this->assign( 'args', $args );
        $this->assign( 'top_fundraisers', $top_fundraisers );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-top-fundraisers' );
    }

    public function form( $instance ) {
        $campaign_model = new Campaign();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Fundraisers', 'peerraiser' ),
            'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
            'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
            'campaigns' => $campaign_model->get_campaigns(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-top-fundraisers' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;
        $instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

        return $instance;
    }
}