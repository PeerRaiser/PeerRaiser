<?php

namespace PeerRaiser\Controller;

/**
 *  Base controller.
 */
class Base extends \PeerRaiser\Core\View implements \PeerRaiser\Core\Event\Subscriber_Interface {
    /**
     * @see \PeerRaiser\Core\Event\Subscriber_Interface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array();
    }

    /**
     * @see \PeerRaiser_Core_Event_Subscriber_Interface::get_shared_events()
     */
    public static function get_shared_events() {
        return array();
    }

    /**
     * @param    \PeerRaiser\Model\Config    $config
     *
     * @return    \PeerRaiser\Core\View
     */
    public function __construct( $config = null ) {
        parent::__construct( $config );
    }

}
