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
     * Contains the logger instance.
     *
     * @var    \PeerRaiser_Core_Logger
     */
    protected $logger;

    /**
     * @param    \PeerRaiser\Model\Config    $config
     *
     * @return    \PeerRaiser\Core\View
     */
    public function __construct( $config = null ) {
        $this->logger = \PeerRaiser\Core\Logger::get_logger();
        parent::__construct( $config );
    }

}
