<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Fundraiser;

class Fundraiser_Total_Raised_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-fundraiser-total-raised-widget-title', __( 'Fundraiser Total Raised', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-fundraiser-total-raised-widget-options', array(
            'classname' => 'peerraiser-fundraiser-total-raised',
            'description' => __( 'Display the total amount raised by the fundraiser', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_fundraiser_total_raised', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['fundraiser'] === 'auto' || empty( $instance['fundraiser'] ) ) {
            $fundraiser = peerraiser_get_current_fundraiser();
        } else {
            $fundraiser = new Fundraiser( $instance['fundraiser']);
        }

        $hide_if_zero  = ! empty( $instance['hide_if_zero'] ) ? $instance['hide_if_zero'] : 'false';

        if ( $hide_if_zero === 'on' && $fundraiser->donation_value === 0.00 ) {
            return;
        }

        $view_args = array(
            'before_amount' => $instance['before_amount'],
            'after_amount'  => $instance['after_amount'],
        );
        $this->assign( 'peerraiser', $view_args );
        $this->assign( 'fundraiser', $fundraiser );
        $this->assign( 'args', $args );

        echo $this->get_text_view( 'frontend/widget/peerraiser-fundraiser-total-raised' );
    }

    public function form( $instance ) {
        $fundraiser_model = new Fundraiser();

        $view_args = array(
            'hide_if_zero'  => ! empty( $instance['hide_if_zero'] ) ? $instance['hide_if_zero'] : 'false',
            'before_amount' => ! empty( $instance['before_amount'] ) ? $instance['before_amount'] : '',
            'after_amount'  => ! empty( $instance['after_amount'] ) ? $instance['after_amount'] : __( '<h4>Total Raised</h4>', 'peerraiser' ),
            'fundraiser' => ! empty( $instance['fundraiser'] ) ? $instance['fundraiser'] : 'auto',
            'fundraisers' => $fundraiser_model->get_fundraisers(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-fundraiser-total-raised' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['hide_if_zero']  = $new_instance['hide_if_zero'];
        $instance['before_amount'] = $new_instance['before_amount'];
        $instance['after_amount']  = $new_instance['after_amount'];
        $instance['fundraiser'] = ! empty( $new_instance['fundraiser'] ) ? esc_attr( $new_instance['fundraiser'] ) : 'auto';

        return $instance;
    }
}