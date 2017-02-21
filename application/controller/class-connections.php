<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser admin controller.
 */
class Connections extends Base {

    public function register_actions() {
        add_action( 'p2p_init',   array( $this, 'register_connections' ) );
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

        // Get default connections
        $model = new \PeerRaiser\Model\Connections();
        $default_connections = $model->get_connections();

        // Merge default connections with any new connections
        $connections = apply_filters( 'peerraiser_connections', $default_connections );

        foreach ($connections as $connection) {
            p2p_register_connection_type( $connection );
        }

    }

}