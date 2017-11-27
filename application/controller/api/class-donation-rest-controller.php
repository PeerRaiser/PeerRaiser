<?php

namespace PeerRaiser\Controller\Api;

use PeerRaiser\Controller\Activity_Feed;
use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Currency;
use PeerRaiser\Model\Donation;
use PeerRaiser\Model\Donor;
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
        // register_rest_route( $this->namespace, '/' . $this->base, array(
        // 	array(
        // 		'methods'              => WP_REST_Server::CREATABLE,
        // 		'callback'             => array( $this, 'create_item' ),
        // 		'permission_callback'  => array( $this, 'create_item_permissions_check' ),
        // 		'args'                 => $this->get_endpoint_args_for_item_schema( true ),
        // 	),
        // 	'schema' => array( $this, 'get_public_item_schema' ),
        // ) );
        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<key>\S{32})', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'                => $this->get_endpoint_args_for_item_schema( false ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<key>\S{32})', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'                => array(),
            )
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
        $params   = $request->get_params();
        $item     = array();
        $donation = new Donation();

        $donation = $donation->get_donation( $params['key'], 'transaction_id' );

        if ( ! is_wp_error( $donation ) ) {
            $plugin_options = get_option( 'peerraiser_options', array() );
            $donor          = new Donor( $donation->donor_id );
            $campaign       = new Campaign( $donation->campaign_id );
            $currency_model = new Currency();
            $currency       = $plugin_options['currency'];

            $item['total']             = $donation->total;
            $item['total_formatted']   = peerraiser_money_format( $donation->total );
            $item['first_name']        = $donor->first_name;
            $item['last_name']         = $donor->last_name;
            $item['allow_fees_paid']   = $campaign->allow_fees_covered;
            $item['thank_you_page']    = get_permalink( $campaign->thank_you_page );
            $item['test_mode']         = filter_var( peerraiser_get_option( 'test_mode' ), FILTER_VALIDATE_BOOLEAN );
            $item['thousands_sep']     = $plugin_options['thousands_separator'];
            $item['currency_position'] = $plugin_options['currency_position'];
            $item['decimal_sep']       = $plugin_options['decimal_separator'];
            $item['number_decimals']   = $plugin_options['number_decimals'];
            $item['currency']          = $currency;
            $item['currency_symbol']   = $currency_model->get_currency_symbol_by_iso4217_code( $currency );
            $item['donor_id'] 		   = $donation->donor_id;
        }

        $data = $this->prepare_item_for_response( $item, $request );

        //return a response or error based on some conditional
        if ( 1 == 1 ) {
            return new WP_REST_Response( $data, 200 );
        } else{
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
        $params = $request->get_params();
        $item   = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $item ) ) {
            return new WP_Error( 'cant-update', __( 'Donation not found', 'peerraiser'), array( 'status' => 500 ) );
        }

        $item->transaction_id = $params['transaction_id'];
        $item->subtotal       = $params['subtotal'];
        $item->total          = $params['total'];
        $item->status         = $params['status'];
        $item->add_note( __( 'Donation completed!', 'peerraiser' ), __( 'PeerRaiser Bot', 'peerraiser' ) );
        $item->save();

        $data = array(
            'success' => true
        );

        return new WP_REST_Response( $data, 200 );
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
        return true;
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_item_permissions_check( $request ) {
        return true;
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
     * @return WP_Error|object|array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $params   = $request->get_params();
        $donation = new Donation();

        $donation = $donation->get_donation( $params['key'], 'transaction_id' );

        return $donation;
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response( $item, $request ) {
        $whitelist = array(
            'total',
            'total_formatted',
            'first_name',
            'last_name',
            'allow_fees_paid',
            'thank_you_page',
            'test_mode',
            'currency',
            'currency_symbol',
            'thousands_sep',
            'currency_position',
            'decimal_sep',
            'number_decimals',
            'donor_id',
        );

        return array_intersect_key( $item, array_flip( $whitelist ) );
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
                'total_formatted' => array(
                    'description' => __( 'The total amount of the donation, formatted with currency symbol.', 'peerraiser' ),
                    'type'        => 'string',
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