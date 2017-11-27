<?php

namespace PeerRaiser\Model\Admin;

class Admin_Notices {

    private static $notices = array();

    private $notice_messages = array();

    public function __construct() {
    	$this->setup_notice_messages();
    }

	public static function get_notices() {
        return self::$notices;
    }

    public static function add_notice( $message, $class = 'notice-info', $dismissible = false) {
        $notice = array(
            'message' => $message,
            'class' => $class, // notice-error, notice-warning, or notice-info
            'is-dismissible' => $dismissible
        );
        array_push(self::$notices, $notice);
    }

    private function setup_notice_messages() {
    	$default_notices = array(
    		'campaign_added' => array(
			    'message'     => __( 'Campaign added', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'campaign_updated' => array(
			    'message'     => __( 'Campaign updated', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'campaign_deleted' => array(
			    'message'     => __( 'Campaign deleted', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'team_added' => array(
			    'message'     => __( 'Team added', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'team_updated' => array(
			    'message'     => __( 'Team updated', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'team_deleted' => array(
			    'message'     => __( 'Team deleted', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'donation_added' => array(
			    'message'     => __( 'Donation added', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'donation_updated' => array(
			    'message'     => __( 'Donation updated', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'donation_deleted' => array(
			    'message'     => __( 'Donation deleted', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'donor_added' => array(
			    'message'     => __( 'Donor added', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'donor_updated' => array(
			    'message'     => __( 'Donor updated', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'donor_deleted' => array(
			    'message'     => __( 'Donor deleted', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'participant_added' => array(
			    'message'     => __( 'Participant added', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'participant_updated' => array(
			    'message'     => __( 'Participant updated', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'participant_deleted' => array(
			    'message'     => __( 'Participant deleted', 'peerraiser' ),
			    'class'       => 'notice-info',
			    'dismissible' => true,
		    ),
		    'test_mode_active_reminder' => array(
		    	'message' => __( 'Test mode is active. Transactions will not be charged, and amounts shown are from test donations only.' ),
			    'class' => 'notice-info',
                'dismissible' => true,
		    )
	    );

	    $this->notice_messages = apply_filters( 'peerraiser_notice_messages', $default_notices );
    }

    public function get_notice_message( $key ) {
    	$message = isset( $this->notice_messages[$key] ) ? $this->notice_messages[$key] : array();

	    return apply_filters( 'peerraiser_notice_message_' . $key, $message );
    }

}