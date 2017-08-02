<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Controller\Base;

class Widget extends Base {

    public function register_actions() {
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
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
    }

}