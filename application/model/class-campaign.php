<?php

namespace PeerRaiser\Model;

use WP_Term_Query;

/**
 * Campaign Model
 *
 * Use this class to interact with PeerRaiser Campaigns
 */
class Campaign {

	/**
	 * The Campaign ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	public    $ID  = 0;

	/**
	 * The Protected Campaign ID
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	protected $_ID = 0;

	/**
	 * New or existing campaign
	 *
	 * @since  1.0.0
	 * @var boolean
	 */
	protected $new = false;

	/**
	 * Campaign name
	 *
	 * @var string
	 */
	protected $campaign_name = '';

	/**
	 * Campaign slug
	 *
	 * @var string
	 */
	protected $campaign_slug = '';

	/**
	 * Start date
	 *
	 * @var string
	 */
	protected $start_date = '';

	/**
	 * End date
	 *
	 * @var string
	 */
	protected $end_date = '';

	/**
	 * Campaign Description
	 *
	 * @var string
	 */
	protected $campaign_description = '';

	/**
	 * Banner image
	 *
	 * @var string
	 */
	protected $banner_image = '';

	/**
	 * Thumbnail image
	 *
	 * @var string
	 */
	protected $thumbnail_image = '';

	/**
	 * Campaign goal
	 *
	 * @var float
	 */
	protected $campaign_goal = 0.00;

	/**
	 * Suggested individual goal
	 *
	 * @var float
	 */
	protected $suggested_individual_goal = 0.00;

	/**
	 * Suggested team goal
	 *
	 * @var float
	 */
	protected $suggested_team_goal = 0.00;

	/**
	 * Registration limit
	 *
	 * @var int
	 */
	protected $registration_limit = -1;

	/**
	 * Team limit
	 *
	 * @var int
	 */
	protected $team_limit = -1;

	/**
	 * Allow anonymous donations?
	 *
	 * @var bool
	 */
	protected $allow_anonymous_donations = true;

	/**
	 * Allow comments?
	 *
	 * @var bool
	 */
	protected $allow_comments = true;

	/**
	 * Ask donors to cover transaction fees?
	 *
	 * @var bool
	 */
	protected $allow_fees_covered = true;

	/**
	 * Thank you page id
	 *
	 * @var int
	 */
	protected $thank_you_page = 0;

    /**
     * The total amount the campaign has received
     *
     * @var float
     */
    protected $donation_value = 0.00;

    /**
     * The number of donations the campaign has received
     *
     * @var int
     */
    protected $donation_count = 0;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_campaign_meta calls to be run
	 *
	 * @since  1.0.0
	 * @var array
	 */
	private $pending;

	/**
	 * Setup campaign class
	 *
	 * @since 1.0.0
	 * @param int|boolean $id Campaign ID
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$id = absint( $id );

		// WP_Term_Query arguments
		$args = array(
			'taxonomy'               => array( 'peerraiser_campaign' ),
			'include'                => array( $id ),
			'number'                 => 1,
			'hide_empty'             => false,
		);

		$campaign = new WP_Term_Query( $args );

		if ( empty( $campaign->terms ) || is_wp_error( $campaign ) ) {
			return false;
		}

		$this->setup_campaign( $campaign->terms[0] );

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
	 * Setup the campaign properties
	 *
	 * @since  1.0.0
	 * @param  object $campaign A campaign object
	 * @return bool             True if the setup worked, false if not
	 */
	private function setup_campaign( $campaign ) {
		$this->pending = array();

		// Perform your actions before the campaign is loaded with this hook:
		do_action( 'peerraiser_before_setup_campaign', $this, $campaign );

		// Primary Identifiers
		$this->ID			 = absint( $campaign->term_id );
		$this->_ID           = absint( $campaign->term_id);
		$this->campaign_name = $campaign->name;
		$this->campaign_slug = $campaign->slug;

		// Dates
		$this->start_date  = get_term_meta( $this->ID, 'start_date', true );
		$this->end_date    = get_term_meta( $this->ID, 'end_date', true );

		// Campaign content
		$this->campaign_description = get_term_meta( $this->ID, 'campaign_description', true );
		$this->banner_image         = get_term_meta( $this->ID, 'banner_image', true );
		$this->thumbnail_image      = get_term_meta( $this->ID, 'thumbnail_image', true );

		// Money
		$this->campaign_goal			 = get_term_meta( $this->ID, 'campaign_goal', true );
		$this->suggested_individual_goal = get_term_meta( $this->ID, 'suggested_individual_goal', true );
		$this->suggested_team_goal       = get_term_meta( $this->ID, 'suggested_team_goal', true );
		$donation_value                  = get_term_meta( $this->ID, '_peerraiser_donation_value', true );
		$this->donation_value            = $donation_value ? floatval( $donation_value ) : 0.00;
		$donation_count                  = get_term_meta( $this->ID, '_peerraiser_donation_count', true );
		$this->donation_count            = $donation_count ? intval( $donation_count ) : 0;

		// Limits
		$this->registration_limit 		 = get_term_meta( $this->ID, '_peerraiser_registration_limit', true );
		$this->team_limit         		 = get_term_meta( $this->ID, '_peerraiser_team_limit', true );
		$this->allow_anonymous_donations = get_term_meta( $this->ID, '_peerraiser_allow_anonymous_donations', true );
		$this->allow_comments            = get_term_meta( $this->ID, '_peerraiser_allow_comments', true );

		// Thank you page
		$this->thank_you_page = get_term_meta( $this->ID, '_peerraiser_thank_you_page', true );

		// Add your own items to this object via this hook:
		do_action( 'peerraiser_after_setup_campaign', $this, $campaign );

		return true;
	}

	/**
	 * Creates a campaign record in the database
	 *
	 * @since     1.0.0
	 * @return    int    Campaign ID
	 */
	private function insert_campaign() {
		if ( empty( $this->campaign_slug ) ) {
		    $this->campaign_slug = $this->generate_campaign_slug();
        }

	    if ( empty( $this->start_date ) ) {
			$this->start_date = current_time( 'timestamp' );
			$this->pending['start_date'] = $this->start_date;
		}

		$campaign = wp_insert_term(
		    $this->campaign_name,
            'peerraiser_campaign',
            $args = array(
                'slug' => $this->campaign_slug,
            ) );

		$this->ID  = $campaign['term_id'];
		$this->_ID = $campaign['term_id'];

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
			$this->insert_campaign();
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
                    do_action( 'peerraiser_campaign_save', $this, $key );
                }
			}
		}

		do_action( 'peerraiser_campaign_saved', $this->ID, $this );

		$cache_key = md5( 'peerraiser_campaign_' . $this->ID );
		wp_cache_set( $cache_key, $this, 'campaigns' );

		return true;
	}

    /**
     * Delete the campaign
     */
	public function delete() {
        wp_delete_term( $this->ID, 'peerraiser_campaign' );
	}

	/**
	 * Update campaign meta
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
     * Increase the donation count of the campaign
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

        do_action( 'peerraiser_campaign_pre_increase_donation_count', $count, $this->ID );

        $this->update_meta( '_peerraiser_donation_count', $new_total );
        $this->donation_count = $new_total;

        do_action( 'peerraiser_campaign_post_increase_donation_count', $this->donation_count, $count, $this->ID );

        return $this->donation_count;
    }

    /**
     * Decrease the campaign donation count
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

        do_action( 'peerraiser_campaign_pre_decrease_donation_count', $count, $this->ID );

        $this->update_meta( '_peerraiser_donation_count', $new_total );
        $this->donation_count = $new_total;

        do_action( 'peerraiser_campaign_post_decrease_donation_count', $this->donation_count, $count, $this->ID );

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
        $value = apply_filters( 'peerraiser_campaign_increase_value', $value, $this );

        $new_value = floatval( $this->donation_value ) + $value;

        do_action( 'peerraiser_campaign_pre_increase_value', $value, $this->ID, $this );

        $this->update_meta( '_peerraiser_donation_value', $new_value );
        $this->donation_value = $new_value;

        do_action( 'peerraiser_campaign_post_increase_value', $this->donation_value, $value, $this->ID, $this );

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
        $value = apply_filters( 'peerraiser_campaign_decrease_value', $value, $this );

        $new_value = floatval( $this->donation_value ) - $value;

        if( $new_value < 0 ) {
            $new_value = 0.00;
        }

        do_action( 'peerraiser_campaign_pre_decrease_value', $value, $this->ID, $this );

        $this->update_meta( '_peerraiser_donation_value', $new_value );
        $this->donation_value = $new_value;

        do_action( 'peerraiser_campaign_post_decrease_value', $this->donation_value, $value, $this->ID, $this );

        return $this->donation_value;
    }

    /**
     * Returns the total number of campaigns
     *
     * @return array|int|\WP_Error
     */
    public function get_total_campaigns() {
        return wp_count_terms( 'peerraiser_campaign', array( 'hide_empty' => false ) );
    }

    /**
     * Generate a safe campaign slug
     *
     * @since     1.0.0
     * @return    string    campaign slug
     */
    private function generate_campaign_slug() {
        $campaign_title = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities( wp_strip_all_tags( $this->campaign_name ) ) );
        return sanitize_title_with_dashes( $campaign_title, null, 'save' );
    }
}
