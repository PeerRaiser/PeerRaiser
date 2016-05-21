<?php

namespace PeerRaiser\Helper;

/**
 * PeerRaiser debug helper.
 */
class Debug {

    /**
     * Sends an email to the admin if test_mode is turned on
     *
     * @since     1.0.0
     * @param     string    $subject    The subject line
     * @param     string    $message    The body of the email
     * @return    void
     */
    public static function debug_email( $subject, $message ) {

        $plugin_options = get_option( 'peerraiser_options', array() );
        $test_most      = $plugin_options['test_mode'];

        if ( $test_most == 'true' ) {
            $admin_email = get_option( 'admin_email' );
            wp_mail( $admin_email, $subject, $message );

            if ( WP_DEBUG_LOG )
                error_log( "SUBJECT: " . $subject . "\r\n MESSAGE: " . $message );
        }

    }

}