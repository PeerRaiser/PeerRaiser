<?php

namespace PeerRaiser\Model;

use WP_Query;

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
     * Team ID
     *
     * @var int
     */
    protected $team_id = 0;

    /**
     * Participant ID
     *
     * @var int
     */
    protected $participant = 0;

    /**
     * Team ID
     *
     * @var int
     */
    protected $team = 0;

    /**
     * The total amount the fundraiser has received
     *
     * @var float
     */
    protected $donation_value = 0.00;

    /**
     * The number of donations the fundraiser has received
     *
     * @var int
     */
    protected $donation_count = 0;

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

		$fundraiser = get_post( $id );

		if ( empty( $fundraiser ) ) {
			return false;
		}

		$this->setup_fundraiser( $fundraiser );

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
		$this->ID			   = absint( $fundraiser->ID );
		$this->_ID             = absint( $fundraiser->ID);
		$this->fundraiser_name = $fundraiser->post_title;
		$this->fundraiser_slug = $fundraiser->post_name;
        $this->participant     = (int) get_post_meta( $this->ID, '_peerraiser_fundraiser_participant', true );

        $campaign              = wp_get_post_terms( $this->ID, 'peerraiser_campaign' );
        $this->campaign_id     = ! empty( $campaign ) ? $campaign[0]->term_id : 0;

        $team                  = wp_get_post_terms( $this->ID, 'peerraiser_team' );
        $this->team_id         = ! empty( $team ) ? $team[0]->term_id : 0;

		// Money
		$this->fundraiser_goal = get_post_meta( $this->ID, '_peerraiser_fundraiser_goal', true );
        $donation_value        = get_post_meta( $this->ID, '_peerraiser_donation_value', true );
        $this->donation_value  = $donation_value ? floatval( $donation_value ) : 0.00;
        $donation_count        = get_post_meta( $this->ID, '_peerraiser_donation_count', true );
        $this->donation_count  = $donation_count ? intval( $donation_count ) : 0;

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
	    do_action( 'peerraiser_pre_delete_fundraiser', $this );

        wp_delete_post( $this->ID );

        do_action( 'peerraiser_post_delete_fundraiser', $this );
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
		return update_post_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

    /**
     * Add the fundraiser to a campaign
     *
     * @param $campaign_id int The campaign to add the fundraiser to
     */
	public function add_to_campaign( $campaign_id ) {
		$this->update_meta( '_peerraiser_campaign_id', $campaign_id );
	}

    /**
     * Get the total number of fundraisers
     *
     * @return int Number of fundraisers
     */
	public function get_total_fundraisers() {
        $fundraisers_count = wp_count_posts( 'fundraiser' );
        return (int) $fundraisers_count->publish;
    }

    /**
     * Get the top fundraisers sort by value
     *
     * @param int $count Number of top fundraisers to get
     *
     * @return array Fundraisers
     */
    public function get_top_fundraisers( $count = 20 ) {
	    $args = array(
	    	'post_type'      => 'fundraiser',
		    'posts_per_page' => $count,
		    'meta_key'       => '_peerraiser_donation_value',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC'
	    );

	    $fundraisers = new \WP_Query( $args );
	    $fundraiser_ids = wp_list_pluck( $fundraisers->posts, 'ID' );
	    $data = array();

	    foreach ( $fundraiser_ids as $fundraiser_id ) {
	    	$data[] = new $this( $fundraiser_id );
	    }

	    return $data;
    }

    /**
     * Increase the donation count of the fundraiser
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

        do_action( 'peerraiser_fundraiser_pre_increase_donation_count', $count, $this->ID );

        $this->update_meta( '_peerraiser_donation_count', $new_total );
        $this->donation_count = $new_total;

        do_action( 'peerraiser_fundraiser_post_increase_donation_count', $this->donation_count, $count, $this->ID );

        return $this->donation_count;
    }

    /**
     * Decrease the fundraiser donation count
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

        do_action( 'peerraiser_fundraiser_pre_decrease_donation_count', $count, $this->ID );

        $this->update_meta( '_peerraiser_donation_count', $new_total );
        $this->donation_count = $new_total;

        do_action( 'peerraiser_fundraiser_post_decrease_donation_count', $this->donation_count, $count, $this->ID );

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
        $value = apply_filters( 'peerraiser_fundraiser_increase_value', $value, $this );

        $new_value = floatval( $this->donation_value ) + $value;

        do_action( 'peerraiser_fundraiser_pre_increase_value', $value, $this->ID, $this );

        $this->update_meta( '_peerraiser_donation_value', $new_value );
        $this->donation_value = $new_value;

        do_action( 'peerraiser_fundraiser_post_increase_value', $this->donation_value, $value, $this->ID, $this );

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
        $value = apply_filters( 'peerraiser_fundraiser_decrease_value', $value, $this );

        $new_value = floatval( $this->donation_value ) - $value;

        if( $new_value < 0 ) {
            $new_value = 0.00;
        }

        do_action( 'peerraiser_fundraiser_pre_decrease_value', $value, $this->ID, $this );

        $this->update_meta( '_peerraiser_donation_value', $new_value );
        $this->donation_value = $new_value;

        do_action( 'peerraiser_fundraiser_post_decrease_value', $this->donation_value, $value, $this->ID, $this );

        return $this->donation_value;
    }

}
