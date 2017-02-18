<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser custom post type controller.
 */
class Custom_Post_Type extends Base {

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_ready' => array(
                array( 'register_custom_post_types' )
            ),
         );
    }


    public function register_custom_post_types( \PeerRaiser\Core\Event $event ){
        $this->register_fundraiser_cpt();
    }

    private function register_fundraiser_cpt() {
        $plugin_options = get_option( 'peerraiser_options', array() );

        // Fundraiser post type for each individual fundraiser page. Users create fundraisers, where they solicit donations
        $fundraiser_slug = ( isset($plugin_options['fundraiser_slug']) ) ? $plugin_options['fundraiser_slug'] : 'give';
        $post_type_name = array(
            'post_type_name' => 'fundraiser',
            'singular'       => __( 'Fundraiser', 'peerraiser'),
            'plural'         => __( 'Fundraisers', 'peerraiser'),
            'slug'           => $fundraiser_slug,
        );

        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
            'rewrite'      => array(
                'slug' => $fundraiser_slug
            ),
        );

        $fundraisers = new \PeerRaiser\Model\Custom_Post_Type( $post_type_name, apply_filters( 'peerraiser_fundraiser_cpt_args', $args ) );

        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'title'         => __( 'Title', 'peerraiser' ),
            'campaign'      => __( 'Campaign', 'peerraiser' ),
            'participant'   => __( 'Participant', 'peerraiser' ),
            'team'          => __( 'Team', 'peerraiser' ),
            'goal_amount'   => __( 'Goal', 'peerraiser' ),
            'amount_raised' => __( 'Raised', 'peerraiser' ),
        );
        $columns = apply_filters( 'peerraiser_fundraiser_cpt_columns', $columns );
        $fundraisers->columns( $columns );
    }

}