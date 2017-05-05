<?php

namespace PeerRaiser\Model;

use WP_User;
use WP_User_Query;

/**
 * Team Model
 *
 * Use this class to interact with PeerRaiser Participants
 */
class Participant {

	/**
	 * The User ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	public    $ID  = 0;

	/**
	 * The Protected User ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected $_ID = 0;

	/**
	 * The username
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected    $username  = '';

	/**
	 * The password
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected    $password  = '';

	/**
	 * The first name of the participant
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $first_name = '';

	/**
	 * The last name of the participant
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $last_name = '';

	/**
	 * The participant's full name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $full_name = '';

	/**
	 * The participant's primary email address
	 *
	 * @var string
	 */
	protected $email_address = '';

	/**
	 * The total amount the participant has raised
	 *
	 * @var float
	 */
	protected $donation_value = 0.00;

	/**
	 * The number of donations the participant has received
	 *
	 * @var int
	 */
	protected $donation_count = 0;

	/**
	 * Date the participant started.
	 *
	 * Not necessarily when the user account was created though.
	 *
	 * @var string
	 */
	protected $date = '';

	/**
	 * Array of items that have changed since the last save() was run
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $pending;

	/**
	 * Setup donor class
	 *
	 * @since 1.0.0
	 * @param  int|boolean $id Donor ID
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$user = get_user_by( 'id', $id );

		if ( ! $user ) {
			return false;
		}

		$this->setup_participant( $user );

		return $this;
	}

	/**
	 * Run when reading data from inaccessible properties.
	 *
	 * @since 1.0.0
	 * @param  string $key  The property
	 * @return mixed        The value
	 */
	public function __get( $key ) {
		// Password can be set, but not retrieved
		if ( $key === 'password' ) {
			return false;
		}

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
	 * @since 1.0.0
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
	 * @since 1.0.0
	 * @param  string  $name The attribute to get
	 * @return boolean       If the item is set or not
	 */
	public function __isset( $name ) {
		if ( property_exists( $this, $name ) ) {
			return false === empty( $this->$name );
		} elseif ( method_exists( $this, 'get_' . $name ) ) {
			return false === empty( call_user_func( array( $this, 'get_' . $name ) ) );
		} else {
			return null;
		}
	}

	/**
	 * Setup the participant properties
	 *
	 * @since 1.0.0
	 * @param  WP_User $user A WP_User object
	 *
	 * @return bool True if the setup worked, false if not
	 */
	private function setup_participant( WP_User $user ) {
		$this->pending = array();

		// Perform your actions before the participant is loaded with this hook:
		do_action( 'peerraiser_before_setup_participant', $this, $user );

		// Primary Identifier
		$this->ID = absint( $user->ID );

		// Protected ID (can't be changed)
		$this->_ID = absint( $user->ID);

		$this->username       = $user->user_login;
		$this->first_name     = $user->first_name;
		$this->last_name      = $user->last_name;
		$this->full_name      = trim( $this->first_name . ' ' . $this->last_name );
		$this->email_address  = $user->user_email;
		$this->date           = get_user_meta( $user->ID, '_peerraiser_date', true );
		$this->donation_count = get_user_meta( $user->ID, '_peerraiser_donation_count', true );
		$this->donation_value = get_user_meta( $user->ID, '_peerraiser_donation_value', true );

		// Add your own items to this object via this hook:
		do_action( 'peerraiser_after_setup_participant', $this, $user );

		return true;
	}

	/**
	 * Creates a participant/user record
	 *
	 * @since     1.0.0
	 * @return    int    Donor ID
	 */
	private function insert_participant() {
		if ( empty( $this->date ) ) {
			$this->date            = current_time( 'mysql' );
			$this->pending['date'] = $this->date;
		}

		$exists = $this->maybe_attach_user();

		if ( ! $exists ) {
			$user_id = wp_create_user( $this->username, $this->password, $this->email_address );

			$this->ID  = $user_id;
			$this->_ID = $user_id;
		}

		wp_set_object_terms( $this->ID, array( 'participant' ), 'peerraiser_group', true );
		clean_object_term_cache( $this->ID, 'peerraiser_group' );

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
			$this->insert_participant();
		}

		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		$updated = array();

		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				if ( property_exists( $this, $key ) ) {
					$this->update_meta( '_peerraiser_' . $key, $value );
					$updated[$key] = $value;
				} else {
					do_action( 'peerraiser_participant_save', $this, $key );
				}
			}
		}

		do_action( 'peerraiser_participant_saved', $this, $updated );

		$cache_key = md5( 'peerraiser_participant_' . $this->ID );
		wp_cache_set( $cache_key, $this, 'participants' );

		return true;
	}

	/**
	 * Delete this participant
	 */
	public function delete() {
		do_action( 'peerraiser_participant_delete', $this );

		// Don't actually delete the user. Keeping this here for now though:
		// wp_delete_user( $this->ID );

		// Remove the user from the 'participant' peerraiser group taxonomy
		wp_remove_object_terms( $this->ID, array( 'participant' ), 'peerraiser_group' );
		clean_object_term_cache( $this->ID, 'peerraiser_group' );

		do_action( 'peerraiser_participant_delete', $this );
	}

	/**
	 * Get participant meta
	 *
	 * @param string $meta_key
	 * @param bool $single
	 *
	 * @return mixed
	 */
	public function get_meta( $meta_key= '', $single = false ) {
		return get_user_meta( $this->ID, $meta_key, $single );
	}

	/**
	 * Update participant/user meta
	 *
	 * @since     1.0.0
	 * @param     string    $meta_key      Meta key to update
	 * @param     string    $meta_value    Meta value
	 * @param     string    $prev_value    Previous value
	 *
	 * @return    int|bool                 Meta ID if the key didn't exist, true on success, false on failure
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		return update_user_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Delete participant meta
	 *
	 * @since     1.0.0
	 * @param     string    $meta_key      Meta key to update
	 * @param     string    $meta_value    Meta value
	 * @return    int|bool                 Optional. Metadata value.
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return delete_user_meta( $this->ID, $meta_key, $meta_value );
	}

	/**
	 * Get Participants
	 *
	 * @param array $args  Optional set of arguments
	 * @param bool  $total Get the total number of participants?
	 *
	 * @return array|int|\WP_Error|WP_User_Query
	 */
	public function get_participants( $args = array(), $total = false ) {
		$participant_term = get_term_by( 'slug', 'participant', 'peerraiser_group' );
		$participant_ids  = get_objects_in_term( $participant_term->term_id, 'peerraiser_group' );

		if ( $total || empty( $participant_ids ) ) {
			return $total ? count( $participant_ids ) : $participant_ids;
		}

		$defaults = array(
			'include'     => $participant_ids,
			'count_total' => false,
			'number'      => -1,
			'offset'      => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$participants = new WP_User_Query( $args );

		return $participants->results;
	}

	/**
	 * Get the total number of participants
	 *
	 * @return int Number of participants
	 */
	public function get_total_participants() {
		return (int) $this->get_participants( array(), true );
	}

	/**
	 * Increase the donation count of the participant
	 *
	 * @since 1.0.0
	 * @param  integer $count The number to increment by
	 *
	 * @return int The donation count
	 */
	public function increase_donation_count( $count = 1 ) {
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->donation_count + (int) $count;

		do_action( 'peerraiser_participant_pre_increase_donation_count', $count, $this->ID );

		if ( $this->update_meta( '_peerraiser_donation_count', $new_total ) ) {
			$this->donation_count = $new_total;
		}

		do_action( 'peerraiser_participant_post_increase_donation_count', $this->donation_count, $count, $this->ID );

		return $this->donation_count;
	}

	/**
	 * Decrease the participant donation count
	 *
	 * @since 1.0.0
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

		if ( $new_total < 0 ) {
			$new_total = 0;
		}

		do_action( 'peerraiser_participant_pre_decrease_donation_count', $count, $this->ID );

		if ( $this->update_meta( '_peerraiser_donation_count', $new_total ) ) {
			$this->donation_count = $new_total;
		}

		do_action( 'peerraiser_participant_post_decrease_donation_count', $this->donation_count, $count, $this->ID );

		return $this->donation_count;
	}

	/**
	 * Increase the customer's lifetime value
	 *
	 * @since 1.0.0
	 * @param  float $value The value to increase by
	 *
	 * @return mixed If successful, the new value, otherwise false
	 */
	public function increase_value( $value = 0.00 ) {
		$value = apply_filters( 'peerraiser_participant_increase_value', $value, $this );

		$new_value = floatval( $this->donation_value ) + $value;

		do_action( 'peerraiser_participant_pre_increase_value', $value, $this->ID, $this );


		if ( $this->update_meta( '_peerraiser_donation_value', $new_value ) ) {
			$this->donation_value = $new_value;
		}

		do_action( 'peerraiser_participant_post_increase_value', $this->donation_value, $value, $this->ID, $this );

		return $this->donation_value;
	}

	/**
	 * Decrease a participant's lifetime value
	 *
	 * @since 1.0.0
	 * @param  float  $value The value to decrease by
	 *
	 * @return mixed If successful, the new value, otherwise false
	 */
	public function decrease_value( $value = 0.00 ) {
		$value = apply_filters( 'peerraiser_participant_decrease_value', $value, $this );

		$new_value = floatval( $this->donation_value ) - $value;

		if( $new_value < 0 ) {
			$new_value = 0.00;
		}

		do_action( 'peerraiser_participant_pre_decrease_value', $value, $this->ID, $this );

		if ( $this->update_meta( array( 'donation_value' => $new_value ) ) ) {
			$this->donation_value = $new_value;
		}

		do_action( 'peerraiser_participant_post_decrease_value', $this->donation_value, $value, $this->ID, $this );

		return $this->donation_value;
	}

	/**
	 * Check if a user account exists with the participants email address
	 *
	 * @return bool|int False if user doesn't exist, user id if it does
	 */
	private function maybe_attach_user() {
		$user = get_user_by( 'email', $this->email_address );

		if ( ! $user ) {
			return false;
		}

		$this->ID  = $user->ID;
		$this->_ID = $user->ID;

		return $this->ID;
	}

}