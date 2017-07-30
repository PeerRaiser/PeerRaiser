<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;

class Campaign_Total_Raised_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-campaign-total-raised-widget-title', __( 'Campaign Total Raised', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-campaign-total-raised-widget-options', array(
            'classname' => 'peerraiser-campaign-total-raised',
            'description' => __( 'Display the total amount raised by the campaign', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_campaign_total_raised', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['campaign'] === 'auto' || empty( $instance['campaign'] ) ) {
            $campaign = peerraiser_get_current_campaign();
        } else {
            $campaign = new Campaign( $instance['campaign']);
        }

        $hide_if_zero  = ! empty( $instance['hide_if_zero'] ) ? $instance['hide_if_zero'] : 'false';

        if ( $hide_if_zero === 'on' && $campaign->donation_value === 0.00 ) {
            return;
        }

        $view_args = array(
            'before_amount' => $instance['before_amount'],
            'after_amount'  => $instance['after_amount'],
        );
        $this->assign( 'peerraiser', $view_args );
        $this->assign( 'campaign', $campaign );
        $this->assign( 'args', $args );

        echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-total-raised' );
    }

    public function form( $instance ) {
        $campaign_model = new Campaign();

        $view_args = array(
            'hide_if_zero'  => ! empty( $instance['hide_if_zero'] ) ? $instance['hide_if_zero'] : 'false',
            'before_amount' => ! empty( $instance['before_amount'] ) ? $instance['before_amount'] : '',
            'after_amount'  => ! empty( $instance['after_amount'] ) ? $instance['after_amount'] : __( '<h4>Total Raised</h4>', 'peerraiser' ),
            'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
            'campaigns' => $campaign_model->get_campaigns(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-campaign-total-raised' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['hide_if_zero']  = $new_instance['hide_if_zero'];
        $instance['before_amount'] = $new_instance['before_amount'];
        $instance['after_amount']  = $new_instance['after_amount'];
        $instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

        return $instance;
    }
}