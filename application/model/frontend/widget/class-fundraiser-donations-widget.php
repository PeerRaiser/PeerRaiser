<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Donation;
use PeerRaiser\Model\Fundraiser;

class Fundraiser_Donations_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-fundraiser-donations-widget-title', __( 'Fundraiser Donations', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-fundraiser-donations-widget-options', array(
            'classname' => 'peerraiser-fundraiser-donations',
            'description' => __( 'Display a list of donations to a fundraiser', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_fundraiser_donations', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        $donation_model = new Donation();

        if ( $instance['fundraiser'] === 'auto' || empty( $instance['fundraiser'] ) ) {
            $fundraiser = peerraiser_get_current_fundraiser();
            $donations = $donation_model->get_donations( array(
                'fundraiser_id' => $fundraiser->ID,
                'number' => $instance['list_size'],
                'is_test' => false
            ) );
        } else {
            $fundraiser = new Fundraiser( $instance['fundraiser']);
            $donations = $donation_model->get_donations( array(
                'fundraiser_id' => $fundraiser->ID,
                'number' => $instance['list_size'],
                'is_test' => false
            ) );
        }

        $this->assign( 'args', $args );
        $this->assign( 'donations', $donations );
        $this->assign( 'instance', $instance );

        echo $this->get_text_view( 'frontend/widget/peerraiser-fundraiser-donations' );
    }

    public function form( $instance ) {
        $fundraiser_model = new Fundraiser();

        $view_args = array(
            'title' => ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent Donations', 'peerraiser' ),
            'list_size' => ! empty( $instance['list_size'] ) ? $instance['list_size'] : 10,
            'fundraiser' => ! empty( $instance['fundraiser'] ) ? $instance['fundraiser'] : 'auto',
            'fundraisers' => $fundraiser_model->get_fundraisers(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-fundraiser-donations' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : '';
        $instance['list_size'] = ! empty( $new_instance['list_size'] ) ? intval( $new_instance['list_size'] ) : 10;
        $instance['fundraiser'] = ! empty( $new_instance['fundraiser'] ) ? esc_attr( $new_instance['fundraiser'] ) : 'auto';

        return $instance;
    }
}