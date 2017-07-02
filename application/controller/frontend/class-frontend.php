<?php

namespace PeerRaiser\Controller\Frontend;

class Frontend extends \PeerRaiser\Controller\Base {

	public function register_actions() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );

		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
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
}