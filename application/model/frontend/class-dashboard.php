<?php

namespace PeerRaiser\Model\Frontend;

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
        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_participant',
            'connected_items' => get_current_user_id()
        );
        return new \WP_Query( $args );
    }

    public function get_fundraisers() {
        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'fundraiser_to_participant',
            'connected_items' => get_current_user_id(),
        );
        return new \WP_Query( $args );
    }

    /**
     * Get teams where the current user is the leader
     *
     * @since     1.0.0
     * @return    WP_Query
     */
    public function get_teams() {
        $args = array(
            'post_type' => 'pr_team',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'connected_type' => 'teams_to_captains',
            'connected_items' => get_current_user_id(),
        );
        return new \WP_Query( $args );
    }

}