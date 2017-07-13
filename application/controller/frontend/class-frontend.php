<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Controller\Base;

class Frontend extends Base {

	public function register_actions() {
		add_action( 'init',              array( $this, 'add_rewrite_rules' ) );
		add_action( 'after_setup_theme', array( $this, 'register_image_sizes' ) );

		add_filter( 'query_vars',        array( $this, 'register_query_vars' ) );
		add_filter( 'cmb2_wrap_classes', array( $this, 'add_form_class' ), 10, 2 );
	}

	public function add_rewrite_rules() {
		$plugin_options    = get_option( 'peerraiser_options', array() );
		$donate_page_url   = get_page_uri( $plugin_options['donation_page'] );
		$register_page_url = get_page_uri( $plugin_options['registration_page'] );

		add_rewrite_rule( '^' . $donate_page_url . '/([^/]*)/?$', 'index.php?pagename=' . urlencode( $donate_page_url ) . '&peerraiser_campaign=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $donate_page_url . '/([^/]*)/([^/]*)/?$', 'index.php?pagename=' . urlencode( $donate_page_url ) . '&peerraiser_campaign=$matches[1]&peerraiser_fundraiser=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $register_page_url . '/([^/]*)/?$', 'index.php?pagename=' . urlencode( $register_page_url ) . '&peerraiser_campaign=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $register_page_url . '/([^/]*)/([^/]*)/?$', 'index.php?pagename=' . urlencode( $register_page_url ) . '&peerraiser_campaign=$matches[1]&peerraiser_registration_choice=$matches[2]', 'top' );
	}

	public function register_query_vars( $vars ) {
		$vars[] = 'peerraiser_campaign';
		$vars[] = 'peerraiser_fundraiser';
		$vars[] = 'peerraiser_registration_choice';

		return $vars;
	}

	public function add_form_class( $classes, $box ) {

		foreach ( $box as $key => $value ) {
			if ( isset( $box->meta_box['attributes'] ) && isset( $box->meta_box['attributes']['classes'] ) ) {
				if ( ! empty( $box->meta_box['attributes']['classes'] ) ) {
					$classes[] = $box->meta_box['attributes']['classes'];
				}
			}
		}

		return array_unique( $classes );
	}

	public function register_image_sizes() {
		add_image_size( 'peerraiser_thumbnail', 150, 150, true );
	}
}