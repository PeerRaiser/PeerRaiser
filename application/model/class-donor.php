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
	 * The total amount the donor has donated
	 *
	 * @var float
	 */
	protected $donation_value = 0.00;

	/**
	 * The number of donations the donor has made
	 *
	 * @var int
	 */
	protected $donation_count = 0;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_donor_meta calls to be run
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private $pending;

	/**
	 * The donor database
	 */
	protected $db;

	/**
	 * Setup donor class
	 *
	 * @since  1.0.0
	 * @param  int|boolean $id Donor ID
	 */
	public function __construct( $id = false ) {
		$this->db = new Donor_Database();

		if ( empty( $id ) ) {
			return false;
		}

		$args = array(
			'number'   => 1,
			'donor_id' => $id,
		);

		$donor = current( $this->db->get_donors( $args ) );

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

		$this->donor_name     = $donor->donor_name;
		$this->email_address  = $donor->email_address;
		$this->date           = $donor->date;
		$this->donation_count = $donor->donation_count;
		$this->user_id        = $donor->user_id;

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

		$donor_id = $this->db->add_donor( $this );

		$this->ID  = $donor_id;
		$this->_ID = $donor_id;

		return $this->ID;
	}

	/**
	 * Update a donor record
	 *
	 * @since 1.0.0
	 * @param array $data Array of data attributes for a donor
	 *
	 * @return bool If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		do_action( 'peerraiser_donor_pre_update', $this->ID, $data );

		$updated = false;

		if ( $this->db->update( $this->ID, $data ) ) {

			$donor = $this->db->get_donors( array( 'donor_id' => $this->ID ) );
			$this->setup_donor( current( $donor ) );

			$updated = true;
		}

		do_action( 'peerraiser_donor_post_update', $updated, $this->ID, $data );

		return $updated;
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

		// Attempt to connect donor to existing user
		if ( 0 === $this->user_id) {
			$this->user_id = $this->maybe_connect_user();
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
		$this->db->delete( $this->ID );
	}

	/**
	 * Increase the donation count of the donor
	 *
	 * @since  1.0.0
	 * @param  integer $count The number to increment by
	 *
	 * @return int The purchase count
	 */
	public function increase_donation_count( $count = 1 ) {
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->donation_count + (int) $count;

		do_action( 'peerraiser_donor_pre_increase_donation_count', $count, $this->ID );

		if ( $this->update( array( 'donation_count' => $new_total ) ) ) {
			$this->donation_count = $new_total;
		}

		do_action( 'peerraiser_donor_post_increase_donation_count', $this->donation_count, $count, $this->ID );

		return $this->donation_count;
	}

	/**
	 * Decrease the donor donation count
	 *
	 * @since  1.0.0
	 * @param  integer $count The amount to decrease by
	 *
	 * @return mixed If successful, the new count, otherwise false
	 */
	public function decrease_donation_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->donation_count - (int) $count;

		if( $new_total < 0 ) {
			$new_total = 0;
		}

		do_action( 'peerraiser_donor_pre_decrease_donation_count', $count, $this->ID );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->donation_count = $new_total;
		}

		do_action( 'peerraiser_customer_post_decrease_purchase_count', $this->donation_count, $count, $this->ID );

		return $this->donation_count;
	}

	/**
	 * Increase the customer's lifetime value
	 *
	 * @since  1.0.0
	 * @param  float $value The value to increase by
	 *
	 * @return mixed If successful, the new value, otherwise false
	 */
	public function increase_value( $value = 0.00 ) {
		$value = apply_filters( 'peerraiser_donor_increase_value', $value, $this );

		$new_value = floatval( $this->donation_value ) + $value;

		do_action( 'peerraiser_donor_pre_increase_value', $value, $this->ID, $this );

		if ( $this->update( array( 'donation_value' => $new_value ) ) ) {
			$this->donation_value = $new_value;
		}

		do_action( 'peerraiser_donor_post_increase_value', $this->donation_value, $value, $this->ID, $this );

		return $this->donation_value;
	}

	/**
	 * Decrease a customer's lifetime value
	 *
	 * @since  1.0.0
	 * @param  float  $value The value to decrease by
	 *
	 * @return mixed If successful, the new value, otherwise false
	 */
	public function decrease_value( $value = 0.00 ) {
		$value = apply_filters( 'peerraiser_donor_decrease_value', $value, $this );

		$new_value = floatval( $this->donation_value ) - $value;

		if( $new_value < 0 ) {
			$new_value = 0.00;
		}

		do_action( 'peerraiser_donor_pre_decrease_value', $value, $this->ID, $this );

		if ( $this->update( array( 'donation_value' => $new_value ) ) ) {
			$this->donation_value = $new_value;
		}

		do_action( 'peerraiser_donor_post_decrease_value', $this->donation_value, $value, $this->ID, $this );

		return $this->donation_value;
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

		return $donor_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value);
	}

	/**
	 * Attempt to get the donor's username by their email address
	 *
	 * @return int User ID (0 if no user found)
	 */
	private function maybe_connect_user( ) {
		if ( ! is_email( $this->email_address ) ) {
			return 0;
		}

		$donation_table = new Donor_Database();
		$donor_count = $donation_table->count( array( 'email_address' => $this->email_address ) );

		// If there's already a donor using this email address
		if ( $donor_count > 0 ) {
			return 0;
		}

		$user = get_user_by( 'email', $this->email_address );

		return ! empty( $user ) ? $user->ID : 0;
	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  1.0.0
	 * @param  array $data The data to sanitize
	 *
	 * @return array The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {
		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch( $type ) {

				case '%s':
					if ( 'email_address' == $key ) {
						$data[$key] = sanitize_email( $data[$key] );
					} else {
						$data[$key] = sanitize_text_field( $data[$key] );
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[$key] ) || (int) $data[$key] !== absint( $data[$key] ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = absint( $data[$key] );
					}
					break;

				case '%f':
					$value = floatval( $data[$key] );

					if ( ! is_float( $value ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;

				default:
					$data[$key] = sanitize_text_field( $data[$key] );
					break;
			}

		}

		return $data;
	}
}
