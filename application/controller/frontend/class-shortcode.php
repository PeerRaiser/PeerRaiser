<?php

namespace PeerRaiser\Controller\Frontend;

class Shortcode extends \PeerRaiser\Controller\Base {

    /**
     * @see PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_shortcode_receipt' => array(
                array( 'render_donation_receipt' ),
            ),
            'peerraiser_shortcode_login' => array(
                array( 'render_login_form' ),
            ),
            'peerraiser_shortcode_signup' => array(
                array( 'render_signup_form' ),
            ),
            'peerraiser_shortcode_dashboard' => array(
                array( 'render_participant_dashboard' ),
            ),
            'peerraiser_cmb2_init' => array(
                array( 'register_settings_fields' )
            )
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

        // Plugin options
        $plugin_options = get_option( 'peerraiser_options', array() );

        // Create event so navigation can be added externally
        $new_event = new \PeerRaiser\Core\Event();
        $new_event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_render_participant_dashboard', $new_event );
        $results = (array) $new_event->get_result();

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        // Models
        $dashboard_model = \PeerRaiser\Model\Frontend\Dashboard::get_instance();
        $currency_model  = new \PeerRaiser\Model\Currency();

        // Merge default navigation with any additional navigation
        $default_navigation    = $dashboard_model->get_navigation();
        $additional_navigation = ( isset( $results['navigation'] ) ) ? $results[ 'navigation' ] : array();
        $navigation_links      = array_merge( $default_navigation, $additional_navigation );

        $view_args = array(
            'navigation'                 => $navigation_links,
            'donations'                  => $dashboard_model->get_donations(),
            'fundraisers'                => $dashboard_model->get_fundraisers(),
            'teams'                      => $dashboard_model->get_teams(),
            'user_id'                    => get_current_user_id(),
            'default_campaign_thumbnail' => $plugin_options['campaign_thumbnail_image'],
            'default_team_thumbnail'     => $plugin_options['team_thumbnail_image'],
            'currency_symbol'            => $currency_model->get_currency_symbol_by_iso4217_code( $plugin_options['currency'] ),
            'settings_form'              => cmb2_get_metabox_form( 'dashboard_settings', get_current_user_id() ),
            'profile_photo'              => \PeerRaiser\Helper\View::get_avatar(),
            'password_form'              => $this->get_password_form(),
        );
        $this->assign( 'peerraiser', $view_args );

        $page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 'profile';

        $event->set_result( $this->get_text_view( 'frontend/participant-dashboard-' . $page ) );

    }


    public function register_settings_fields( \PeerRaiser\Core\Event $event ) {

        $dashboard_model = \PeerRaiser\Model\Frontend\Dashboard::get_instance();

        $default_fields    = $dashboard_model->get_fields();
        $additional_fields = $this->get_additional_fields();
        $fields            = array_merge( $default_fields, $additional_fields );

        // Fields that come with WordPress by default
        $wordpress_fields = array( 'user_pass', 'user_login', 'user_nicename', 'user_url', 'user_email', 'display_name', 'nickname', 'first_name', 'last_name', 'description', 'rich_editing', 'user_registered', 'role', 'jabber', 'aim', 'yim', 'show_admin_bar_front' );

        $cmb = new_cmb2_box( array(
            'id'               => 'dashboard_settings',
            'object_types'     => array( 'user' ),
            'new_user_section' => 'add-existing-user'
        ) );

        foreach ( $fields as $field ) {
            if ( is_admin() && in_array($field['id'], $wordpress_fields) )
                continue;

            $cmb->add_field( $field );
        }

    }


    private function get_additional_fields() {
        $event = new \PeerRaiser\Core\Event();
        $event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_participant_dashboard_fields', $event );
        return (array) $event->get_result();
    }


    private function get_password_form() {
        return $this->get_text_view( 'frontend\partials\change-password-form' );
    }

}