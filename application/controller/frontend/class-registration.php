<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Controller\Base;

class Registration extends Base {

	public function register_actions() {
		add_action( 'template_redirect', array( $this, 'registration_redirect' ) );
		add_action( 'cmb2_init',         array( $this, 'register_fields') );
	}

	public function registration_redirect() {
		global $wp_query;
		$post_id = $wp_query->get_queried_object_id();

		// Get the default dashboard and login page urls
		$plugin_options    = get_option( 'peerraiser_options', array() );
		$registration_page = $plugin_options[ 'registration_page' ];
		$login_page_url    = get_permalink( $plugin_options[ 'login_page' ] );

		// If this is the registration page and the user isn't logged in, redirect to the login page
		if ( $post_id == $registration_page && ! is_user_logged_in() ) {
			$args = array(
				'next_url' => get_permalink( $registration_page )
			);

			wp_safe_redirect( add_query_arg( $args, $login_page_url ) );
			exit;
		}
	}

	public function register_fields() {
		$registration_model = new \PeerRaiser\Model\Frontend\Registration();
		$fields = $registration_model->get_fields();

		foreach ( $fields as $key => $value ) {
			$metabox = new_cmb2_box( array(
				'id'           => 'peerraiser-'.$key,
				'object_types' => array( 'fundraiser' ),
				'hookup'       => false,
				'save_fields'  => false,
			) );

			foreach ( $fields[$key] as $field ) {
				$metabox->add_field( $field );
			}
		}
	}
}