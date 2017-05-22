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
class Donation_Rest_Controller extends WP_REST_Controller {
	/**
	 * The base to use in the API route.
	 *
	 * @var string
	 */
	protected $base = 'donation';

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
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( false ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->base . '/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = array(); //do a query, call another class, etc
		$data = array();
		foreach( $items as $item ) {
			$itemdata = $this->prepare_item_for_response( $item, $request );
			$data[] = $this->prepare_response_for_collection( $itemdata );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		//get parameters from request
		$params = $request->get_params();
		$item = array();//do a query, call another class, etc
		$data = $this->prepare_item_for_response( $item, $request );

		//return a response or error based on some conditional
		if ( 1 == 1 ) {
			return new WP_REST_Response( $data, 200 );
		}else{
			return new WP_Error( 'code', __( 'message', 'peerraiser' ) );
		}
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$donation = new \PeerRaiser\Model\Donation();

		// Required Fields
		$donation->donor_id      = absint( $request['donor_id'] );
		$donation->total         = $request['total'];
		$donation->subtotal      = $request['subtotal'];
		$donation->campaign_id   = absint( $request['campaign_id'] );
		$donation->status        = $request['status'];
		$donation->donation_type = $request['donation_type'];
		$donation->gateway       = 'peerraiser';
		$donation->fundraiser_id = $request['fundraiser_id'];

		$donation->add_note( $request['donation_note'] );

		$donation->save();

		$data = array(
			'donation_id' => $donation->ID
		);

		if ( is_array( $data ) ) {
			return new WP_REST_Response( $data, 200 );
		}

		return new WP_Error( 'cant-create', __( 'Donation could not be created', 'peerraiser'), array( 'status' => 500 ) );
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		if ( function_exists( 'slug_some_function_to_update_item')  ) {
			$data = slug_some_function_to_update_item( $item );
			if ( is_array( $data ) ) {
				return new WP_REST_Response( $data, 200 );
			}
		}

		return new WP_Error( 'cant-update', __( 'message', 'peerraiser'), array( 'status' => 500 ) );
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		if ( function_exists( 'slug_some_function_to_delete_item')  ) {
			$deleted = slug_some_function_to_delete_item( $item );
			if (  $deleted  ) {
				return new WP_REST_Response( true, 200 );
			}
		}

		return new WP_Error( 'cant-delete', __( 'message', 'peerraiser'), array( 'status' => 500 ) );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_donations' );
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		// TODO: Send request to PeerRaiser.com to validate
		return true;
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		return array();
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return array();
	}

	/**
	 * Get the item's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'donation',
			'type'       => 'object',
			'properties' => array(
				'donation_id' => array(
					'description' => __( 'Unique identifier for the donation.', 'peerraiser' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'donor_id' => array(
					'description' => __( 'Unique identifier for the donor.', 'peerraiser' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'total' => array(
					'description' => __( 'The total amount of the donation.', 'peerraiser' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'subtotal' => array(
					'description' => __( 'Donation amount before additions and subtractions.', 'peerraiser' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'campaign_id' => array(
					'description' => __( 'Unique identifier for the campaign being donated to.', 'peerraiser' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'status' => array(
					'description' => __( 'The status of the donation.', 'peerraiser' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'donation_type' => array(
					'description' => __( 'The type of donation.', 'peerraiser' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit'),
				),
				'gateway' => array(
					'description' => __( 'The merchant that authorized the donation.', 'peerraiser' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'fundraiser_id' => array(
					'description' => __( 'Unique identifier for the fundraiser being donated to.', 'peerraiser' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'donation_notes'          => array(
					'description' => 'Internal notes to store about the donation.',
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}