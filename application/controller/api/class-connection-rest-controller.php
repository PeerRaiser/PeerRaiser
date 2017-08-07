<?php

namespace PeerRaiser\Controller\Api;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Response;
use \WP_REST_Request;
use \WP_Error;

/**
 * Activity feed controller.
 */
class Connection_Rest_Controller extends WP_REST_Controller {
    /**
     * The base to use in the API route.
     *
     * @var string
     */
    protected $base = 'connect';

    /**
     * The namespace for these routes.
     *
     * @var string
     */
    protected $namespace = 'peerraiser/v1';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, array(
            array(
                'methods'              => WP_REST_Server::READABLE,
                'callback'             => array( $this, 'confirm_connection' ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function confirm_connection( $request ) {
        $data = array( 'connected' => true );

        return new WP_REST_Response( $data, 200 );
    }
}