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
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $html = '<p><strong>Testing</strong> 123</p>';

        $event->set_result( $html );

    }


    public function render_login_form( \PeerRaiser\Core\Event $event ) {
        list( $atts ) = $event->get_arguments() + array( array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $view_args = array(
            'test' => 'test result'
        );
        $this->assign( 'peerraiser', $view_args );

        $event->set_result( $this->get_text_view( 'frontend/partials/login-form' ) );

    }


    public function render_signup_form( \PeerRaiser\Core\Event $event ) {
        list( $atts ) = $event->get_arguments() + array( array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $view_args = array(
            'test' => 'test result'
        );
        $this->assign( 'peerraiser', $view_args );

        $event->set_result( $this->get_text_view( 'frontend/partials/signup-form' ) );

    }


    public function render_participant_dashboard( \PeerRaiser\Core\Event $event ) {
        list( $atts ) = $event->get_arguments() + array( array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        // Get the default dashboard and login page urls
        $plugin_options        = get_option( 'peerraiser_options', array() );
        $participant_dashboard = get_permalink( $plugin_options[ 'participant_dashboard' ] );
        $login_page            = get_permalink( $plugin_options[ 'login_page' ] );

        // If the user isn't logged in, redirect to the login page
        if ( !is_user_logged_in() ) {
            $args = array(
                'next_url' => $participant_dashboard
            );

            wp_safe_redirect( add_query_arg( $args, $login_page ) );
            exit;
        }

        // Create event so navigation can be added externally
        $new_event = new \PeerRaiser\Core\Event();
        $new_event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_dashboard_navigation', $new_event );
        $navigation = (array) $new_event->get_result();

        // Get default navigation
        $model = \PeerRaiser\Model\Frontend\Dashboard::get_instance();
        $default_navigation = $model->get_navigation();

        // Merge default navigation with any new navigation
        $navigation_links = array_merge( $default_navigation, $navigation );

        $view_args = array(
            'navigation'                 => $navigation_links,
            'donations'                  => $model->get_donations(),
            'fundraisers'                => $model->get_fundraisers(),
            'teams'                      => $model->get_teams(),
            'user_id'                    => get_current_user_id(),
            'default_campaign_thumbnail' => $plugin_options['campaign_thumbnail_image'],
            'default_team_thumbnail'     => $plugin_options['team_thumbnail_image']
        );
        $this->assign( 'peerraiser', $view_args );

        $page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 'profile';

        $event->set_result( $this->get_text_view( 'frontend/participant-dashboard-' . $page ) );

    }

}