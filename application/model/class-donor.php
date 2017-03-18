<?php

namespace PeerRaiser\Model;

use \PeerRaiser\Model\Database\Donor as Donor_Database;
use \PeerRaiser\Model\Database\Donor_Meta;

class Donor {

	/**
	 * The Donor ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	public    $ID  = 0;

	/**
	 * The Protected Donor ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected $_ID = 0;

	/**
	 * New or existing donor
	 *
	 * @since  1.0.0
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * The date the donor was created
	 *
	 * @since  1.0.0
	 * @var string
	 */
	protected $date = '';

	/**
	 * The User ID of the donor (if they have one)
	 *
	 * @since  1.0.0
	 * @var integer
	 */
	protected $user_id = 0;

	/**
	 * The name of the donor
	 *
	 * @since  1.0.0
	 * @var string
	 */
	protected $donor_name = '';

	/**
	 * The donor's primary email address
	 *
	 * @var string
	 */
	protected $email_address = '';

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_donor_meta calls to be run
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private $pending;

	/**
	 * Setup donor class
	 *
	 * @since  1.0.0
	 * @param  int|boolean $id Donor ID
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$donor_table = new Donor_Database();

		$args = array(
			'number'   => 1,
			'donor_id' => $id,
		);

		$donor = current( $donor_table->get_donors( $args ) );

		if ( empty( $donor ) ) {
			return false;
		}

		$this->setup_donor( $donor );

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
	 * Setup the donor properties
	 *
	 * @since  1.0.0
	 * @param  object $donor A donor object
	 *
	 * @return bool True if the setup worked, false if not
	 */
	private function setup_donor( $donor ) {
		$this->pending = array();

		// Perform your actions before the donor is loaded with this hook:
		do_action( 'peerraiser_before_setup_donor', $this, $donor );

		// Primary Identifier
		$this->ID = absint( $donor->donor_id );

		// Protected ID (can't be changed)
		$this->_ID = absint( $donor->donor_id);

		$this->date    = $donor->date;
		$this->user_id = $this->setup_user_id();

		// Add your own items to this object via this hook:
		do_action( 'peerraiser_after_setup_donor', $this, $donor );

		return true;
	}

	/**
	 * Creates a donor record in the database
	 *
	 * @since     1.0.0
	 * @return    int    Donor ID
	 */
	private function insert_donor() {
		if ( empty( $this->date ) ) {
			$this->date = current_time( 'timestamp' );
		}

		$donor_table = new Donor_Database();
		$donor_id    = $donor_table->add_donor( $this );

		$this->ID  = $donor_id;
		$this->_ID = $donor_id;

		return $this->ID;
	}

	/**
	 * Save information to the database
	 *
	 * @since 1.0.0
	 * @return bool  True of the save occurred, false if it failed
	 */
	public function save() {
		if ( empty( $this->ID ) ) {
			$this->insert_donor();
		}

		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				switch( $key ) {
					default :
						do_action( 'peerraiser_donor_save', $this, $key );
						break;
				}
			}
		}

		do_action( 'peerraiser_donor_saved', $this->ID, $this );

		$cache_key = md5( 'peerraiser_donor_' . $this->ID );
		wp_cache_set( $cache_key, $this, 'donors' );

		return true;
	}

	public function delete() {
		$donor_table = new Donor_Database();
		$donor_table->delete( $this->ID );
	}

	/**
	 * Update donor meta
	 *
	 * @since     1.0.0
	 * @param     string    $meta_key      Meta key to update
	 * @param     string    $meta_value    Meta value
	 * @param     string    $prev_value    Previous value
	 * @return    int|bool                 Meta ID if the key didn't exist, true on success, false on failure
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		$meta_value = apply_filters( 'peerraiser_update_donor_meta_' . $meta_key, $meta_value, $this->ID );

		$donor_meta = new Donor_Meta();

		$result = $donor_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value);
	}

	private function setup_user_id( ) {

	}
}
