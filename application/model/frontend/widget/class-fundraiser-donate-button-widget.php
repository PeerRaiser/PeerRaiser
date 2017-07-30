<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Fundraiser;

class Fundraiser_Donate_Button_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-fundraiser-donate-button-widget-title', __( 'Fundraiser Donate Button', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-fundraiser-donate-button-widget-options', array(
            'classname' => 'peerraiser-fundraiser-donate-button',
            'description' => __( 'Renders a donation button for fundraisers', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_fundraiser_donate_button', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['fundraiser'] === 'auto'|| empty( $instance['fundraiser'] ) ) {
            $fundraiser = peerraiser_get_current_fundraiser();
        } else {
            $fundraiser = new Fundraiser( $instance['fundraiser']);
        }

        $plugin_options = get_option( 'peerraiser_options', array() );

        $view_args = array(
            'donation_page' => get_permalink( $plugin_options[ 'donation_page' ] ),
            'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : wp_kses_post( 'Donate to this fundraiser', 'peerraiser' ),
        );
        $this->assign( 'peerraiser', $view_args );
        $this->assign( 'fundraiser', $fundraiser );
        $this->assign( 'donate_url', $this->get_donate_url( $fundraiser ) );
        $this->assign( 'args', $args );

        echo $this->get_text_view( 'frontend/widget/peerraiser-fundraiser-donate-button' );
    }

    public function form( $instance ) {
        $fundraiser_model = new Fundraiser();

        $view_args = array(
            'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : esc_attr__( 'Donate to this fundraiser', 'peerraiser' ),
            'fundraiser' => ! empty( $instance['fundraiser'] ) ? $instance['fundraiser'] : 'auto',
            'fundraisers' => $fundraiser_model->get_fundraisers(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-fundraiser-donate-button' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['button_label'] = ( ! empty( $new_instance['button_label'] ) ) ? strip_tags( $new_instance['button_label'] ) : '';
        $instance['fundraiser'] = ! empty( $new_instance['fundraiser'] ) ? esc_attr( $new_instance['fundraiser'] ) : 'auto';

        return $instance;
    }

    private function get_donate_url( $fundraiser) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $donation_page  = get_permalink( $plugin_options['donation_page'] );
        $campaign = new Campaign( $fundraiser->campaign_id );

        return trailingslashit( $donation_page ) . $campaign->campaign_slug . '/' . $fundraiser->fundraiser_slug;
    }
}