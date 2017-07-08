<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser custom post type controller.
 */
class Custom_Post_Type extends Base {

    public function register_actions() {
        add_action( 'peerraiser_ready', array( $this, 'register_custom_post_types' ) );
    }

    /**
     * Register Customer Post Types
     *
     * Currently only registers the "Fundraiser" post type, but we may register others
     * in the future.
     *
     * @since     1.0.4
     * @return    void
     */
    public function register_custom_post_types(){
        $this->register_fundraiser_cpt();
    }

    /**
     * Register Fundraiser Customer Post Type
     *
     * A Fundraiser is a page that participants can customize, and use to raise money for
     * your cause. Fundraisers are usually associated with Campaigns and can be associated
     * with a team.
     *
     * @since     1.0.4
     * @return    void
     */
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
            'supports'     => array( 'title', 'editor', 'thumbnail' ),
            'rewrite'      => array(
                'slug' => $fundraiser_slug
            ),
        );

        $fundraisers = new \PeerRaiser\Model\Custom_Post_Type(
            apply_filters( 'peerraiser_fundraiser_cpt_name', $post_type_name ),
            apply_filters( 'peerraiser_fundraiser_cpt_args', $args )
        );

        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'title'         => __( 'Title', 'peerraiser' ),
            'campaign'      => __( 'Campaign', 'peerraiser' ),
            'participant'   => __( 'Participant', 'peerraiser' ),
            'team'          => __( 'Team', 'peerraiser' ),
            'goal_amount'   => __( 'Goal', 'peerraiser' ),
            'amount_raised' => __( 'Raised', 'peerraiser' ),
        );
        $fundraisers->columns( apply_filters( 'peerraiser_fundraiser_cpt_columns', $columns ) );
    }

}