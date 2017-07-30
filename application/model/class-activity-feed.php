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

    public function add_install_notice_to_feed( $version ){
        $notice = array(
            'id'      => 'peerraiser_installed',
            'type'    => 'install',
            'message' => sprintf( __( 'PeerRaiser %s was installed', 'peerraiser' ), $version ),
            'time'    => current_time('timestamp')
        );

        // If the activity feed already exists, say updated instead of installed
        if ( $this->get_activity_feed() ) {
            $notice['message'] = sprintf( __( 'PeerRaiser was updated to version %s', 'peerraiser' ), $version );
        }

        $this->add_activity($notice);
    }

    public function add_donation_to_feed( $donation ) {
        $donor = new \PeerRaiser\Model\Donor( $donation->donor_id );
        $campaign = new \PeerRaiser\Model\Campaign( $donation->campaign_id );

        $message = sprintf( __( '<a href="admin.php?page=peerraiser-donors&donor=%1$d&view=summary">%2$s</a> donated <a href="admin.php?page=peerraiser-donations&donation=%3$d&view=summary">%4$s</a> to the <a href="admin.php?page=peerraiser-campaigns&campaign=%5$d&view=summary">%6$s</a> campaign.', 'peerraiser' ), $donor->ID, $donor->full_name, $donation->ID, peerraiser_money_format( $donation->total ), $campaign->ID, $campaign->campaign_name );

        $this->add_activity(
            array(
                'id'      => $donation->ID,
                'type'    => 'donation',
                'message' => $message,
                'time'    => current_time('timestamp')
            )
        );
    }

    public function remove_donation_from_feed( $donation ) {
        $this->remove_activity( $donation->ID );
    }

    public function add_campaign_to_feed( $campaign ) {
        $message = sprintf( __( '"<a href="admin.php?page=peerraiser-campaigns&campaign=%1$d&view=summary">%2$s</a>" campaign created.', 'peerraiser' ), $campaign->ID, $campaign->campaign_name );

        $this->add_activity(
            array(
                'id'      => $campaign->ID,
                'type'    => 'campaign',
                'message' => $message,
                'time'    => current_time('timestamp')
            )
        );
    }

    public function remove_campaign_from_feed( $campaign ) {
        $this->remove_activity( $campaign->ID );
    }

    public function add_fundraiser_to_feed( $post ) {
        $participant_id        = isset( $_POST[ '_peerraiser_fundraiser_participant' ] ) ? (int) $_POST[ '_peerraiser_fundraiser_participant' ] : get_current_user_id();
        $participant_details   = get_user_by( 'id', $participant_id );
        $participant_full_name = trim( $participant_details->first_name . ' ' . $participant_details->last_name );
        $user_info             = get_userdata( $participant_id );
        $participant_name      = empty( $participant_full_name ) ? $user_info->user_login : $participant_full_name;
        $campaign              = new \PeerRaiser\Model\Campaign( $_POST[ '_peerraiser_fundraiser_campaign' ] );

        $message = sprintf( __( '<a href="user-edit.php?user_id=%1$d">%2$s</a> created fundraiser <a href="post.php?action=edit&post=%3$d">%4$s</a> for the <a href="admin.php?page=peerraiser-campaigns&view=summary&campaign=%5$d">%6$s</a> campaign.', 'peerraiser' ), $participant_id, $participant_name, $post->ID, get_the_title( $post->ID ), $campaign->ID, $campaign->campaign_name );

        // Remove activity if fundraiser is already in the feed
        $this->remove_activity( $post->ID );

        $this->add_activity(
            array(
                'id'      => $post->ID,
                'type'    => 'fundraiser',
                'message' => $message,
                'time'    => current_time('timestamp')
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