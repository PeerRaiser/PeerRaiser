<?php

namespace PeerRaiser\Model;

class Activity_Feed {

    public function add_activity ( $args = array() ) {
        $current_feed = get_option( 'peerraiser_activity_feed', array() );
        // If there are already 100 items, remove the oldest item
        if ( count( $current_feed ) == 100 ) {
            array_pop( $current_feed );
        }
        array_unshift( $current_feed, $args );
        update_option( 'peerraiser_activity_feed', $current_feed );
    }

    public function add_install_notice_to_feed(){
        $this->add_activity(
            array(
                'id'      => 'peerraiser_installed',
                'type'    => 'install',
                'message' => __( 'PeerRaiser was installed', 'peerraiser'),
                'time'    => time()
            )
        );
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
                'type'    => 'donation',
                'message' => $message,
                'time'    => time()
            )
        );
    }

    public function add_campaign_to_feed( $post ) {
        $author_id      = get_post_field( 'post_author', $post->ID );
        $author_details = get_user_by( 'id', $author_id );
        $author_name    = $author_details->first_name . ' ' . $author_details->last_name;

        $message = "<a href=\"user-edit.php?user_id=" . $author_id . "\">" . $author_name . "</a> created campaign \"<a href=\"post.php?action=edit&post=" . $post->ID . "\">" . get_the_title( $post->ID ) . "</a>\"";

        $this->add_activity(
            array(
                'id'      => $post->ID,
                'type'    => 'campaign',
                'message' => $message,
                'time'    => time()
            )
        );
    }
    public function add_fundraiser_to_feed( $post ) {
        $participant_id        = $_POST[ '_fundraiser_participant' ];
        $participant_details   = get_user_by( 'id', $participant_id );
        $participant_full_name = $participant_details->first_name . ' ' . $participant_details->last_name;
        $user_info             = get_userdata( $participant_id );
        $participant_name      = ( trim( $participant_full_name ) == false ) ? $user_info->user_login : $participant_full_name;
        $campaign_id           = $_POST[ '_fundraiser_campaign' ];

        $message = "<a href=\"user-edit.php?user_id=" . $participant_id . "\">" . $participant_name . "</a> created fundraiser \"<a href=\"post.php?action=edit&post=" . $post->ID . "\">" . get_the_title( $post->ID ) . "</a>\" for the \"<a href=\"user-edit.php?user_id=" . $campaign_id . "\">" . get_the_title( $campaign_id ) . "</a>\" campaign";

        $this->add_activity(
            array(
                'id'      => $post->ID,
                'type'    => 'fundraiser',
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