<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Controller\Base;

class Widget extends Base {

    public function register_actions() {
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
        add_action( 'widgets_init', array( $this, 'maybe_set_default_widgets' ) );

    }

    public function register_sidebars() {
        // Campaign Sidebar
        register_sidebar( array(
            'name' => __( 'PeerRaiser Campaign Sidebar', 'peerraiser' ),
            'id' => 'peerraiser-campaign-sidebar',
            'description' => __( 'Widgets in this area will be shown on side of campaign pages.', 'peerraiser' ),
            'before_widget' => '<div id="%1$s" class="peerraiser-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );

        // Fundraiser Sidebar
        register_sidebar( array(
            'name' => __( 'PeerRaiser Fundraiser Sidebar', 'peerraiser' ),
            'id' => 'peerraiser-fundraiser-sidebar',
            'description' => __( 'Widgets in this area will be shown on side of fundraiser pages.', 'peerraiser' ),
            'before_widget' => '<div id="%1$s" class="peerraiser-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );

        // Team Sidebar
        register_sidebar( array(
            'name' => __( 'PeerRaiser Team Sidebar', 'peerraiser' ),
            'id' => 'peerraiser-team-sidebar',
            'description' => __( 'Widgets in this area will be shown on side of team pages.', 'peerraiser' ),
            'before_widget' => '<div id="%1$s" class="peerraiser-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ) );
    }

    public function register_widgets() {
        // Campaign Widgets
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Campaign_Donate_Button_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Campaign_Register_Button_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Campaign_Total_Raised_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Campaign_Thermometer_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Campaign_Donations_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Top_Fundraisers_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Top_Teams_Widget' );

        // Fundraiser Widgets
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Fundraiser_Donations_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Fundraiser_Donate_Button_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Fundraiser_Total_Raised_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Fundraiser_Thermometer_Widget' );

        // Team Widgets
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Team_Join_Button_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Team_Thermometer_Widget' );
        register_widget( 'PeerRaiser\Model\Frontend\Widget\Team_Roster_Widget' );
    }

    /**
     * Maybe set the default widgets
     *
     * If the default widgets haven't already been set, add them to the widget areas
     *
     * @since 1.1.1
     */
    public function maybe_set_default_widgets() {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $active_widgets  = get_option( 'sidebars_widgets' );

        //Check if sidebar widgets have already been setup
        if ( isset( $plugin_options['_widgets_setup'] ) ) {
            return;
        }

        $widget_options = array();

        // If the campaign sidebar is empty, add campaign widgets
        if ( empty( $active_widgets[ 'peerraiser-campaign-sidebar' ] ) ) {
            $widget_options['peerraiser-campaign-sidebar'] = array(
                'peerraiser_campaign_donate_button' => array(
                    'button_label' => __( 'Donate to this campaign', 'peerraiser' ),
                    'campaign' => 'auto',
                ),
                'peerraiser_campaign_register_button' => array(
                    'button_label' => __( 'Register Now', 'peerraiser' ),
                    'campaign' => 'auto',
                ),
                'peerraiser_campaign_total_raised' => array(
                    'hide_if_zero' => false,
                    'before_amount' => '',
                    'after_amount' => sprintf( "<h4>%s</h4>", __( 'Total Raised', 'peerraiser' ) ),
                    'campaign' => 'auto',
                ),
                'peerraiser_campaign_thermometer' => array(
                    'title' => '',
                    'campaign' => 'auto',
                ),
                'peerraiser_top_fundraisers' => array(
                    'title' => __( 'Top Fundraisers', 'peerraiser' ),
                    'list_size' => 10,
                    'campaign' => 'auto',
                ),
                'peerraiser_top_teams' => array(
                    'title' => __( 'Top Teams', 'peerraiser' ),
                    'list_size' => 10,
                    'campaign' => 'auto',
                ),
                'peerraiser_campaign_donations' => array(
                    'title' => __( 'Donations', 'peerraiser' ),
                    'list_size' => 10,
                    'campaign' => 'auto',
                ),
            );
        }

        // If the fundraiser sidebar is empty, add campaign widgets
        if ( empty( $active_widgets[ 'peerraiser-fundraiser-sidebar' ] ) ) {
            $widget_options['peerraiser-fundraiser-sidebar'] = array(
                'peerraiser_fundraiser_donate_button' => array(
                    'button_label' => __( 'Donate to my fundraiser', 'peerraiser'),
                    'fundraiser' => 'auto',
                ),
                'peerraiser_fundraiser_total_raised' => array(
                    'hide_if_zero' => false,
                    'before_amount' => '',
                    'after_amount' => sprintf( "<h4>%s</h4>", __( 'Total Raised', 'peerraiser' ) ),
                    'fundraiser' => 'auto',
                ),
                'peerraiser_fundraiser_thermometer' => array(
                    'title' => '',
                    'fundraiser' => 'auto',
                ),
                'peerraiser_fundraiser_donations' => array(
                    'title' => __( 'Recent Donations', 'peerraiser' ),
                    'list_size' => 10,
                    'fundraiser' => 'auto',
                ),
            );
        }

        // If the team sidebar is empty, add campaign widgets
        if ( empty( $active_widgets[ 'peerraiser-team-sidebar' ] ) ) {
            $widget_options['peerraiser-team-sidebar'] = array(
                'peerraiser_team_join_button' => array(
                    'button_label' => __( 'Join Team', 'peerraiser' ),
                    'team' => 'auto',
                ),
                'peerraiser_team_thermometer' => array(
                    'title' => '',
                    'team' => 'auto',
                ),
                'peerraiser_team_roster' => array(
                    'title' => __( 'Team Roster', 'peerraiser' ),
                    'list_size' => -1,
                    'team' => 'auto',
                ),
            );
        }

        foreach ( $widget_options as $sidebar => $widgets ) {
            foreach ( $widget_options[$sidebar] as $widget => $option ) {
                $active_widgets[$sidebar][] = $widget . '-2';

                update_option( 'widget_' . $widget, array( '2' => $option, '_multiwidget' => 1 ) );
            }
        }

        // Update the active widgets
        update_option( 'sidebars_widgets', $active_widgets );

        // Set widget_setup to true, so we know it's been setup
        $plugin_options['_widgets_setup'] = true;
        update_option( 'peerraiser_options', $plugin_options );
    }

}