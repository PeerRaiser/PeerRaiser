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
        $post_type_name = array(
            'post_type_name' => 'fundraiser',
            'singular' => 'Fundraiser',
            'plural' => 'Fundraisers',
            'slug' => $fundraiser_slug
        );
        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
            'rewrite'      => array(
                'slug' => $fundraiser_slug
            )
        );
        $fundraisers = new \PeerRaiser\Model\Custom_Post_Type( $post_type_name, $args );
        $fundraisers->columns(array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'campaign' => __('Campaign'),
            'participant' => __('Participant'),
            'team' => __('Team'),
            'goal_amount' => __('Goal'),
            'amount_raised' => __('Raised'),
        ));

        // Campaign post type for each campaign. Fundraisers are tied to specific campaigns
        $campaign_slug = ( isset($plugin_options['campaign_slug']) ) ? $plugin_options['campaign_slug'] : 'campaign';
        $post_type_name = array(
            'post_type_name' => 'pr_campaign',
            'singular' => 'Campaign',
            'plural' => 'Campaigns',
            'slug' => 'campaign'
        );
        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
            'rewrite'      => array(
                'slug' => $campaign_slug
            )
        );
        $campaigns = new \PeerRaiser\Model\Custom_Post_Type( $post_type_name, $args );
        $campaigns->columns(array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'amount_raised' => __('Raised'),
            'goal_amount' => __('Goal'),
            'fundraisers' => __('Fundraisers'),
            'teams' => __('Teams'),
            'donations' => __('Donations'),
            'start_date' => __('Start Date'),
            'end_date' => __('End Date'),
        ));

        // Team post type for each team. Users can create or join teams.
        $args = array(
            'show_in_menu' => false,
            'supports'     => array('title'),
        );
        $post_type_name = array(
            'post_type_name' => 'pr_team',
            'singular' => 'Team',
            'plural' => 'Teams',
            'slug' => 'team'
        );
        $teams = new \PeerRaiser\Model\Custom_Post_Type( $post_type_name, $args );
        $teams->columns(array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Team Name'),
            'leader' => __('Team Leader'),
            'campaign' => __('Campaign'),
            'goal_amount' => __('Goal'),
            'amount_raised' => __('Raised'),
            'fundraisers' => __('Fundraisers'),
        ));

        // Donation post type. Each post is a seperate donation/transaction
        $args = array(
            'exclude_from_search' => true,
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'show_in_menu'        => false,
            'supports'            => false,
        );
        $post_type_name = array(
            'post_type_name' => 'pr_donation',
            'singular' => 'Donation',
            'plural' => 'Donations',
            'slug' => 'donation'
        );
        $donation = new \PeerRaiser\Model\Custom_Post_Type( $post_type_name, $args );
        $donation->columns(array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID'),
            'link' => __('Details'),
            'donor' => __('Donor'),
            'donation_amount' => __('Amount'),
            'method' => __('Method'),
            'campaign' => __('Campaign'),
            'fundraiser' => __('Fundraiser'),
            'test_mode' => __('Live Mode?'),
            'date' => __('Date'),
        ));

        // Donor post type. Each post is a seperate donor record
        $args = array(
            'exclude_from_search' => true,
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'show_in_menu'        => false,
            'supports'            => false,
        );
        $post_type_name = array(
            'post_type_name' => 'pr_donor',
            'singular' => 'Donor',
            'plural' => 'Donors',
            'slug' => 'donor'
        );
        $labels = array(
            'edit_item' => __( 'Donor Info', 'peerraiser' ),
        );
        $donor = new \PeerRaiser\Model\Custom_Post_Type( $post_type_name, $args, $labels );
        $donor->columns(array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID'),
            'link' => __('Details'),
            'first_name' => __('First Name'),
            'last_name' => __('Last Name'),
            'email_address' => __('Email'),
            'username' => __('Username'),
            'total_donated' => __('Total Donated'),
            'date' => __('Date'),
        ));
    }

}