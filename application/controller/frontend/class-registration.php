<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Controller\Base;
use PeerRaiser\Helper\File;
use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Fundraiser;
use PeerRaiser\Model\Participant;
use PeerRaiser\Model\Team;

class Registration extends Base {

	public function register_actions() {
		add_action( 'template_redirect',              array( $this, 'registration_redirect' ) );
		add_action( 'cmb2_init',                      array( $this, 'register_fields') );
		add_action( 'peerraiser_register_individual', array( $this, 'register_individual' ) );
		add_action( 'peerraiser_register_team',       array( $this, 'register_team' ) );
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
				'attributes'   => array( 'classes' => 'peerraiser-form' ),
			) );

			foreach ( $fields[$key] as $field ) {
				$metabox->add_field( $field );
			}
		}
	}

	public function register_individual() {
		$registration_model = new \PeerRaiser\Model\Frontend\Registration();
		$fundraiser         = new Fundraiser();
		$participant_model  = new Participant();
		$participant        = $participant_model->get_current_participant();

		// If no form submission, bail
		if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) {
			return false;
		}

		// Get CMB2 metabox object
		$cmb = cmb2_get_metabox( 'peerraiser-individual', 'fundraiser' );

		// Check security nonce
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'security_fail', __( 'Security check failed.' ) ) );
		}

		$required_fields = $registration_model->get_required_field_ids( 'individual' );
		$errors = array();

		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[$field] ) ) {
				$errors[] = $field;
			}
		}

		// TODO: Check if file type and size is allowed
		// TODO: Check if goal amount is correct format
		// TODO: Check if campaign accepting fundraisers

		if ( ! empty( $errors ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'Some required fields are empty.' ) ) );
		}

		/**
		 * Fetch sanitized values
		 */
		$sanitized_values = $cmb->get_sanitized_values( $_POST );

		$fundraiser->fundraiser_name    = $sanitized_values['_peerraiser_headline'];
		$fundraiser->fundraiser_slug    = sanitize_title( $participant->full_name );
		$fundraiser->fundraiser_content = $sanitized_values['_peerraiser_body'];
		$fundraiser->campaign_id        = absint( $_POST['_peerraiser_fundraiser_campaign'] );
		$fundraiser->participant        = $participant->ID;

		// Unset data that shouldn't be saved as post meta
		unset( $sanitized_values['_peerraiser_headline'] );
		unset( $sanitized_values['_peerraiser_body'] );
		unset( $sanitized_values['peerraiser_action'] );

		$fundraiser->save();

		foreach ( $sanitized_values as $key => $value ) {
			$fundraiser->update_meta( $key, $value );
		}

		// Try to upload the featured image
		$image_id = File::attach_image_to_post( $fundraiser->ID );

		// If image upload was successful, set the featured image
		if ( $image_id && ! is_wp_error( $image_id ) ) {
			set_post_thumbnail( $fundraiser->ID, $image_id );
		}

		// Redirect to the new fundraiser
		wp_safe_redirect( get_permalink( $fundraiser->ID ) );
		exit;
	}

	public function register_team() {
		$plugin_options     = get_option( 'peerraiser_options', array() );
		$team               = new Team();
		$registration_model = new \PeerRaiser\Model\Frontend\Registration();
		$campaign           = new Campaign( $_POST['_peerraiser_fundraiser_campaign'] );
		$participant_model  = new Participant();
		$participant        = $participant_model->get_current_participant();

		// If no form submission, bail
		if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) {
			return false;
		}

		// Get CMB2 metabox object
		$cmb = cmb2_get_metabox( 'peerraiser-start-team', 'fundraiser' );

		// Check security nonce
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'security_fail', __( 'Security check failed.' ) ) );
		}

		$required_fields = $registration_model->get_required_field_ids( 'start-team' );
		$errors = array();

		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[$field] ) ) {
				$errors[] = $field;
			}
		}

		if ( ! empty( $errors ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'Some required fields are empty.' ) ) );
		}

		/**
		 * Fetch sanitized values
		 */
		$sanitized_values = $cmb->get_sanitized_values( $_POST );

		$team->team_name     = $sanitized_values['_peerraiser_team_name'];
		$team->team_headline = $sanitized_values['_peerraiser_headline_team'];
		$team->team_content  = $sanitized_values['_peerraiser_body_team'];
		$team->campaign_id   = absint( $_POST['_peerraiser_fundraiser_campaign'] );
		$team->team_goal     = $sanitized_values['_peerraiser_team_goal'];
		$team->team_leader   = $participant->ID;

		$image_id = File::attach_image_to_post();

		$team->thumbnail_image = wp_get_attachment_url( $image_id );

		$team->save();

		// TODO: If participant already has a fundraising page for this campaign, add it to the new team and redirect to team page

		// Redirect to register for the new team
		$url = trailingslashit( get_permalink( $plugin_options[ 'registration_page' ] ) ) . $campaign->campaign_slug . '/individual';
		wp_safe_redirect( add_query_arg( 'team', $team->team_slug, $url) );
		exit;
	}
}