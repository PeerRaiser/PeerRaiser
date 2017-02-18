<?php

namespace PeerRaiser\Model;

class Admin {

    private $menu_items = array();

    public function __construct(){
        $this->menu_items = array(
            'dashboard' => array(
                'url'   => 'peerraiser-dashboard',
                'title' => __( 'Dashboard', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'campaigns' => array(
                'url'   => 'peerraiser-campaigns',
                'title' => __( 'Campaigns', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'fundraisers' => array(
                'url'   => 'peerraiser-fundraisers',
                'title' => __( 'Fundraisers', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'teams' => array(
                'url'   => 'peerraiser-teams',
                'title' => __( 'Teams', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'donations' => array(
                'url'   => 'peerraiser-donations',
                'title' => __( 'Donations', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'donors' => array(
                'url'   => 'peerraiser-donors',
                'title' => __( 'Donors', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'settings' => array(
                'url'   => 'peerraiser-settings',
                'title' => __( 'Settings', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            )
        );
    }

    public function get_menu_items() {
        return apply_filters( 'peerraiser_menu_items', $this->menu_items );
    }

}