<?php

namespace PeerRaiser\Controller\Api;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Response;
use \WP_REST_Request;
use \WP_Error;

/**
 * Connection controller.
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
				'methods'              => 'POST, GET',
				'callback'             => array( $this, 'confirm_connection' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Confirm the connection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function confirm_connection( $request ) {
		$params         = $request->get_params();
        $plugin_options = get_option( 'peerraiser_options', array() );
		$license_key    = $plugin_options['license_key'];
		$slug           = $params['slug'];

		if ( empty( $slug ) || empty( $license_key ) ) {
			return new WP_REST_Response( array( 'valid' => false ), 404 );
		}

		$response = wp_remote_post( add_query_arg(
		    array(
                'slug' => esc_attr( $slug ),
                'license_key' => esc_attr( $license_key ),
            ),
		    'https://peerraiser.com/wp-json/heart/v1/connect' ),
			array( 'sslverify' => false )
        );

        if ( is_wp_error( $response ) ) {
            error_log( print_r( $response, 1) );
        }

        $response_body = json_decode( $response['body'] );

        if ( $response_body->valid === true ) {
            update_option( 'peerraiser_slug', $slug );
        }

		$data = array( 'connected' => $response_body->valid );

		return new WP_REST_Response( $data, 200 );
	}
}
