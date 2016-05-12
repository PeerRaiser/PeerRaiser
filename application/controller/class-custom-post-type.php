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
        $plugin_options = get_option( 'peerraiser_options', array() );

        // Fundraiser post type for each individual fundraiser page. Users create fundraisers
        $fundraiser_slug = ( isset($plugin_options['fundraiser_slug']) ) ? $plugin_options['fundraiser_slug'] : 'give';
        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
            'rewrite'      => array(
                'slug' => $fundraiser_slug
            )
        );
        $fundraisers = new \PeerRaiser\Model\Custom_Post_Type( 'Fundraiser', $args );

        // Campaign post type for each campaign. Fundraisers are tied to specific campaigns
        $campaign_slug = ( isset($plugin_options['campaign_slug']) ) ? $plugin_options['campaign_slug'] : 'campaign';
        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
            'rewrite'      => array(
                'slug' => $campaign_slug
            )
        );
        $campaigns = new \PeerRaiser\Model\Custom_Post_Type( 'Campaign', $args );
        $campaigns->register_taxonomy( 'Campaign Type');
        $campaigns->filters( array( 'Campaign Type' ) );

        // Team post type for each team. Users can create or join teams.
        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
        );
        $teams = new \PeerRaiser\Model\Custom_Post_Type( 'Team', $args );

        // Donation post type. Each post is a seperate donation/transaction
        $args = array(
            'exclude_from_search' => true,
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'show_in_menu'        => false,
            'supports'            => false,
        );
        $teams = new \PeerRaiser\Model\Custom_Post_Type( 'Donation', $args );

        // Donor post type. Each post is a seperate donor record
        $args = array(
            'exclude_from_search' => true,
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'show_in_menu'        => false,
            'supports'            => false,
        );
        $teams = new \PeerRaiser\Model\Custom_Post_Type( 'Donor', $args );
    }

}