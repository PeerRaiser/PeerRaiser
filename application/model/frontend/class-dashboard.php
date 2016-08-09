<?php

namespace PeerRaiser\Model\Frontend;

class Dashboard extends \PeerRaiser\Model\Admin {

    private static $instance   = null;
    private static $navigation = array();
    private static $fields     = array();

    public function __construct() {}

    /**
     * Singleton to get only one Dashboard model
     *
     * @return    \PeerRaiser\Model\Admin\Dashboard
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance   = new self();
            self::$navigation = array(
                "profile"   => __( "My Profile", 'peerraiser' ),
                "donations" => __( "Donations", 'peerraiser' ),
                "settings"  => __( "Settings", 'peerraiser' ),
            );
            self::$fields = array(
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
            );
        }
        return self::$instance;
    }


    public function get_navigation() {
        return self::$navigation;
    }


    public function get_fields() {
        return self::$fields;
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