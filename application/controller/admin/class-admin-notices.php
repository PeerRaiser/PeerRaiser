<?php

namespace PeerRaiser\Controller\Admin;

class Admin_Notices extends \PeerRaiser\Controller\Base {
    private static $notices = array();

    public function register_actions() {
        add_action( 'admin_notices', array( $this, 'display_notices' ) );
    }

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

    public static function display_notices() {
        foreach (self::$notices as $notice) {
            $class = ( $notice['is-dismissible'] ) ? $notice['class'] . ' is-dismissible' : $notice['class'];
            ?>
                <div class="notice <?= $class ?>">
                    <p><?php echo $notice['message'] ?></p>
                </div>
            <?php
        }
    }

}