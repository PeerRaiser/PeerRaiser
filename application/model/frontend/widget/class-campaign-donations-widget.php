<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Donation;

class Campaign_Donations_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-campaign-donations-widget-title', __( 'Campaign Donations', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-campaign-donations-widget-options', array(
            'classname' => 'peerraiser-campaign-donations',
            'description' => __( 'Display a list of donations to a campaign (or all campaigns)', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_campaign_donations', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        $donation_model = new Donation();

        if ( $instance['campaign'] === 'auto' ) {
            $campaign = peerraiser_get_current_campaign();
            $donations = $donation_model->get_donations( array(
                'campaign_id' => $campaign->ID,
                'number'      => $instance['list_size'],
                'status'      => 'completed',
                'is_test'     => false
            ) );
        } elseif( $instance['campaign'] == 'all' || empty( $instance['campaign'] ) ) {
            $donations = $donation_model->get_donations( array(
                'number'  => $instance['list_size'],
                'status'  => 'completed',
                'is_test' => false
            ) );
        } else {
            $campaign = new Campaign( $instance['campaign']);
            $donations = $donation_model->get_donations(array(
                'campaign_id' => $campaign->ID,
                'number'      => $instance['list_size'],
                'status'      => 'completed',
                'is_test'     => false
            ) );
        }

        $this->assign( 'args', $args );
        $this->assign( 'donations', $donations );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-donations' );
    }

    public function form( $instance ) {
        $campaign_model = new Campaign();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent Donations', 'peerraiser' ),
            'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
            'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
            'campaigns' => $campaign_model->get_campaigns(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-campaign-donations' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;
        $instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

        return $instance;
    }
}