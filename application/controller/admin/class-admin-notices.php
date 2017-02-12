<?php

namespace PeerRaiser\Controller\Admin;

class Admin_Notices extends \PeerRaiser\Controller\Base {
    private static $notices = array();
    private static $instance = null;

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_admin_notices' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'display_notices' )
            ),
        );
    }

    /**
     * Singleton to get only one Admin_Notices
     *
     * @return    \PeerRaiser\Admin\Admin_Notices
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function __construct(){}


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