<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Fundraiser;

class Fundraiser_Thermometer_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-fundraiser-thermometer-widget-title', __( 'Fundraiser Thermometer', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-fundraiser-thermometer-widget-options', array(
            'classname' => 'peerraiser-fundraiser-thermometer',
            'description' => __( 'Display a thermometer that shows the progress of the fundraiser goal', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_fundraiser_thermometer', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['fundraiser'] === 'auto' || empty( $instance['fundraiser'] ) ) {
            $fundraiser = peerraiser_get_current_fundraiser();
        } else {
            $fundraiser = new Fundraiser( $instance['fundraiser']);
        }

        $this->assign( 'goal_percentage', round( ( $fundraiser->donation_value / $fundraiser->fundraiser_goal) * 100 ) );
        $this->assign( 'fundraiser', $fundraiser );
        $this->assign( 'args', $args );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-fundraiser-thermometer' );
    }

    public function form( $instance ) {
        $fundraiser_model = new Fundraiser();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : '',
            'fundraiser' => ! empty( $instance['fundraiser'] ) ? $instance['fundraiser'] : 'auto',
            'fundraisers' => $fundraiser_model->get_fundraisers(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-fundraiser-thermometer' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['fundraiser'] = ! empty( $new_instance['fundraiser'] ) ? esc_attr( $new_instance['fundraiser'] ) : 'auto';

        return $instance;
    }
}