<?php

namespace PeerRaiser\Controller;

/**
 * Activity feed controller.
 */
class Activity_Feed extends Base {

    public function register_actions() {
        add_action( 'save_post',                   array( $this, 'maybe_add_post_to_feed' ), 10, 3 );
        add_action( 'delete_post',                 array( $this, 'maybe_remove_post_from_feed' ) );
        add_action( 'peerraiser_campaign_added',   array( $this, 'add_campaign_to_feed' ) );
        add_action( 'peerraiser_campaign_deleted', array( $this, 'remove_campaign_from_feed', ) );
        add_action( 'peerraiser_donation_added',   array( $this, 'add_donation_to_feed' ) );
        add_action( 'peerraiser_donation_deleted', array( $this, 'remove_donation_from_feed', ) );
    }

    /**
     * Determines if the saved post should be added to the feed, depending on the post
     * type and status.
     *
     * @since 1.0.0
     * @param \PeerRaiser\Core\Event    $event
     */

    /**
     * @param int $post_id The post ID.
     * @param post $post The post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public function maybe_add_post_to_feed( $post_id, $post, $update ) {
        // If this isn't a fundraiser, exist early
        if ( $post->post_type !== 'fundraiser' )
            return;

        // If this is an autosave, exit early
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
            return;

        // If the status isn't "publish", exit early
        if ( isset($post->post_status) && 'publish' != $post->post_status )
            return;

        // If this isn't a new post, exit early
        if ( $post->post_modified_gmt != $post->post_date_gmt )
            return;

        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->add_fundraiser_to_feed( $post );
    }

    /**
     * Determines if the deleted post should be removed from the activity feed
     *
     * @since 1.0.0
     * @param int $post_id
     */
    public function maybe_remove_post_from_feed( $post_id ) {
        if ( get_post_type( $post_id ) !== 'fundraiser' )
            return;

        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->remove_activity( $post_id );
    }

    public function add_campaign_to_feed( $campaign ) {
        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->add_campaign_to_feed( $campaign );
    }

    public function remove_campaign_from_feed( $campaign ) {
        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->remove_campaign_from_feed( $campaign );
    }

    public function add_donation_to_feed( $donation ) {
        if ( $donation->status === 'pending' || $donation->is_test ) {
            return;
        }

        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->add_donation_to_feed( $donation );
    }

    public function remove_donation_from_feed( $donation ) {
        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->remove_donation_from_feed( $donation );
    }

}