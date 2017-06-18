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

        $config->set( 'peerraiser_live_url', 'https://peerraiser.com/api/live' );

        $peerraiser_urls = array(
            // 'peerraiser_url.live'    => 'https://peerraiser.com/donate/%s/%s',
            // 'peerraiser_url.sandbox' => 'https://peerraiser.com/sandbox/%s/%s'
            'peerraiser_url.live'    => 'http://peerraiser.dev/donate/%s/%s',
            'peerraiser_url.sandbox' => 'http://peerraiser.dev/sandbox/%s/%s'
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

        wp_cache_set( 'config', $config, 'peerraiser' );

        return $config;

    }

}