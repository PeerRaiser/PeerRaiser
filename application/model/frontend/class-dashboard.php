<?php

namespace PeerRaiser\Model\Frontend;

use PeerRaiser\Model\Donation;

class Dashboard extends \PeerRaiser\Model\Admin\Admin {

    private $navigation = array();
    private $fields     = array();

    public function __construct() {
        $this->navigation = array(
            "profile"   => __( "My Profile", 'peerraiser' ),
            "donations" => __( "Donations", 'peerraiser' ),
            "settings"  => __( "Settings", 'peerraiser' ),
        );

        $this->fields = array(
            array(
                'name' => __( 'First Name', 'peerraiser' ),
                'id'   => 'first_name',
                'type' => 'text',
            ),
            array(
                'name' => __( 'Last Name', 'peerraiser' ),
                'id'   => 'last_name',
                'type' => 'text',
            ),
            array(
                'name' => __( 'Email', 'peerraiser' ),
                'id'   => 'user_email',
                'type' => 'text',
            ),
            array(
                'name' => __( 'Bio', 'peerraiser' ),
                'id'   => 'description',
                'type' => 'textarea',
            ),
        );
    }

    public function get_navigation() {
        return $this->navigation;
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_donations() {
        $participant_id = get_current_user_id();
        $donation_model = new Donation();

        return $donation_model->get_donations( array( 'participant_id' => $participant_id, 'status' => 'completed' ) );
    }

}