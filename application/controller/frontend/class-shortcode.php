<?php

namespace PeerRaiser\Controller\Frontend;

class Shortcode extends \PeerRaiser\Controller\Base {

    /**
     * @see PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_shortcode_receipt' => array(
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'render_donation_receipt' ),
            ),
            'peerraiser_shortcode_login' => array(
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'render_login_form' ),
            ),
            'peerraiser_shortcode_signup' => array(
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'render_signup_form' ),
            ),
            'peerraiser_shortcode_dashboard' => array(
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'render_participant_dashboard' ),
            ),
        );
    }


    public function render_donation_receipt( \PeerRaiser\Core\Event $event ) {
        list( $atts ) = $event->get_arguments() + array( array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'target_post_id'    => '',
            'target_post_title' => '',
            'heading_text'      => '',
            'description_text'  => '',
            'content_type'      => '',
            'teaser_image_path' => '',
        ), $atts );

        $html = '<p><strong>Testing</strong> 123</p>';

        $event->set_result( $html );

    }

}