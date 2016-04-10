<?php

namespace PeerRaiser\Model;

class Admin {

    private $menu_items = array();

    public function __construct(){
        $this->menu_items = array(
            'dashboard' => array(
                'url'   => 'peerraiser-dashboard-tab',
                'title' => __( 'Dashboard', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'fundraisers' => array(
                'url'   => 'edit.php?post_type=fundraiser',
                'title' => __( 'Fundraisers', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'teams' => array(
                'url'   => 'edit.php?post_type=pr_team',
                'title' => __( 'Teams', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'campaigns' => array(
                'url'   => 'edit.php?post_type=pr_campaign',
                'title' => __( 'Campaigns', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'settings' => array(
                'url'   => 'peerraiser-settings-tab',
                'title' => __( 'Settings', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            )
        );
    }

    public function get_menu_items() {
        return $this->menu_items;
    }

}