<?php

namespace PeerRaiser\Controller;

/**
 * Activity feed controller.
 */
class Activity_Feed extends Base {

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        // return array(
        //     'peerraiser_donation_post_saved' => array(
        //         array( 'add_post_to_feed' )
        //     ),
        // );
    }


    public static function add_activity ( $args = array() ) {
        $current_feed = get_option( 'peerraiser_activity_feed', array() );

        // If there are already 100 items, remove the oldest item
        if ( count( $current_feed == 100 ) ) {
            array_pop( $current_feed );
        }

        array_unshift( $current_feed, $args );
        update_option( 'peerraiser_activity_feed', $current_feed );
    }


    public function add_post_to_feed( \PeerRaiser\Core\Event $event ) {
        list( $post_id, $post, $update ) = $event->get_arguments();

        // If this is an update instead of a new post, return
        if ( $update )
            return;

        switch ( $post->post_type ) {
            case 'pr_donation':
                $this->add_donation_to_feed( $post );
                break;

            default:
                break;
        }

    }


    public function add_donation_to_feed( $post ) {
        $donor_name      = get_post_meta( $post->ID, '_donor_name', true );
        $donation_amount = get_post_meta( $post->ID, '_donation_amount', true );
        $fundraiser_name = get_post_meta( $post->ID, '_fundraiser', true );

        $this->add_activity(
            array(
                'type' => 'new_donation',
                'message' => "$donor_name donated $donation_amount to \"$fundraiser_name\""
            )
        );
    }

}