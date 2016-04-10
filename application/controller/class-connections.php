<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser admin controller.
 */
class Connections extends Base {

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_p2p_init' => array(
                array( 'register_connections', 200 ),
            ),
        );
    }


    /**
     * Setup connection types for P2P
     *
     * @since     1.0.0
     */
    public function register_connections() {
        if ( !function_exists( 'p2p_register_connection_type' ) ){
            return;
        }

        // Create event so connections can be added externally
        $event = new \PeerRaiser\Core\Event();
        $event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_connections_data', $event );
        $connections = (array) $event->get_result();

        // Get default connections
        $model = new \PeerRaiser\Model\Connections();
        $default_connections = $model->get_connections();

        // Merge default connections with any new connections
        $connections = array_merge( $default_connections, $connections );

        foreach ($connections as $connection) {
            p2p_register_connection_type( $connection );
        }

    }

}