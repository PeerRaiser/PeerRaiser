<?php

namespace PeerRaiser\Model;

use WP_Term_Query;
use \DateTime;;

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
     * Start Time
     *
     * @var string
     */
    protected $start_time = '';

    /**
     * Start date in UTC format
     *
     * @var string
     */
    protected $start_date_utc = '';

    /**
     * End date
     *
     * @var string
     */
    protected $end_date = '';

    /**
     * End Time
     *
     * @var string
     */
    protected $end_time = '';

    /**
     * End date in UTC format
     *
     * @var string
     */
    protected $end_date_utc = '';

    /**
     * Timezone for the start/end date
     *
     * @var string
     */
    protected $timezone = '';

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
     * Banner image ID
     *
     * @var int
     */
    protected $banner_image_id = 0;

    /**
     * Thumbnail image
     *
     * @var string
     */
    protected $thumbnail_image = '';

    /**
     * Thumbnail image ID
     *
     * @var int
     */
    protected $thumbnail_image_id = 0;

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
     * Default fundraising title
     *
     * @var string
     */
    protected $default_fundraiser_title = '';

    /**
     * Default fundraising title
     *
     * @var string
     */
    protected $default_team_title = '';

    /**
     * Default fundraising content
     *
     * @var string
     */
    protected $default_fundraiser_content = '';

    /**
     * Default team content
     *
     * @var string
     */
    protected $default_team_content = '';

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
    protected $registration_limit;

    /**
     * Team limit
     *
     * @var int
     */
    protected $team_limit;

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
	 * The total amount the campaign has received in test mode
	 *
	 * @var float
	 */
	protected $test_donation_value = 0.00;

	/**
	 * The number of test donations the campaign has received
	 *
	 * @var int
	 */
	protected $test_donation_count = 0;

    /**
     * The campaign status
     *
     * @var string
     */
    protected $campaign_status = 'active';

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

        // Dates / Status
        $this->start_date      = get_term_meta( $this->ID, '_peerraiser_start_date', true );
        $this->start_time      = get_term_meta( $this->ID, '_peerraiser_start_time', true );
        $this->start_date_utc  = get_term_meta( $this->ID, '_peerraiser_start_date_utc', true );
        $this->end_date        = get_term_meta( $this->ID, '_peerraiser_end_date', true );
        $this->end_time        = get_term_meta( $this->ID, '_peerraiser_end_time', true );
        $this->end_date_utc    = get_term_meta( $this->ID, '_peerraiser_end_date_utc', true );
        $this->timezone        = get_term_meta( $this->ID, '_peerraiser_timezone', true );
        $this->campaign_status = get_term_meta( $this->ID, '_peerraiser_campaign_status', true );

        // Campaign content
        $this->campaign_description = get_term_meta( $this->ID, '_peerraiser_campaign_description', true );
        $this->banner_image         = get_term_meta( $this->ID, '_peerraiser_banner_image', true );
        $this->banner_image_id      = get_term_meta( $this->ID, '_peerraiser_banner_image_id', true );
        $this->thumbnail_image      = get_term_meta( $this->ID, '_peerraiser_thumbnail_image', true );
        $this->thumbnail_image_id   = get_term_meta( $this->ID, '_peerraiser_thumbnail_image_id', true );

        // Money
        $this->campaign_goal       = get_term_meta( $this->ID, '_peerraiser_campaign_goal', true );
        $donation_value            = get_term_meta( $this->ID, '_peerraiser_donation_value', true );
        $this->donation_value      = $donation_value ? floatval( $donation_value ) : 0.00;
        $donation_count            = get_term_meta( $this->ID, '_peerraiser_donation_count', true );
        $this->donation_count      = $donation_count ? intval( $donation_count ) : 0;
	    $test_donation_value       = get_term_meta( $this->ID, '_peerraiser_test_donation_value', true );
	    $this->test_donation_value = $test_donation_value ? floatval( $test_donation_value ) : 0.00;
	    $test_donation_count       = get_term_meta( $this->ID, '_peerraiser_test_donation_count', true );
	    $this->test_donation_count = $test_donation_count ? intval( $test_donation_count ) : 0;

        // Limits
	    $this->registration_limit        = get_term_meta( $this->ID, '_peerraiser_registration_limit', true );
	    $this->team_limit                = get_term_meta( $this->ID, '_peerraiser_team_limit', true );
        $this->allow_anonymous_donations = get_term_meta( $this->ID, '_peerraiser_allow_anonymous_donations', true );
        $this->allow_comments            = get_term_meta( $this->ID, '_peerraiser_allow_comments', true );
        $this->allow_fees_covered        = get_term_meta( $this->ID, '_peerraiser_allow_fees_covered', true );

        // Fundraiser Page Options
        $this->suggested_individual_goal  = get_term_meta( $this->ID, '_peerraiser_suggested_individual_goal', true );
        $this->default_fundraiser_title   = get_term_meta( $this->ID, '_peerraiser_default_fundraiser_title', true );
        $this->default_fundraiser_content = get_term_meta( $this->ID, '_peerraiser_default_fundraiser_content', true );

        // Team Page Options
        $this->suggested_team_goal  = get_term_meta( $this->ID, '_peerraiser_suggested_team_goal', true );
        $this->default_team_title   = get_term_meta( $this->ID, '_peerraiser_default_team_title', true );
        $this->default_team_content = get_term_meta( $this->ID, '_peerraiser_default_team_content', true );

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
            $time = new DateTime();
            $this->start_date = $time->format( apply_filters( 'peerraiser_date_field_format', 'm/d/Y' ) );
            $this->pending['start_date'] = $this->start_date;
        }

        $campaign = wp_insert_term(
            $this->campaign_name,
            'peerraiser_campaign',
            $args = array(
                'slug' => $this->campaign_slug,
            ) );

        if ( is_wp_error( $campaign ) ) {
            error_log( $campaign->get_error_message() );
            die( $campaign->get_error_message() );
        }

        $this->ID  = $campaign['term_id'];
        $this->_ID = $campaign['term_id'];

        // Set stats to 0
        $this->update_meta( '_peerraiser_donation_count', 0 );
        $this->update_meta( '_peerraiser_donation_value', 0.00 );

        do_action( 'peerraiser_campaign_added', $this );

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
                    $this->update_meta( '_peerraiser_' . $key, $value );
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
        do_action( 'peerraiser_campaign_delete', $this );

        wp_delete_term( $this->ID, 'peerraiser_campaign' );

        do_action( 'peerraiser_campaign_deleted', $this );
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
        do_action( 'peerraiser_update_campaign_meta', $this, $meta_key, $meta_value );

        if ( $results = update_term_meta( $this->ID, $meta_key, $meta_value, $prev_value ) ) {
            do_action( 'peerraiser_updated_campaign_meta', $this, $meta_key, $meta_value );
        }

        return $results;
    }

    /**
     * Get campaign meta
     *
     * @param string $meta_key The meta key to retrieve. By default, returns data for all keys.
     * @param bool   $single   Whether to return a single value
     *
     * @return mixed
     */
    public function get_meta( $meta_key = '', $single = false ) {
        return get_term_meta( $this->ID, $meta_key, true );
    }

    /**
     * Removes campaign meta
     *
     * @since 1.0.0
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Optional. Metadata value. If provided, rows will only be removed that match the value.
     *
     * @return bool True on success, false on failure.
     */
    function delete_meta( $meta_key, $meta_value = '' ) {
        do_action( 'peerraiser_delete_campaign_meta', $this, $meta_key );

        if ( $results = delete_term_meta( $this->ID, $meta_key, $meta_value ) ) {
            do_action( 'peerraiser_deleted_campaign_meta', $this, $meta_key );
        }

        return $results;
    }

    /**
     * Update the campaign name
     *
     * @since  1.0.0
     * @param string $name Campaign name
     * @param bool   $slug Campaign slug
     */
    public function update_campaign_name( $name, $slug = false ) {
        $args = array(
            'name' => $name,
            'slug' => $slug ? $slug : $this->campaign_slug,
        );

        wp_update_term( $this->ID, 'peerraiser_campaign', $args );

        do_action( 'peerraiser_campaign_name_updated', $this );
    }

    /**
     * Increase the donation count of the campaign
     *
     * @since 1.0.0
     * @param integer $count   The number to increment by
     * @param bool    $is_test Whether the donation was made in test mode or not
     *
     * @return int The donation count
     */
    public function increase_donation_count( $count = 1, $is_test = false ) {
        if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
            return false;
        }

        if ( $is_test ) {
	        $new_total = (int) $this->test_donation_count + (int) $count;

	        do_action( 'peerraiser_campaign_pre_increase_test_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_test_donation_count', $new_total );
	        $this->test_donation_count = $new_total;

	        do_action( 'peerraiser_campaign_post_increase_test_donation_count', $this->test_donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count + (int) $count;

	        do_action( 'peerraiser_campaign_pre_increase_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_campaign_post_increase_donation_count', $this->donation_count, $count, $this->ID );
        }

        return $is_test ? $this->test_donation_count : $this->donation_count;
    }

    /**
     * Decrease the campaign donation count
     *
     * @since 1.0.0
     * @param integer $count   The amount to decrease by
     * @param bool    $is_test Whether the donation was made in test mode or not
     *
     * @return mixed If successful, the new count, otherwise false
     */
    public function decrease_donation_count( $count = 1, $is_test = false ) {
        // Make sure it's numeric and not negative
        if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
            return false;
        }

        if ( $is_test ) {
	        $new_total = (int) $this->test_donation_count - (int) $count;

	        if ( $new_total < 0 ) {
		        $new_total = 0;
	        }

	        do_action( 'peerraiser_campaign_pre_decrease_test_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_test_donation_count', $new_total );
	        $this->test_donation_count = $new_total;

	        do_action( 'peerraiser_campaign_post_decrease_test_donation_count', $this->test_donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count - (int) $count;

	        if ( $new_total < 0 ) {
		        $new_total = 0;
	        }

	        do_action( 'peerraiser_campaign_pre_decrease_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_campaign_post_decrease_donation_count', $this->donation_count, $count, $this->ID );
        }

        return $is_test ? $this->test_donation_count : $this->donation_count;
    }

    /**
     * Increase the customer's lifetime value
     *
     * @since 1.0.0
     * @param float $value   The value to increase by
     * @param bool  $is_test Whether the donation was made in test mode or not
     *
     * @return mixed If successful, the new value, otherwise false
     */
    public function increase_value( $value = 0.00, $is_test = false ) {
        if ( $is_test ) {
	        $value = apply_filters( 'peerraiser_campaign_increase_test_value', $value, $this );

	        $new_value = floatval( $this->test_donation_value ) + $value;

	        do_action( 'peerraiser_campaign_pre_increase_test_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_test_donation_value', $new_value );
	        $this->test_donation_value = $new_value;

	        do_action( 'peerraiser_campaign_post_increase_test_value', $this->test_donation_value, $value, $this->ID, $this );
        } else {
	        $value = apply_filters( 'peerraiser_campaign_increase_value', $value, $this );

	        $new_value = floatval( $this->donation_value ) + $value;

	        do_action( 'peerraiser_campaign_pre_increase_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_donation_value', $new_value );
	        $this->donation_value = $new_value;

	        do_action( 'peerraiser_campaign_post_increase_value', $this->donation_value, $value, $this->ID, $this );
        }

        return $is_test ? $this->test_donation_value : $this->donation_value;
    }

    /**
     * Decrease a customer's lifetime value
     *
     * @since 1.0.0
     * @param float $value   The value to decrease by
     * @param bool  $is_test Whether the donation was made in test mode or not
     *
     * @return mixed If successful, the new value, otherwise false
     */
    public function decrease_value( $value = 0.00, $is_test = false ) {
        if ( $is_test ) {
	        $value = apply_filters( 'peerraiser_campaign_decrease_test_value', $value, $this );

	        $new_value = floatval( $this->test_donation_value ) - $value;

	        if( $new_value < 0 ) {
		        $new_value = 0.00;
	        }

	        do_action( 'peerraiser_campaign_pre_decrease_test_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_test_donation_value', $new_value );
	        $this->test_donation_value = $new_value;

	        do_action( 'peerraiser_campaign_post_decrease_value', $this->test_donation_value, $value, $this->ID, $this );
        } else {
	        $value = apply_filters( 'peerraiser_campaign_decrease_value', $value, $this );

	        $new_value = floatval( $this->donation_value ) - $value;

	        if( $new_value < 0 ) {
		        $new_value = 0.00;
	        }

	        do_action( 'peerraiser_campaign_pre_decrease_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_donation_value', $new_value );
	        $this->donation_value = $new_value;

	        do_action( 'peerraiser_campaign_post_decrease_value', $this->donation_value, $value, $this->ID, $this );
        }

        return $is_test ? $this->test_donation_value : $this->donation_value;
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
     * Get the fundraisers associated with this campaign
     *
     * @param bool $count
     *
     * @return int|\WP_Query
     */
    public function get_fundraisers( $count = false ) {
        $fundraiser_ids = $this->get_fundraiser_ids();

        if ( $count ) {
            return count( $fundraiser_ids );
        }

        $fundraisers = array();

        foreach ( $fundraiser_ids as $fundraiser_id ) {
            $fundraisers[] = new \PeerRaiser\Model\Fundraiser( $fundraiser_id );
        }

        return $fundraisers;
    }

    /**
     * Get Fundraiser IDs
     *
     * @since 1.0.0
     * @return array Fundraiser ids associated with this campaign
     */
    public function get_fundraiser_ids() {
        $args = array(
            'post_type' => 'fundraiser',
            'tax_query' => array(
                array(
                    'taxonomy'       => 'peerraiser_campaign',
                    'field'          => 'id',
                    'terms'          => $this->ID,
                    'posts_per_page' => -1
                )
            )
        );
        $query = new \WP_Query( $args );

        return wp_list_pluck( $query->posts, 'ID' );
    }

    /**
     * Get the total number of fundraisers for this campaign
     *
     * @return int
     */
    public function get_total_fundraisers() {
        return $this->get_fundraisers( true );
    }

    /**
     * Get the teams for this campaign
     *
     * @param  bool  $count Just return the total number of teams
     * @return int
     */
    public function get_teams( $count = false) {
        $fundraiser_ids = $this->get_fundraiser_ids();

        $args = array(
            'fields' => $count ? 'ids' : 'all',
        );

        $terms = wp_get_object_terms( $fundraiser_ids, 'peerraiser_team', $args );

        return $count ? count( $terms ) : $terms;
    }

    /**
     * Get the total number of teams for this campaign
     *
     * @return int
     */
    public function get_total_teams() {
        return $this->get_teams(true);
    }

    /**
     * Get campaigns
     *
     * @param array $args
     *
     * @return array
     */
    public function get_campaigns( $args = array()) {
        $defaults = array(
            'count'      => 20,
            'offset'     => 0,
            'hide_empty' => false,
            'taxonomy'   => array( 'peerraiser_campaign' ),
        );

        $args = wp_parse_args( $args, $defaults );

        if ( isset( $args['orderby'] ) ) {
            switch ( $args['orderby'] ) {
                case 'donations' :
                    $args['meta_key'] = '_peerraiser_donation_count';
                    $args['orderby'] = 'meta_value_num';
                    break;
                case 'raised' :
                    $args['meta_key'] = '_peerraiser_donation_value';
                    $args['orderby'] = 'meta_value_num';
                    break;
                default :
                    // do nothing
            }
        }

        if ( isset( $args['campaign_status'] ) ) {
            if ( is_array( $args['campaign_status'] ) ) {
                $args['meta_query'] = array(
                    'relation' => 'OR',
                );
                foreach ( $args['campaign_status'] as $status ) {
                    $args['meta_query'][] = array(
                        'key' => '_peerraiser_campaign_status',
                        'value' => $status,
                        'compare' => '='
                    );
                }
            } else {
                $args['meta_key'] = '_peerraiser_campaign_status';
                $args['meta_value'] = $args['campaign_status'];
            }
            unset( $args['campaign_status'] );
        }

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
     * Get the permalink URL for this campaign
     *
     * @return string|\WP_Error
     */
    public function get_permalink() {
        return get_term_link( (int) $this->ID, 'peerraiser_campaign' );
    }

    /**
     * Get the display link for the permalink of this campaign
     *
     * @return mixed
     */
    public function get_display_link() {
        $post_name_html = '<span id="editable-post-name">' . esc_html( $this->get_name_abridged() ) . '</span>';
        return \PeerRaiser\Helper\Text::str_replace_last( $this->campaign_slug, $post_name_html, $this->get_permalink() );
    }

    /**
     * Get the campaign slug, abridged if its more than 34 characters
     *
     * @return string
     */
    public function get_name_abridged() {
        if ( mb_strlen( $this->campaign_slug ) > 34 ) {
            $post_name_abridged = mb_substr( $this->campaign_slug, 0, 16 ) . '&hellip;' . mb_substr( $this->campaign_slug, -16 );
        } else {
            $post_name_abridged = $this->campaign_slug;
        }

        return $post_name_abridged;
    }

    /**
     * Returns the timezone in a format that can be used with DateTime
     *
     * @return string
     */
    public function get_timezone_string() {
        $timezone = $this->timezone;

        // If there's no timezone, get the site's timezone
        if ( empty( $timezone ) ) {
            $timezone = \PeerRaiser\Helper\Date::get_timezone_string();
        }

        if ( $timezone === 'UTC' ) {
            return 'UTC';
        }

        // If the timezone is UTC offset, return just the offset amount
        if ( substr( $timezone, 0, 3 ) === "UTC" ) {
            return substr( $timezone, 3 );
        }

        return $timezone;
    }

    public function get_thumbnail_image() {
        if ( ! empty ( $this->thumbnail_image ) ) {
            return $this->thumbnail_image;
        }

        $plugin_options = get_option( 'peerraiser_options', array() );

        return esc_url( $plugin_options['campaign_thumbnail_image'] );
    }

	/**
	 * Determines if the fundraiser limit has been reached
	 *
	 * @since 1.2.0
	 * @return bool Whether the limit has been reached or not
	 */
    public function fundraiser_limit_reached() {
    	return ( ! empty( $this->registration_limit ) && $this->get_total_fundraisers() >= $this->registration_limit );
    }

    /**
     * Generate a safe campaign slug
     *
     * @since     1.0.0
     * @return    string    campaign slug
     */
    private function generate_campaign_slug() {
        $campaign_name = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities( wp_strip_all_tags( $this->campaign_name ) ) );
        return sanitize_title_with_dashes( $campaign_name, null, 'save' );
    }
}
