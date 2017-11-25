<?php

namespace PeerRaiser\Helper;

class Email {

    public static function send_email( $to, $subject, $message, $id = '', $context = array() ) {
	    $peerraiser_options = get_option( 'peerraiser_options', array() );

	    $from_name =  $peerraiser_options['from_name'];
	    $from_email = $peerraiser_options['from_email'];

	    // Convert email addresses to an array and remove whitespace
	    if ( ! is_array( $to ) ) {
	    	$to = array_map('trim', explode( ',', $to ) );
	    } else {
	    	$to = array_map('trim', $to );
	    }

	    /**
	     * If an ID is passed, filters will be created. This allows you to customize the
	     * email options. Filter example:
	     *
	     * add_filter( 'peerraiser_donation_receipt_subject', function( $subject, $donation ) {
	     *      if ( $donation->campaign_id === 5 ) {
	     *          return 'Custom subject here';
	     *      }
	     *      return $from_name;
	     * }, 10, 2 );
	     */
	    if ( ! empty( $id ) ) {
		    $from_name  = apply_filters( $id . 'from_name', $from_name, $context );
		    $from_email = apply_filters( $id . 'from_email', $from_email, $context );
		    $to         = apply_filters( $id . '_to', $to, $context );
		    $subject    = apply_filters( $id . '_subject', $subject, $context );
		    $message    = apply_filters( $id . '_message', $message, $context );
	    }

    	$headers = array();

    	$headers[] = "From: {$from_name} <{$from_email}>";
	    $headers[] = "Content-Type: text/html; charset=UTF-8";

	    wp_mail( $to, $subject, $message, $headers );
    }

}
