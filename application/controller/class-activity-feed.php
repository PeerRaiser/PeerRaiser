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
        return array(
            'peerraiser_post_saved' => array(
                array( 'maybe_add_post_to_feed' )
            ),
            'peerraiser_post_deleted' => array(
                array( 'maybe_remove_post_from_feed' )
            ),
        );
    }


    /**
     * Determines if the saved post should be added to the feed, depending on the post
     * type and status.
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     */
    public function maybe_add_post_to_feed( \PeerRaiser\Core\Event $event ) {
        list( $post_id, $post, $update ) = $event->get_arguments();

        // If this is an autosave, exit early
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // If the status isn't "publish", exit early
        if ( isset($post->post_status) && 'publish' != $post->post_status )
            return;

        // If this isn't a new post, exit early
        if ( $post->post_modified_gmt != $post->post_date_gmt )
            return;

        $model = new \PeerRaiser\Model\Activity_Feed();

        switch ( $post->post_type ) {
            case 'pr_donation':
                $model->add_donation_to_feed( $post );
                break;

            default:
                break;
        }

    }


    /**
     * Determines if the deleted post should be removed from the activity feed
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     */
    public function maybe_remove_post_from_feed( \PeerRaiser\Core\Event $event ) {
        list( $post_id ) = $event->get_arguments();

        $model = new \PeerRaiser\Model\Activity_Feed();
        $post_type = get_post_type( $post_id );

        switch ( $post_type ) {
            case 'pr_donation':
                $model->remove_activity( $post_id );
                break;

            default:
                break;
        }

    }

}