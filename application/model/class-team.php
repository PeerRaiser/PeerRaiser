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
     * The total amount the team has received
     *
     * @var float
     */
    protected $donation_value = 0.00;

    /**
     * The number of donations the team has received
     *
     * @var int
     */
    protected $donation_count = 0;

	/**
	 * Date Team Created
	 *
	 * $var string
	 */
	protected $created = '';

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
			'taxonomy'   => array( 'peerraiser_team' ),
			'include'    => array( $id ),
			'number'     => 1,
			'hide_empty' => false,
		);

		$team = new WP_Term_Query( $args );

		if ( empty( $team->terms ) || is_wp_error( $team ) ) {
			return false;
		}

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
		$this->ID          = absint( $team->term_id );
		$this->_ID         = absint( $team->term_id );
		$this->team_leader = absint( get_term_meta( $this->ID, '_peerraiser_team_leader', true ) );
		$this->team_name   = $team->name;
		$this->team_slug   = $team->slug;
		$this->campaign_id = absint( get_term_meta( $this->ID, '_peerraiser_campaign_id', true ) );

		// Team content
		$this->team_description = get_term_meta( $this->ID, '_peerraiser_team_description', true );
		$this->thumbnail_image  = get_term_meta( $this->ID, '_peerraiser_thumbnail_image', true );

		// Money
		$this->team_goal      = get_term_meta( $this->ID, '_peerraiser_team_goal', true );
		$donation_value       = get_term_meta( $this->ID, '_peerraiser_donation_value', true );
		$this->donation_value = $donation_value ? floatval( $donation_value ) : 0.00;
		$donation_count       = get_term_meta( $this->ID, '_peerraiser_donation_count', true );
		$this->donation_count = $donation_count ? intval( $donation_count ) : 0;

        // Team info
		$this->created = get_term_meta( $this->ID, '_peerraiser_created', true );

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

		if ( empty( $this->created ) ) {
			$this->created            = current_time( 'mysql' );
			$this->pending['created'] = $this->created;
		}

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
					$this->update_meta( '_peerraiser_' . $key, $value );
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
        do_action( 'peerraiser_team_delete]', $this );

		wp_delete_term( $this->ID, 'peerraiser_team' );

        do_action( 'peerraiser_team_deleted', $this );
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

	/**
	 * Get team meta
	 *
	 * @param string $meta_key The meta key to retrieve. By default, returns data for all keys.
	 * @param bool   $single   Whether to return a single value
	 *
	 * @return mixed
	 */
	public function get_meta( $meta_key = '', $single = false ) {
		return get_term_meta( $this->ID, $meta_key, $single );
	}

	/**
	 * Removes team meta
	 *
	 * @since 1.0.0
	 * @param string $meta_key   Metadata name.
	 * @param mixed  $meta_value Optional. Metadata value. If provided, rows will only be removed that match the value.
	 *
	 * @return bool True on success, false on failure.
	 */
	function delete_meta( $meta_key, $meta_value = '' ) {
		return delete_term_meta( $this->ID, $meta_key, $meta_value );
	}

    /**
     * Add the team to a campaign
     *
     * @param $campaign_id int The campaign to add the team to
     */
	public function add_to_campaign( $campaign_id ) {
		$this->update_meta( '_peerraiser_campaign_id', $campaign_id );
	}

	/**
	 * Returns the total number of teams
	 *
	 * @return array|int|\WP_Error
	 */
	public function get_total_teams() {
		return wp_count_terms( 'peerraiser_team', array( 'hide_empty' => false ) );
	}

	public function get_teams( $args = array() ) {
		$defaults = array(
			'count'      => 20,
			'offset'     => 0,
			'hide_empty' => false,
			'taxonomy'   => array( 'peerraiser_team' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$term_query = new WP_Term_Query( $args );

		$results = array();

		if ( ! empty( $term_query->terms ) ) {
			foreach ( $term_query->terms as $term ) {
				$results[] = new self( $term->term_id );
			}
		}

		return $results;
	}

	/**
	 * Update the team name
	 *
	 * @since  1.0.0
	 * @param string $name Team name
	 * @param bool   $slug Team slug
	 */
	public function update_team_name( $name, $slug = false ) {
		$args = array(
			'name' => $name,
			'slug' => $slug ? $slug : $this->team_slug,
		);

		wp_update_term( $this->ID, 'peerraiser_team', $args );
	}

    /**
     * Increase the donation count of the team
     *
     * @since  1.0.0
     * @param  integer $count The number to increment by
     *
     * @return int The donation count
     */
    public function increase_donation_count( $count = 1 ) {
        if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
            return false;
        }

        $new_total = (int) $this->donation_count + (int) $count;

        do_action( 'peerraiser_team_pre_increase_donation_count', $count, $this->ID );

        $this->update_meta( '_peerraiser_donation_count', $new_total );
        $this->donation_count = $new_total;

        do_action( 'peerraiser_team_post_increase_donation_count', $this->donation_count, $count, $this->ID );

        return $this->donation_count;
    }

    /**
     * Decrease the team donation count
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

        if ( $new_total < 0 ) {
            $new_total = 0;
        }

        do_action( 'peerraiser_team_pre_decrease_donation_count', $count, $this->ID );

        $this->update_meta( '_peerraiser_donation_count', $new_total );
        $this->donation_count = $new_total;

        do_action( 'peerraiser_team_post_decrease_donation_count', $this->donation_count, $count, $this->ID );

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
        $value = apply_filters( 'peerraiser_team_increase_value', $value, $this );

        $new_value = floatval( $this->donation_value ) + $value;

        do_action( 'peerraiser_team_pre_increase_value', $value, $this->ID, $this );

        $this->update_meta( '_peerraiser_donation_value', $new_value );
        $this->donation_value = $new_value;

        do_action( 'peerraiser_team_post_increase_value', $this->donation_value, $value, $this->ID, $this );

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
        $value = apply_filters( 'peerraiser_team_decrease_value', $value, $this );

        $new_value = floatval( $this->donation_value ) - $value;

        if( $new_value < 0 ) {
            $new_value = 0.00;
        }

        do_action( 'peerraiser_team_pre_decrease_value', $value, $this->ID, $this );

        $this->update_meta( '_peerraiser_donation_value', $new_value );
        $this->donation_value = $new_value;

        do_action( 'peerraiser_team_post_decrease_value', $this->donation_value, $value, $this->ID, $this );

        return $this->donation_value;
    }

    public function get_total_members() {
	    $term = get_term( $this->ID, 'peerraiser_team' );
	    return $term->count;
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
