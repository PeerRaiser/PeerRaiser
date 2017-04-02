<?php

namespace PeerRaiser\Model;

use WP_Term_Query;

/**
 * Fundraiser Model
 *
 * Use this class to interact with PeerRaiser Fundraisers
 */
class Fundraiser {

	/**
	 * The Fundraiser ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	public    $ID  = 0;

	/**
	 * The Protected Fundraiser ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected $_ID = 0;

	/**
	 * New or existing fundraiser
	 *
	 * @since  1.0.0
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * Fundraiser name
	 *
	 * @var string
	 */
	protected $fundraiser_name = '';

	/**
	 * Fundraiser slug
	 *
	 * @var string
	 */
	protected $fundraiser_slug = '';

	/**
	 * Fundraiser Leader
	 *
	 * @var int
	 */
	protected $fundraiser_leader = 0;

	/**
	 * Fundraiser Description
	 *
	 * @var string
	 */
	protected $fundraiser_description = '';

	/**
	 * Thumbnail image
	 *
	 * @var string
	 */
	protected $thumbnail_image = '';

	/**
	 * Fundraiser goal
	 *
	 * @var float
	 */
	protected $fundraiser_goal = 0.00;

	/**
	 * Campaign ID
	 *
	 * @var int
	 */
	protected $campaign_id = 0;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_fundraiser_meta calls to be run
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private $pending;

	/**
	 * Setup fundraiser class
	 *
	 * @since 1.0.0
	 * @param int|boolean $id Fundraiser ID
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$id = absint( $id );

		// WP_Term_Query arguments
		$args = array(
			'taxonomy'               => array( 'peerraiser_fundraiser' ),
			'include'                => array( $id ),
			'number'                 => 1,
			'hide_empty'             => false,
		);

		$fundraiser = new WP_Term_Query( $args );

		if ( empty( $fundraiser->terms ) || is_wp_error( $fundraiser ) ) {
			return false;
		}

		$this->setup_fundraiser( $fundraiser->terms[0] );

		return $this;
	}

	/**
	 * Run when reading data from inaccessible properties.
	 *
	 * @since  1.0.0
	 * @param  string $key  The property
	 * @return mixed        The value
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			$value = call_user_func( array( $this, 'get_' . $key ) );
		} else {
			$value = $this->$key;
		}

		return $value;
	}

	/**
	 * Run when writing data to inaccessible properties.
	 *
	 * @since  1.0.0
	 * @param string $key   The property name
	 * @param mixed $value  The value of the property
	 */
	public function __set( $key, $value ) {
		$ignore = array( '_ID' );

		if ( ! in_array( $key, $ignore ) ) {
			$this->pending[ $key ] = $value;
		}

		if( '_ID' !== $key ) {
			$this->$key = $value;
		}
	}

	/**
	 * Run when isset() or empty() is called on inaccessible properties.
	 *
	 * @since  1.0.0
	 * @param  string  $name The attribute to get
	 * @return boolean       If the item is set or not
	 */
	public function __isset( $name ) {
		if ( property_exists( $this, $name) ) {
			return false === empty( $this->$name );
		} else {
			return null;
		}
	}

	/**
	 * Setup the fundraiser properties
	 *
	 * @since  1.0.0
	 * @param  object $fundraiser A fundraiser object
	 * @return bool             True if the setup worked, false if not
	 */
	private function setup_fundraiser( $fundraiser ) {
		$this->pending = array();

		// Perform your actions before the fundraiser is loaded with this hook:
		do_action( 'peerraiser_before_setup_fundraiser', $this, $fundraiser );

		// Primary Identifiers
		$this->ID			   = absint( $fundraiser->term_id );
		$this->_ID             = absint( $fundraiser->term_id);
		$this->fundraiser_name = $fundraiser->name;
		$this->fundraiser_slug = $fundraiser->slug;
		$this->campaign_id     = $fundraiser->campaign_id; // Get campaign ID from campaign term relationship

		// Money
		$this->fundraiser_goal = get_term_meta( $this->ID, '_peerraiser_fundraiser_goal', true );

		// Add your own items to this object via this hook:
		do_action( 'peerraiser_after_setup_fundraiser', $this, $fundraiser );

		return true;
	}

	/**
	 * Save information to the database
	 *
	 * @since 1.0.0
	 * @return bool  True of the save occurred, false if it failed
	 */
	public function save() {
		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				if ( property_exists( $this, $key ) ) {
					$this->update_meta( $key, $value );
					unset( $this->pending[ $key ] );
				} else {
					do_action( 'peerraiser_fundraiser_save', $this, $key );
				}
			}
		}

		do_action( 'peerraiser_fundraiser_saved', $this->ID, $this );

		$cache_key = md5( 'peerraiser_fundraiser_' . $this->ID );
		wp_cache_set( $cache_key, $this, 'fundraisers' );

		return true;
	}

	/**
	 * Delete the fundraiser
	 */
	public function delete() {
		wp_delete_term( $this->ID, 'peerraiser_fundraiser' );
	}

	/**
	 * Update fundraiser meta
	 *
	 * @since     1.0.0
	 * @param     string    $meta_key      Meta key to update
	 * @param     string    $meta_value    Meta value
	 * @param     string    $prev_value    Previous value
	 *
	 * @return    int|bool                 Meta ID if the key didn't exist, true on success, false on failure
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		return update_term_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	public function add_to_campaign( $campaign_id ) {
		$this->update_meta( '_peerraiser_campaign_id', $campaign_id );
	}

}
