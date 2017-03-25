<?php

namespace PeerRaiser\Model;

use WP_Term_Query;

/**
 * Team Model
 *
 * Use this class to interact with PeerRaiser Teams
 */
class Team {

	/**
	 * The Team ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	public    $ID  = 0;

	/**
	 * The Protected Team ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected $_ID = 0;

	/**
	 * New or existing team
	 *
	 * @since  1.0.0
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * Team name
	 *
	 * @var string
	 */
	protected $team_name = '';

	/**
	 * Team slug
	 *
	 * @var string
	 */
	protected $team_slug = '';

	/**
	 * Team Leader
	 *
	 * @var int
	 */
	protected $team_leader = 0;

	/**
	 * Team Description
	 *
	 * @var string
	 */
	protected $team_description = '';

	/**
	 * Thumbnail image
	 *
	 * @var string
	 */
	protected $thumbnail_image = '';

	/**
	 * Team goal
	 *
	 * @var float
	 */
	protected $team_goal = 0.00;

	/**
	 * Campaign ID
	 *
	 * @var int
	 */
	protected $campaign_id = 0;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_team_meta calls to be run
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private $pending;

	/**
	 * Setup team class
	 *
	 * @since 1.0.0
	 * @param int|boolean $id Team ID
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$id = absint( $id );

		// WP_Term_Query arguments
		$args = array(
			'taxonomy'               => array( 'peerraiser_team' ),
			'include'                => array( $id ),
			'number'                 => 1,
			'hide_empty'             => false,
		);

		$team = new WP_Term_Query( $args );

		if ( empty( $team->terms ) || is_wp_error( $team ) ) {
			return false;
		}

		error_log( '$team->terms[0]: ' . print_r( $team->terms[0], 1 ) );

		$this->setup_team( $team->terms[0] );

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
	 * Setup the team properties
	 *
	 * @since  1.0.0
	 * @param  object $team A team object
	 * @return bool             True if the setup worked, false if not
	 */
	private function setup_team( $team ) {
		$this->pending = array();

		// Perform your actions before the team is loaded with this hook:
		do_action( 'peerraiser_before_setup_team', $this, $team );

		// Primary Identifiers
		$this->ID			 = absint( $team->term_id );
		$this->_ID           = absint( $team->term_id);
		$this->team_leader   = absint( get_term_meta( $this->ID, '_peerraiser_team_leader', true ) );
		$this->team_name     = $team->name;
		$this->team_slug     = $team->slug;
		$this->campaign_id   = $team->campaign_id; // Get campaign ID from campaign term relationship

		// Team content
		$this->team_description = get_term_meta( $this->ID, '_peerraiser_team_description', true );
		$this->thumbnail_image  = get_term_meta( $this->ID, '_peerraiser_thumbnail_image', true );

		// Money
		$this->team_goal = get_term_meta( $this->ID, '_peerraiser_team_goal', true );

		// Add your own items to this object via this hook:
		do_action( 'peerraiser_after_setup_team', $this, $team );

		return true;
	}

	/**
	 * Creates a team record in the database
	 *
	 * @since     1.0.0
	 * @return    int    Team ID
	 */
	private function insert_team() {
		if ( empty( $this->team_slug ) ) {
			$this->team_slug = $this->generate_team_slug();
		}

		$team = wp_insert_term(
			$this->team_name,
			'peerraiser_team',
			$args = array(
				'slug' => $this->team_slug,
			) );

		$this->ID  = $team['term_id'];
		$this->_ID = $team['term_id'];

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
			$this->insert_team();
		}

		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				if ( property_exists( $this, $key ) ) {
					$this->update_meta( $key, $value );
					unset( $this->pending[ $key ] );
				} else {
					do_action( 'peerraiser_team_save', $this, $key );
				}
			}
		}

		do_action( 'peerraiser_team_saved', $this->ID, $this );

		$cache_key = md5( 'peerraiser_team_' . $this->ID );
		wp_cache_set( $cache_key, $this, 'teams' );

		return true;
	}

	/**
	 * Delete the team
	 */
	public function delete() {
		wp_delete_term( $this->ID, 'peerraiser_team' );
	}

	/**
	 * Update team meta
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

	/**
	 * Generate a safe team slug
	 *
	 * @since     1.0.0
	 * @return    string    team slug
	 */
	private function generate_team_slug() {
		$team_title = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities( wp_strip_all_tags( $this->team_name ) ) );
		return sanitize_title_with_dashes( $team_title, null, 'save' );
	}
}
