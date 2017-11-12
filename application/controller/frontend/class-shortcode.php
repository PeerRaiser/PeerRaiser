<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Frontend\Registration;
use PeerRaiser\Model\Fundraiser;
use PeerRaiser\Model\Team;

class Shortcode extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_shortcode( 'peerraiser_donation_form',         array( $this, 'render_donation_form' ) );
        add_shortcode( 'peerraiser_receipt',               array( $this, 'render_donation_receipt' ) );
        add_shortcode( 'peerraiser_login',                 array( $this, 'render_login_form' ) );
        add_shortcode( 'peerraiser_signup',                array( $this, 'render_signup_form' ) );
        add_shortcode( 'peerraiser_participant_dashboard', array( $this, 'render_participant_dashboard' ) );
        add_shortcode( 'peerraiser_registration',          array( $this, 'render_registration_form' ) );

        add_action( 'cmb2_init', array( $this, 'register_settings_fields' ) );
    }

    public function render_donation_form( $atts, $content = '' ) {
        $atts = shortcode_atts( array(), $atts, 'peerraiser_donation_form' );
        $plugin_options = get_option( 'peerraiser_options', array() );

        // Donations can't be processed if the PeerRaiser account isn't setup :(
        if ( empty( get_option('peerraiser_slug') ) ) {
            return sprintf( __( 'Error: PeerRaiser account not connected. Please <a href="mailto:%s">contact the site owner</a>.', 'peerraiser'), get_option( 'admin_email' ) );
        }

        $view_args = array(
            'campaigns' => $this->get_campaigns_accepting_donations(),
            'fundraisers' => $this->get_fundraisers_for_current_campaign(),
            'currency_symbol' => peerraiser_get_currency_symbol(),
            'currency_position' => $plugin_options['currency_position'],
            'donation_minimum' => isset( $plugin_options['donation_minimum'] ) ? $plugin_options['donation_minimum'] : 10,
            'campaign_select_class' => $this->get_campaign_select_class(),
            'fundraiser_select_class' => $this->get_fundraiser_select_class(),
        );
        $this->assign( 'peerraiser', $view_args );

        return $this->get_text_view( 'frontend/donation-form' );
    }

    public function render_donation_receipt( $atts, $content = '' ) {
        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $view_args = array(
            'test' => 'test result'
        );
        $this->assign( 'peerraiser', $view_args );

        return $this->get_text_view( 'frontend/donation-receipt' );
    }

    public function render_login_form( $atts, $content ='' ) {
        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $plugin_options = get_option( 'peerraiser_options', array() );
        $signup_page    = $plugin_options[ 'signup_page' ];

        $signup_page_url = isset( $_GET['next_url'] )
            ? add_query_arg( 'next_url', $_GET['next_url'], get_permalink( $signup_page ) )
            : get_permalink( $signup_page );

        $this->assign( 'signup_page', $signup_page_url );

        return $this->get_text_view( 'frontend/partials/login-form' );
    }

    public function render_signup_form( $atts, $content = '' ) {
        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $plugin_options = get_option( 'peerraiser_options', array() );
        $login_page     = $plugin_options[ 'login_page' ];

        $login_page_url = isset( $_GET['next_url'] )
            ? add_query_arg( 'next_url', $_GET['next_url'], get_permalink( $login_page ) )
            : get_permalink( $login_page );

        $this->assign( 'login_page', $login_page_url );

        return $this->get_text_view( 'frontend/partials/signup-form' );
    }

    public function render_participant_dashboard( $atts, $content = '' ) {
        // Plugin options
        $plugin_options = get_option( 'peerraiser_options', array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        // Models
        $dashboard_model  = new \PeerRaiser\Model\Frontend\Dashboard();
        $currency_model   = new \PeerRaiser\Model\Currency();
        $fundraiser_model = new Fundraiser();
        $team_model       = new Team();
        $current_user_id  = get_current_user_id();

        $navigation_links = apply_filters( 'peerraiser_participant_dashboard_navigation', $dashboard_model->get_navigation() );

        $view_args = array(
            'navigation'                 => $navigation_links,
            'donations'                  => $dashboard_model->get_donations(),
            'fundraisers'                => $fundraiser_model->get_fundraisers( array( 'participant' => $current_user_id ) ),
            'teams'                      => $team_model->get_teams_for_current_user(),
            'user_id'                    => $current_user_id,
            'default_campaign_thumbnail' => $plugin_options['campaign_thumbnail_image'],
            'default_team_thumbnail'     => $plugin_options['team_thumbnail_image'],
            'currency_symbol'            => $currency_model->get_currency_symbol_by_iso4217_code( $plugin_options['currency'] ),
            'settings_form'              => cmb2_get_metabox_form( 'dashboard_settings', $current_user_id ),
            'profile_photo'              => \PeerRaiser\Helper\View::get_avatar(),
            'password_form'              => $this->get_password_form(),
        );
        $this->assign( 'peerraiser', $view_args );

        $page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 'profile';

        return $this->get_text_view( 'frontend/participant-dashboard-' . $page );
    }

    public function register_settings_fields() {
        $dashboard_model = new \PeerRaiser\Model\Frontend\Dashboard();

        $default_fields    = $dashboard_model->get_fields();
        $fields            = apply_filters( 'peerraiser_participant_dashboard_fields', $default_fields );

        // Fields that come with WordPress by default
        $wordpress_fields = array( 'user_pass', 'user_login', 'user_nicename', 'user_url', 'user_email', 'display_name',
            'nickname', 'first_name', 'last_name', 'description', 'rich_editing', 'user_registered', 'role', 'jabber',
            'aim', 'yim', 'show_admin_bar_front' );

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

    public function render_registration_form( $atts, $content = '' ) {
        $plugin_options      = get_option( 'peerraiser_options', array() );
        $registration_choice = get_query_var( 'peerraiser_registration_choice', false );
        $campaign_slug       = get_query_var( 'peerraiser_campaign', false );
        $registration_model  = new Registration();
        $campaign_model      = new Campaign();
        $team_model          = new Team();

        if ( ! $campaign_slug ) {
            $this->assign( 'campaigns', $campaign_model->get_campaigns( array( 'campaign_status' => 'active' ) ) );

            return $this->get_text_view( 'frontend/partials/registration-select-campaign' );
        }

        $campaign = $campaign_model->get_campaigns( array( 'slug' => $campaign_slug ) );

        if ( empty( $campaign ) ) {
            return $this->get_text_view( 'frontend/partials/registration-invalid-campaign' );
        } else {
            $campaign = $campaign[0];
        }

        $registration_choices = $registration_model->get_registration_choices( $campaign );

        if ( $registration_choice && ! isset( $registration_choices[$registration_choice] ) ) {
            $this->assign( 'campaign', $campaign );

            return $this->get_text_view( 'frontend/partials/registration-invalid-choice' );
        }

        if ( ! $registration_choice ) {
            $this->assign( 'headline', apply_filters( 'peerraiser_registration_choice_headline', __( 'Start fundraising', 'peerraiser' ) ) );
            $this->assign( 'choices', $registration_choices );

            return $this->get_text_view( 'frontend/partials/registration-choices' );
        }

        if ( in_array( $registration_choice, array( 'individual', 'start-team' ) ) ) {
            $cmb = cmb2_get_metabox( 'peerraiser-'.$registration_choice, $registration_choice );

            $cmb->add_field( array(
                'id' => '_peerraiser_fundraiser_campaign',
                'type' => 'hidden',
                'default' => $campaign->ID,
            ) );

            $cmb->add_field( array(
                'id' => '_peerraiser_fundraiser_team',
                'type' => 'hidden',
                'default' => isset( $_GET['team'] ) ? esc_attr( $_GET['team'] ) : '',
            ) );

            $fields = cmb2_get_metabox_form( $cmb, $registration_choice, array( 'save_button' => __( 'Submit', 'peerraiser' ) ) );
            $this->assign( 'fields', $fields );
        } else {
            $this->assign( 'teams', $team_model->get_teams_by_campaign( $campaign->ID, 1000 ) );
        }

        // Get any submission errors
        if ( isset( $cmb) && ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
            $this->assign( 'errors', $error->get_error_message() );
        }

        wp_enqueue_script( 'jquery-validate');

        return $this->get_text_view( 'frontend/registration-form-' . $registration_choice );
    }

    private function get_password_form() {
        return $this->get_text_view( 'frontend/partials/change-password-form' );
    }

    private function get_campaigns_accepting_donations() {
        $campaign_model = new \PeerRaiser\Model\Campaign();
        $campaign_statuses = apply_filters( 'peerraiser_campaign_statuses_that_allow_donations', array( 'active', 'ended' ) );

        return $campaign_model->get_campaigns( array( 'campaign_status' => $campaign_statuses ) );
    }

    private function get_fundraisers_for_current_campaign() {
        $campaign_slug = get_query_var( 'peerraiser_campaign', false );

        if ( ! $campaign_slug ) {
            return array();
        }

        $campaign = peerraiser_get_campaign_by_slug( $campaign_slug );

        return $campaign->get_fundraisers();
    }

    private function get_campaign_select_class() {
        $campaign_slug = get_query_var( 'peerraiser_campaign', false );

        if ( ! $campaign_slug || apply_filters( 'peerraiser_donation_show_campaign_select', false ) ) {
            return 'show';
        } else {
            return 'hide';
        }
    }

    private function get_fundraiser_select_class() {
        $campaign_slug = get_query_var( 'peerraiser_campaign', false );
        $fundraiser_slug = get_query_var( 'peerraiser_fundraiser', false );

        if ( ( $campaign_slug && ! $fundraiser_slug ) || apply_filters( 'peerraiser_donation_show_fundraiser_select', false ) ) {
            return 'show';
        } else {
            return 'hide';
        }
    }

}