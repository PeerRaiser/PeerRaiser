<?php

namespace PeerRaiser\Model\Admin;

class Admin_Notices {

    private static $notices = array();

    public static function get_notices() {
        return self::$notices;
    }

    public static function add_notice( $message, $class = 'notice-info', $dismissible = false) {
        $notice = array(
            'message' => $message,
            'class' => $class,
            'is-dismissible' => $dismissible
        );
        array_push(self::$notices, $notice);
    }

}