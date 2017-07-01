<?php

namespace PeerRaiser\Controller\Frontend;

class Donation extends \PeerRaiser\Controller\Base {

	public function register_actions() {
		add_action( 'init',                            array( $this, 'add_rewrite_rules' ) );
		add_action( 'peerraiser_add_pending_donation', array( $this, 'handle_add_pending_donation' ) );

		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
	}

	public function add_rewrite_rules() {
		add_rewrite_rule( '^donate/([^/]*)/?$', 'index.php?pagename=donate&peerraiser_campaign=$matches[1]', 'top' );
		add_rewrite_rule( '^donate/([^/]*)/([^/]*)/?$', 'index.php?pagename=donate&peerraiser_campaign=$matches[1]&peerraiser_fundraiser=$matches[2]', 'top' );
	}

	public function register_query_vars( $vars ) {
		$vars[] = 'peerraiser_campaign';
		$vars[] = 'peerraiser_fundraiser';
		return $vars;
	}

	/**
	 * Create a donor record and pending donation before sending the donor to PeerRaiser to complete the transaction
	 *
	 * @since 1.0.0
	 */
	public function handle_add_pending_donation() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'add_pending_donation' ) ) {
			die( __('Security check failed.', 'peerraiser' ) );
		}

		$plugin_options = get_option( 'peerraiser_options', array() );

		$validation = $this->is_valid_donation();
		if ( ! $validation['is_valid'] ) {
			return;
		}

		$donor    = new \PeerRaiser\Model\Donor( trim( $_POST['email_address'] ) );

		// Donor fields
		$donor->first_name = trim( esc_attr( $_POST['first_name'] ) );

		if ( ! empty( $_POST['last_name'] ) ) {
			$donor->last_name = trim( esc_attr( $_POST['last_name'] ) );
		}

		$donor->email_address = trim( esc_attr( $_POST['email_address'] ) );

		$donor->save();

		$donation = new \PeerRaiser\Model\Donation();

		$donation_amount = empty( $_POST['other_amount'] ) ? $_POST['donation_amount'] : $_POST['other_amount'];

		// Donation Fields
		$donation->donor_id      = $donor->ID;
		$donation->total         = $donation_amount;
		$donation->subtotal      = $donation_amount;
		$donation->campaign_id   = peerraiser_get_campaign_by_slug( $_POST['campaign'] )->ID;
		$donation->status        = 'pending';
		$donation->donation_type = 'cc';
		$donation->is_anonymous  = ( isset( $_POST['is_anonymous'] ) && $_POST['is_anonymous'] === 'true' );

		if ( isset( $_POST['fundraiser'] ) ) {
			$donation->fundraiser_id = peerraiser_get_fundraiser_by_slug( $_POST['fundraiser'] )->ID;
		}

		$donation->add_note( __( 'Donation started and is currently pending.', 'peerraiser' ), __( 'PeerRaiser Bot', 'peerraiser' ) );

		$donation->save();

		if ( filter_var( $plugin_options['test_mode'], FILTER_VALIDATE_BOOLEAN ) ) {
			$redirect_url = sprintf( \PeerRaiser\Core\Setup::get_plugin_config()->get('peerraiser_url.sandbox'), urlencode( trim( $plugin_options['peerraiser_username'] ) ), $donation->transaction_id );
		} else {
			$redirect_url = sprintf(\PeerRaiser\Core\Setup::get_plugin_config()->get('peerraiser_url.live'), urlencode( trim( $plugin_options['peerraiser_username'] ) ), $donation->transaction_id );
		}

		// Send to PeerRaiser.com to finish the transaction
		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Checks if the fields are valid
	 *
	 * @since     1.0.0
	 * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
	 */
	private function is_valid_donation() {
		$required_fields = apply_filters( 'peerraiser_donation_required_fields', array('first_name', 'last_name', 'email_address', 'campaign' ) );

		if ( empty( $_POST['donation_amount'] ) ) {
			$required_fields[] = 'other_amount';
		}

		$data = array(
			'is_valid'     => true,
			'field_errors' => array(),
		);

		foreach ( $required_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) || empty( $_POST[ $field ] ) ) {
				$data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
			}
		}

		if ( ! is_email( $_POST['email_address'] ) ) {
			$data['field_errors'][ 'email_address' ] = __( 'A valid email address is required', 'peerraiser' );
		}

		if ( ! empty( $data['field_errors'] ) ) {
			$message = __( 'There was a problem with one or more fields. Please fix them and try again.', 'peerraiser' );
			\PeerRaiser\Model\Admin\Admin_Notices::add_notice( $message, 'notice-error', true );

			wp_localize_script(
				'jquery',
				'peerraiser_field_errors',
				$data['field_errors']
			);

			$data['is_valid'] = false;
		}

		return $data;
	}
}