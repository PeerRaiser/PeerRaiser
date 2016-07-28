<?php

namespace PeerRaiser\Model\Admin;

class Dashboard extends \PeerRaiser\Model\Admin {

    private static $fields     = array();
    private static $navigation = null;

    public function __construct() {}

    /**
     * Singleton to get only one Dashboard model
     *
     * @return    \PeerRaiser\Model\Admin\Dashboard
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
            self::$navigation = array();
        }
        return self::$instance;
    }

}