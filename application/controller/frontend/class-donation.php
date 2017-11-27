<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Helper\Email;
use PeerRaiser\Model\Donor;

class Donation extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'peerraiser_add_pending_donation', array( $this, 'handle_add_pending_donation' ) );
        add_action( 'peerraiser_donation_completed', array( $this, 'send_donation_receipt_email' ) );
        add_action( 'peerraiser_donation_completed', array( $this, 'send_donation_notification_email' ) );
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

        $donor = new \PeerRaiser\Model\Donor( trim( $_POST['email_address'] ) );

        // Donor fields
        $donor->first_name = trim( esc_attr( $_POST['first_name'] ) );

        if ( ! empty( $_POST['last_name'] ) ) {
            $donor->last_name = trim( esc_attr( $_POST['last_name'] ) );
        }

        $donor->email_address = trim( esc_attr( $_POST['email_address'] ) );

        $donor->save();

        $donation = new \PeerRaiser\Model\Donation();

        $donation_amount = empty( $_POST['other_amount'] ) ? $_POST['donation_amount'] : $_POST['other_amount'];

        if ( isset( $_POST['is_anonymous'] ) && $_POST['is_anonymous'] === 'true' ) {
            $is_anonymous = true;
            $donor_name = '';
        } else {
            $is_anonymous = false;
            $donor_name = empty( $_POST['public_name'] ) ? $donor->full_name : trim( esc_attr( $_POST['public_name'] ) );
        }

        // Donation Fields
        $donation->donor_id      = $donor->ID;
        $donation->donor_name    = $donor_name;
        $donation->total         = $donation_amount;
        $donation->subtotal      = $donation_amount;
        $donation->campaign_id   = peerraiser_get_campaign_by_slug( $_POST['campaign'] )->ID;
        $donation->status        = 'pending';
        $donation->donation_type = 'cc';
        $donation->is_anonymous  = $is_anonymous;

        if ( isset( $_POST['fundraiser'] ) ) {
            $donation->fundraiser_id = peerraiser_get_fundraiser_by_slug( $_POST['fundraiser'] )->ID;
        }

        $donation->add_note( __( 'Donation started and is currently pending.', 'peerraiser' ), __( 'PeerRaiser Bot', 'peerraiser' ) );

        $donation->save();

        if ( filter_var( $plugin_options['test_mode'], FILTER_VALIDATE_BOOLEAN ) ) {
            $redirect_url = sprintf( \PeerRaiser\Core\Setup::get_plugin_config()->get('peerraiser_url.sandbox'), urlencode( trim( get_option('peerraiser_slug') ) ), $donation->transaction_id );
        } else {
            $redirect_url = sprintf(\PeerRaiser\Core\Setup::get_plugin_config()->get('peerraiser_url.live'), urlencode( trim( get_option('peerraiser_slug') ) ), $donation->transaction_id );
        }

        // Send to PeerRaiser.com to finish the transaction
        wp_redirect( $redirect_url );
        exit;
    }

	/**
	 * Send a donation receipt to the donor when a donation is made
	 *
	 * @param $donation
	 */
    public function send_donation_receipt_email( $donation ) {
		$peerraiser_options = get_option( 'peerraiser_options', array() );

		// Skip donation receipt if option is set to disabled
		if ( $peerraiser_options['donation_receipt_enabled'] === 'false' ) {
			return;
		}

    	/**
		 * You can prevent the email from being sent by using this filter:
		 * add_filter('peerraiser_skip_donation_receipt', '__return_true');
		 */
    	if ( apply_filters('peerraiser_skip_donation_receipt', false, $donation ) ) {
    		return;
	    }

	    $donor = new Donor( $donation->donor_id );

		$vars = array(
			"{{donor_first_name}}" => $donor->first_name,
			"{{donor_last_name}}" => $donor->last_name,
			"{{donor_full_name}}" => $donor->full_name,
			"{{donation_summary}}" => $this->get_transaction_summary( $donation ),
			"{{site_name}}" => get_bloginfo( 'name' ),
		);

	    $vars = apply_filters( 'donation_receipt_variables', $vars, $donation );

		$message = strtr( $peerraiser_options['donation_receipt_body'], $vars);
		$subject = strtr( $peerraiser_options['donation_receipt_subject'], $vars);

	    /**
	     * Append test notification if the plugin is in test mode.
	     *
	     * You can prevent the email from being sent by using this filter:
	     * add_filter('peerraiser_enable_test_message_in_email', '__return_false');
	     */
		if ( peerraiser_is_test_mode() && apply_filters('peerraiser_enable_test_message_in_email', true ) ) {
			$subject .= ' ' . __( '(TEST MODE)', 'peerraiser' );
			$message .= "\r\n\r\n" . __( 'Note: This is a test transaction. You will not be charged for this donation.', 'peerraiser' );
		}

		Email::send_email( $donor->email_address, $subject, wpautop($message), 'peerraiser_donation_receipt', $donation );
	}

	/**
	 * Send a notification to the site owner when a donation is made
	 *
	 * @param $donation
	 */
	public function send_donation_notification_email( $donation ) {
		$peerraiser_options = get_option( 'peerraiser_options', array() );

		// Skip donation receipt if option is set to disabled
		if ( $peerraiser_options['donation_receipt_enabled'] === 'false' ) {
			return;
		}

		/**
		 * You can prevent the email from being sent by using this filter:
		 * add_filter('peerraiser_skip_donation_receipt', '__return_true');
		 */
		if ( apply_filters('peerraiser_skip_donation_notification', false, $donation ) ) {
			return;
		}

		$donor = new Donor( $donation->donor_id );

		$vars = array(
			"{{donor_first_name}}" => $donor->first_name,
			"{{donor_last_name}}" => $donor->last_name,
			"{{donor_full_name}}" => $donor->full_name,
			"{{donation_summary}}" => $this->get_transaction_summary( $donation ),
			"{{site_name}}" => get_bloginfo( 'name' ),
		);

		$vars = apply_filters( 'donation_notification_variables', $vars, $donation );

		$message = strtr( $peerraiser_options['new_donation_notification_body'], $vars);
		$subject = strtr( $peerraiser_options['new_donation_notification_subject'], $vars);
		$to      = $peerraiser_options['new_donation_notification_to'];

		/**
		 * Append test notification if the plugin is in test mode.
		 *
		 * You can prevent the email from being sent by using this filter:
		 * add_filter('peerraiser_enable_test_message_in_email', '__return_false');
		 */
		if ( peerraiser_is_test_mode() && apply_filters('peerraiser_enable_test_message_in_email', true ) ) {
			$subject .= ' ' . __( '(TEST MODE)', 'peerraiser' );
			$message .= "\r\n\r\n" . __( 'Note: This is a test transaction. Donor was not charged for this donation.', 'peerraiser' );
		}

		Email::send_email( $to, $subject, wpautop($message), 'peerraiser_donation_notification', $donation );
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

	/**
	 * Creates the transaction summary for donation emails
	 *
	 * @param $donation
	 *
	 * @since     1.2.0
	 * @return string
	 */
    private function get_transaction_summary( $donation ) {
	    $peerraiser_options = get_option( 'peerraiser_options', array() );

	    $this->assign( 'donation', $donation );
	    $this->assign( 'tax_id', $peerraiser_options['tax_id'] );

	    return $this->get_text_view( 'email/transaction-summary' );
    }
}