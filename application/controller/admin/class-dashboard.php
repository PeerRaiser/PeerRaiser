<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Controller\Base;
use \PeerRaiser\Model\Currency;
use \PeerRaiser\Model\Activity_Feed;
use PeerRaiser\Model\Donation;
use \PeerRaiser\Model\Donor as Donor_Model;
use \PeerRaiser\Model\Donation as Donation_Model;
use \PeerRaiser\Model\Campaign as Campaign_Model;
use \PeerRaiser\Model\Fundraiser as Fundraiser_Model;
use \PeerRaiser\Helper\Stats;
use \PeerRaiser\Helper\View;

class Dashboard extends Base {

    public function register_actions() {
        add_action( 'wp_ajax_peerraiser_dismiss_message',              array( $this, 'process_dismiss_message_request' ) );
        add_action( 'wp_ajax_peerraiser_dismiss_message-post-new.php', array( $this, 'peerraiser_on_ajax_send_json' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific CSS
        wp_register_style(
            'peerraiser-admin-dashboard',
            $this->config->get( 'css_url' ) . 'peerraiser-admin-dashboard.css',
            array( 'peerraiser-font-awesome' ),
            $this->config->get( 'version' )
        );
        wp_enqueue_style( 'peerraiser-admin-dashboard' );

        // load page-specific JS
        wp_register_script(
            'peerraiser-select2',
            $this->config->get( 'js_url' ) . 'vendor/select2.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-backend-dashboard',
            $this->config->get( 'js_url' ) . 'peerraiser-backend-dashboard.js',
            array( 'jquery', 'peerraiser-select2' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'peerraiser-select2' );
        wp_enqueue_script( 'peerraiser-backend-dashboard' );

        // translations
        $i18n = array(
            'total'   => __( 'Total', 'peerraiser' ),
            'sales'   => __( 'sales', 'peerraiser' ),
            'signups' => __( 'signups', 'peerraiser' ),
            'delete'  => __( 'Delete', 'peerraiser' ),
        );

        $plugin_options = get_option( 'peerraiser_options', array() );

        wp_localize_script(
            'peerraiser-backend-dashboard',
            'pr_dashboard_variables',
            array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
                'i18n'               => $i18n
            )
        );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency         = new Currency();
        $activity_feed    = new Activity_Feed();
        $donor_model      = new Donor_Model();
        $donation_model   = new Donation_Model();
        $campaign_model   = new Campaign_Model();
        $fundraiser_model = new Fundraiser_Model();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
        $peerraiser_slug = get_option('peerraiser_slug');

        $view_args = array(
            'activity_feed'        => $activity_feed->get_activity_feed(),
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'show_welcome_message' => filter_var($plugin_options['show_welcome_message'], FILTER_VALIDATE_BOOLEAN),
            'display_name'         => $this->get_current_users_name(),
            'plugin_version'       => $plugin_options['peerraiser_version'],
            'admin_url'            => get_admin_url(),
            'donations_total'      => View::format_number( $donation_model->get_donations_total(), true, true ),
            'campaigns_total'      => View::format_number( $campaign_model->get_total_campaigns(), false, true ),
            'fundraisers_total'    => View::format_number( $fundraiser_model->get_total_fundraisers(), false, true ),
            'donors_total'         => View::format_number( $donor_model->get_total_donors(), false, true ),
            'donate_url'           => get_the_permalink( $plugin_options['donation_page'] ),
            'font_awesome_class'   => array(
                'step_1'           => ! empty( $peerraiser_slug ) ? 'fa-check-square-o' : 'fa-square-o',
                'step_2'           => ( $campaign_model->get_total_campaigns() > 0 ) ? 'fa-check-square-o' : 'fa-square-o',
                'step_3'           => $this->made_test_donation() ? 'fa-check-square-o' : 'fa-square-o',
                'step_4'           => ! filter_var($plugin_options['test_mode'], FILTER_VALIDATE_BOOLEAN) ? 'fa-check-square-o' : 'fa-square-o',
            ),
            'top_donors'           => $donor_model->get_top_donors(),
            'top_fundraisers'      => $fundraiser_model->get_top_fundraisers(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/dashboard' );
    }

    private function get_current_users_name(){
        $current_user = wp_get_current_user();
        return ( isset($current_user->user_firstname) && !empty($current_user->user_firstname) ) ? $current_user->user_firstname : $current_user->display_name;
    }

    public function process_dismiss_message_request() {
        $result = array(
            'success' => false,
            'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
        );

        // Attempt to verify the nonce, and exit early if it fails
        if ( !wp_verify_nonce( $_POST['nonce'], 'dismiss_'.$_POST['message_type'] ) ) {
            $result = array(
                'success' => false,
                'message' => __( 'Verify nonce failed.', 'peerraiser' ),
            );
            return $result;
        }

        // Set "show welcome message" option to false
        $plugin_options = get_option( 'peerraiser_options', array() );
        $plugin_options['show_welcome_message'] = false;
        update_option( 'peerraiser_options', $plugin_options );

        return array(
            'success' => true,
            'message' => __( 'Message has been dismissed', 'peerraiser' ),
        );
    }

    private function made_test_donation() {
        $donation_model = new Donation();

        $test_donations = $donation_model->get_donations( array(
            'is_test' =>  1,
            'status'  => 'completed',
            'number'  => 1,
        ) );

        return count( $test_donations ) > 0;
    }
}
