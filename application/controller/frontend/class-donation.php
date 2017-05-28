<?php

namespace PeerRaiser\Controller\Frontend;

class Donation extends \PeerRaiser\Controller\Base {

	public function register_actions() {
		add_action( 'peerraiser_add_pending_donation', array( $this, 'handle_add_pending_donation' ) );
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

		$validation = $this->is_valid_donation();
		if ( ! $validation['is_valid'] ) {
			return;
		}

		// TODO: Check if donor exists first before creating a new one

		$donor    = new \PeerRaiser\Model\Donor();
		$donation = new \PeerRaiser\Model\Donation();

		// Donor fields
		$donor->first_name = trim( esc_attr( $_POST['first_name'] ) );

		if ( ! empty( $_POST['last_name'] ) ) {
			$donor->last_name = trim( esc_attr( $_POST['last_name'] ) );
		}

		if ( ! empty( $_POST['public_name'] ) ) {
			$donor->public_name = trim( esc_attr( $_POST['public_name'] ) );
		}

		$donor->email_address = trim( esc_attr( $_POST['email_address'] ) );

		$donor->save();

		$donation_amount  = empty( $_POST['other_amount'] ) ? $_POST['donation_amount'] : $_POST['other_amount'];

		// Donation Fields
		$donation->donor_id      = $donor->ID;
		$donation->total         = $donation_amount;
		$donation->subtotal      = $donation_amount;
		$donation->campaign_id   = 44; // TODO: Fix this
		$donation->status        = 'pending';
		$donation->donation_type = 'cc';
		$donation->is_anonymous  = ( isset( $_POST['is_anonymous'] ) && $_POST['is_anonymous'] === 'true' );

		$donation->save();

		// Send to PeerRaiser.com to finish the transaction
		wp_redirect( 'http://peerraiser.com/donate/'.$donation->transaction_id );
		exit;
	}

	/**
	 * Checks if the fields are valid
	 *
	 * @since     1.0.0
	 * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
	 */
	private function is_valid_donation() {
		$required_fields = apply_filters( 'peerraiser_donation_required_fields', array('first_name', 'email_address') );

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