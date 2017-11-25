<?php

namespace PeerRaiser\Core;

class Setup {

    public function __construct(  ) {
    }

    public static function get_plugin_config() {

        $config = wp_cache_get( 'config', 'peerraiser' );
        if ( is_a( $config, '\PeerRaiser\Model\Config' ) ) {
            return $config;
        }

        $config = new \PeerRaiser\Model\Config();

        $config->set( 'plugin_dir_path', plugin_dir_path( PEERRAISER_FILE ) );
        $config->set( 'plugin_file_path', PEERRAISER_FILE );
        $config->set( 'plugin_base_name', plugin_basename( PEERRAISER_FILE ) );
        $config->set( 'plugin_url', plugins_url( '/', PEERRAISER_FILE ) );
        $config->set( 'view_dir', plugin_dir_path( PEERRAISER_FILE ) . 'views/' );

        $peerraiser_urls = array(
            'peerraiser_url.live'    => 'https://peerraiser.com/donate/%s/%s',
            'peerraiser_url.sandbox' => 'https://peerraiser.com/sandbox/%s/%s',
        );
        $config->import( $peerraiser_urls );

        $upload_directory = wp_upload_dir();
        $config->set( 'log_directory', $upload_directory['basedir'] . '/peerraiser_logs/' );
        $config->set( 'log_url', $upload_directory['baseurl'] . '/peerraiser_logs/');

        $plugin_url = $config->get( 'plugin_url' );
        $config->set( 'css_url', $plugin_url . 'assets/css/' );
        $config->set( 'js_url', $plugin_url . 'assets/js/' );
        $config->set( 'images_url', $plugin_url . 'assets/images/' );

        $plugin_options = get_option( 'peerraiser_options', array() );
        $config->set( 'in_live_mode', isset( $plugin_options['test_mode'] ) ? $plugin_options['test_mode'] : false );

        $client_address = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
        $debug_mode_enabled = isset( $plugin_options['debugger_enabled'] ) ? $plugin_options['debugger_enabled'] : false;
        $debug_mode_addresses = isset( $plugin_options['debugger_address'] ) ? $plugin_options['debugger_address'] : '';
        $debug_mode_addresses = explode( ',', $debug_mode_addresses );
        $debug_mode_addresses = array_map( 'trim', $debug_mode_addresses );

        $config->set( 'debug_mode', $debug_mode_enabled && ! empty( $debug_mode_addresses ) && in_array( $client_address, $debug_mode_addresses ) );

        // Plugin headers
        $plugin_headers = get_file_data(
            PEERRAISER_FILE,
            array(
                'plugin_name'       => 'Plugin Name',
                'plugin_uri'        => 'Plugin URI',
                'description'       => 'Description',
                'author'            => 'Author',
                'version'           => 'Version',
                'author_uri'        => 'Author URI',
                'textdomain'        => 'Textdomain',
                'text_domain_path'  => 'Domain Path',
            )
        );
        $config->import( $plugin_headers );

        // Currency and Country defaults
        $currency_settings = array(
            'currency.default'             => 'USD',
            'currency.position'            => 'before',
            'currency.thousands_separator' => ',',
            'currency.decimal_separator'   => '.',
            'currency.number_decimals'     => 2,
            'country.default'              => 'US'
        );
        $config->import( $currency_settings );

	    $config->set( 'donation_minimum', 10 );

	    // Emails
	    $config->set( 'donation_receipt_subject', __('Thank you for your donation', 'peerraiser') );
	    $config->set( 'donation_receipt_body', __("Dear {{donor_first_name}},\r\n\r\nThank you so much for your generous donation.\r\n\r\nTransaction Summary:\r\n{{donation_summary}}\r\n\r\nWith thanks,\r\n{{site_name}}", 'peerraiser') );
	    $config->set( 'new_donation_notification_subject', __('New donation received', 'peerraiser') );
	    $config->set( 'new_donation_notification_body', __("{{donor_first_name}} has just made a donation!\r\n\r\nSummary:\r\n{{donation_summary}}", 'peerraiser') );
	    $config->set( 'welcome_email_subject', __('Welcome!', 'peerraiser') );
	    $config->set( 'welcome_email_body', __('Welcome to the {{campaign_name}} campaign!', 'peerraiser') );
	    $config->set( 'team_registration_subject', __('Thank you for creating a team!', 'peerraiser') );
	    $config->set( 'team_registration_body', __("{{first_name}},\r\n\r\nThank you for registering a team.\r\n\r\nYour team page is located at:\r\n{{team_url}}\r\n\r\nTell your friends and family to join your team at this URL:\r\n{{team_url}}\r\n\r\nSincerely,\r\n{{site_name}}", 'peerraiser') );

        wp_cache_set( 'config', $config, 'peerraiser' );

        return $config;
    }

}