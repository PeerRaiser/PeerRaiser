<?php

namespace PeerRaiser\Model;

class Activity_Feed {

    public function add_activity ( $args = array() ) {
        $current_feed = get_option( 'peerraiser_activity_feed' );
        // If there are already 100 items, remove the oldest item
        if ( count( $current_feed ) == 100 ) {
            array_pop( $current_feed );
        }
        array_unshift( $current_feed, $args );
        update_option( 'peerraiser_activity_feed', $current_feed );
    }

    public function add_donation_to_feed( $post ) {
        $donor            = $_POST['_donor'];
        $donor_first_name = get_post_meta( $donor, '_donor_first_name', true );
        $donor_last_name  = get_post_meta( $donor, '_donor_last_name', true );
        $donor_full_name  = $donor_first_name . ' ' . $donor_last_name;
        $donation_amount  = $_POST['_donation_amount'];
        $designation      = isset( $_POST['_fundraiser'] ) ? $_POST['_fundraiser'] : $_POST['_campaign'];

        $message = "<a href=\"post.php?action=edit&post=" . $donor . "\">" . $donor_full_name . "</a> donated $" . $donation_amount . " to " . "<a href=\"post.php?action=edit&post=" . $designation . "\">" . get_the_title( $designation ) . "</a>";

        $this->add_activity(
            array(
                'id'      => $post->ID,
                'type'    => 'new_donation',
                'message' => $message,
                'time'    => time()
            )
        );
    }


    public function get_activity_feed() {
        return get_option( 'peerraiser_activity_feed', array() );
    }


    public function remove_activity( $id ) {
        $current_feed = $this->get_activity_feed();

        foreach ($current_feed as $key => $value) {
            if ( isset( $value['id'] ) && $value['id'] == $id ) {
                unset( $current_feed[$key] );
                update_option( 'peerraiser_activity_feed', $current_feed );
                break;
            }
        }

    }

}