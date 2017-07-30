<?php

namespace PeerRaiser\Model\Frontend\Widget;

use PeerRaiser\Model\Campaign;

class Campaign_Register_Button_Widget extends PeerRaiser_Widget {

    public function __construct() {
        $widget_title = apply_filters( 'peerraiser-campaign-register-button-widget-title', __( 'Campaign Register Button', 'peerraiser' ) );
        $widget_options = apply_filters( 'peerraiser-campaign-register-button-widget-options', array(
            'classname' => 'peerraiser-campaign-register-button',
            'description' => __( 'Renders a registration button for a campaign', 'peerraiser' ),
        ) );

        parent::__construct('peerraiser_campaign_register_button', $widget_title, $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( $instance['campaign'] === 'auto' || empty( $instance['campaign'] ) ) {
            $campaign = peerraiser_get_current_campaign();
        } else {
            $campaign = new Campaign( $instance['campaign']);
        }

        $plugin_options = get_option( 'peerraiser_options', array() );

        $view_args = array(
            'registration_page' => get_permalink( $plugin_options[ 'registration_page' ] ),
            'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : wp_kses_post( 'Register Now', 'peerraiser' ),
        );
        $this->assign( 'peerraiser', $view_args );
        $this->assign( 'campaign', $campaign );
        $this->assign( 'args', $args );

        echo $this->get_text_view( 'frontend/widget/peerraiser-campaign-register-button' );
    }

    public function form( $instance ) {
        $campaign_model = new Campaign();

        $view_args = array(
            'button_label' => ! empty( $instance['button_label'] ) ? $instance['button_label'] : esc_attr__( 'Register Now', 'peerraiser' ),
            'campaign' => ! empty( $instance['campaign'] ) ? $instance['campaign'] : 'auto',
            'campaigns' => $campaign_model->get_campaigns(),
        );
        $this->assign( 'peerraiser', $view_args );

        echo $this->get_text_view( 'backend/widget/peerraiser-campaign-register-button' );
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['button_label'] = ( ! empty( $new_instance['button_label'] ) ) ? strip_tags( $new_instance['button_label'] ) : '';
        $instance['campaign'] = ! empty( $new_instance['campaign'] ) ? esc_attr( $new_instance['campaign'] ) : 'auto';

        return $instance;
    }
}