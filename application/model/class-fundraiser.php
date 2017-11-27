<?php

namespace PeerRaiser\Model;

use PeerRaiser\Helper\View;
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
     * Fundraiser content
     *
     * @var string
     */
    protected $fundraiser_content = '';

    /**
     * Thumbnail image
     *
     * @var string
     */
    protected $thumbnail_image = '';

    /**
     * Thumbnail image
     *
     * @var string
     */
    protected $thumbnail_image_id = 0;

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
	 * The total amount the fundraiser has received in test mode
	 *
	 * @var float
	 */
	protected $test_donation_value = 0.00;

	/**
	 * The number of test donations the fundraiser has received
	 *
	 * @var int
	 */
	protected $test_donation_count = 0;

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
        $this->ID			      = absint( $fundraiser->ID );
        $this->_ID                = absint( $fundraiser->ID);
        $this->fundraiser_name    = $fundraiser->post_title;
        $this->fundraiser_slug    = $fundraiser->post_name;
        $this->fundraiser_content = $fundraiser->post_content;
        $this->thumbnail_image    = get_post_meta( $this->ID, '_peerraiser_thumbnail_image', true );
        $this->thumbnail_image_id = get_post_meta( $this->ID, '_peerraiser_thumbnail_image_id', true );
        $this->participant        = (int) get_post_meta( $this->ID, '_peerraiser_fundraiser_participant', true );

        $campaign          = wp_get_post_terms( $this->ID, 'peerraiser_campaign' );
        $this->campaign_id = ! empty( $campaign ) ? $campaign[0]->term_id : 0;

        $team          = wp_get_post_terms( $this->ID, 'peerraiser_team' );
        $this->team_id = ! empty( $team ) ? $team[0]->term_id : 0;

        // Money
        $this->fundraiser_goal      = get_post_meta( $this->ID, '_peerraiser_fundraiser_goal', true );
        $donation_value             = get_post_meta( $this->ID, '_peerraiser_donation_value', true );
        $this->donation_value       = $donation_value ? floatval( $donation_value ) : 0.00;
        $donation_count             = get_post_meta( $this->ID, '_peerraiser_donation_count', true );
        $this->donation_count       = $donation_count ? intval( $donation_count ) : 0;
	    $test_donation_value        = get_post_meta( $this->ID, '_peerraiser_test_donation_value', true );
	    $this->test_donation_value  = $test_donation_value ? floatval( $test_donation_value ) : 0.00;
	    $test_donation_count        = get_post_meta( $this->ID, '_peerraiser_test_donation_count', true );
	    $this->test_donation_count  = $test_donation_count ? intval( $test_donation_count ) : 0;

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
        if ( empty( $this->ID ) ) {
            $this->insert_fundraiser();
        }

        if ( $this->ID !== $this->_ID ) {
            $this->ID = $this->_ID;
        }

        if ( ! empty( $this->pending ) ) {
            $pending_post_data = array();
            foreach ( $this->pending as $key => $value ) {
                if ( in_array( $key, array( 'fundraiser_name', 'fundraiser_slug', 'fundraiser_content' ) ) ) {
                    switch ( $key ) {
                        case 'fundraiser_name' :
                            $pending_post_data['post_title'] = $value;
                            break;
                        case 'fundraiser_slug' :
                            $pending_post_data['post_name'] = $value;
                            break;
                        case 'fundraiser_content' :
                            $pending_post_data['post_content'] = $value;
                            break;
                    }
                } elseif ( property_exists( $this, $key ) ) {
                    switch ( $key ) {
                        case 'team_id' :
                            $this->add_to_team( $value );
                            $this->update_meta( '_peerraiser_fundraiser_team', $value );
                            break;
                        case 'campaign_id' :
                            $this->add_to_campaign( $value );
                            $this->update_meta( '_peerraiser_fundraiser_campaign', $value );
                            break;
                        case 'fundraiser_goal' :
                            $this->update_meta( '_peerraiser_fundraiser_goal', $value );
                            break;
                        case 'participant' :
                            $this->update_meta( '_peerraiser_fundraiser_participant', $value );
                            break;
                        default :
                            $this->update_meta( '_peerraiser_' . $key, $value );
                            break;
                    }
                } else {
                    do_action( 'peerraiser_fundraiser_save', $this, $key );
                }
            }

            if ( ! empty( $pending_post_data ) ) {
                $pending_post_data['ID'] = $this->ID;

                wp_update_post( $pending_post_data );
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
        do_action( 'peerraiser_fundraiser_delete', $this );

        wp_delete_post( $this->ID );

        do_action( 'peerraiser_fundraiser_deleted', $this );
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
        wp_set_object_terms( $this->ID, (int) $campaign_id, 'peerraiser_campaign' );
    }

    /**
     * Add the fundraiser to a team
     *
     * @param $team_id int The team to add the fundraiser to
     */
    public function add_to_team( $team_id ) {
        wp_set_object_terms( $this->ID, (int) $team_id, 'peerraiser_team' );
    }

    public function get_thumbnail_url( $size = 'peerraiser_thumbnail_medium' ) {
        if ( ! empty( $this->thumbnail_image_id ) ) {
            $image_attributes = wp_get_attachment_image_src( $this->thumbnail_image_id, apply_filters( 'peerraiser_fundraiser_thumbnail_size', $size ) );
            return $image_attributes[0];
        }

        $plugin_options = get_option( 'peerraiser_options', array() );

        return esc_url( $plugin_options['user_thumbnail_image'] );
    }

    public function get_fundraiser_url() {
        return get_permalink( $this->ID );
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
     * Get campaigns
     *
     * @param array $args
     *
     * @return array
     */
    public function get_fundraisers( $args = array()) {
        $defaults = array(
            'posts_per_page' => 20,
            'paged'          => 1,
            'post_type'      => array( 'fundraiser' ),
            'fields'         => 'ids'
        );

        $args = wp_parse_args( $args, $defaults );

        if ( isset( $args['participant'] ) ) {
        	$args['meta_query'][] = array(
        		'key' => '_peerraiser_fundraiser_participant',
		        'value' => $args['participant'],
	        );

        	unset( $args['participant']);
        }

        if ( isset( $args['campaign'] ) ) {
        	$args['tax_query'][] = array(
        		'taxonomy' => 'peerraiser_campaign',
		        'field'    => is_numeric( $args['campaign'] ) ? 'term_id' : 'slug',
		        'terms'    => $args['campaign'],
	        );

        	unset( $args['campaign']);
        }

        $query = new WP_Query( $args );

        $results = array();

        foreach ( $query->posts as $id ) {
            $results[] = new self( $id );
        }

        return $results;
    }

    /**
     * Get the top fundraisers sort by value
     *
     * @param int   $count Number of top fundraisers to get
     * @param array $args
     *
     * @return array Fundraisers
     */
    public function get_top_fundraisers( $count = 20, $args = array() ) {
        $defaults = array(
            'post_type'      => 'fundraiser',
            'posts_per_page' => $count,
            'meta_key'       => ( View::get_plugin_mode() === 'live' ) ? '_peerraiser_donation_value' : '_peerraiser_test_donation_value',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC'
        );

        $args = wp_parse_args( $args, $defaults );

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

	        do_action( 'peerraiser_fundraiser_pre_increase_test_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_test_donation_count', $new_total );
	        $this->test_donation_count = $new_total;

	        do_action( 'peerraiser_fundraiser_post_increase_test_donation_count', $this->test_donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count + (int) $count;

	        do_action( 'peerraiser_fundraiser_pre_increase_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_fundraiser_post_increase_donation_count', $this->donation_count, $count, $this->ID );
        }

        return $is_test ? $this->test_donation_count : $this->donation_count;
    }

    /**
     * Decrease the fundraiser donation count
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

	        do_action( 'peerraiser_fundraiser_pre_decrease_test_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_test_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_fundraiser_post_decrease_test_donation_count', $this->test_donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count - (int) $count;

	        if ( $new_total < 0 ) {
		        $new_total = 0;
	        }

	        do_action( 'peerraiser_fundraiser_pre_decrease_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_fundraiser_post_decrease_donation_count', $this->donation_count, $count, $this->ID );
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
		    $value = apply_filters( 'peerraiser_fundraiser_increase_test_value', $value, $this );

		    $new_value = floatval( $this->test_donation_value ) + $value;

		    do_action( 'peerraiser_fundraiser_pre_increase_test_value', $value, $this->ID, $this );

		    $this->update_meta( '_peerraiser_test_donation_value', $new_value );
		    $this->test_donation_value = $new_value;

		    do_action( 'peerraiser_fundraiser_post_increase_test_value', $this->test_donation_value, $value, $this->ID, $this );
	    } else {
		    $value = apply_filters( 'peerraiser_fundraiser_increase_value', $value, $this );

		    $new_value = floatval( $this->donation_value ) + $value;

		    do_action( 'peerraiser_fundraiser_pre_increase_value', $value, $this->ID, $this );

		    $this->update_meta( '_peerraiser_donation_value', $new_value );
		    $this->donation_value = $new_value;

		    do_action( 'peerraiser_fundraiser_post_increase_value', $this->donation_value, $value, $this->ID, $this );
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
	        $value = apply_filters( 'peerraiser_fundraiser_decrease_test_value', $value, $this );

	        $new_value = floatval( $this->test_donation_value ) - $value;

	        if( $new_value < 0 ) {
		        $new_value = 0.00;
	        }

	        do_action( 'peerraiser_fundraiser_pre_decrease_test_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_test_donation_value', $new_value );
	        $this->test_donation_value = $new_value;

	        do_action( 'peerraiser_fundraiser_post_decrease_test_value', $this->test_donation_value, $value, $this->ID, $this );
        } else {
	        $value = apply_filters( 'peerraiser_fundraiser_decrease_value', $value, $this );

	        $new_value = floatval( $this->donation_value ) - $value;

	        if( $new_value < 0 ) {
		        $new_value = 0.00;
	        }

	        do_action( 'peerraiser_fundraiser_pre_decrease_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_donation_value', $new_value );
	        $this->donation_value = $new_value;

	        do_action( 'peerraiser_fundraiser_post_decrease_value', $this->donation_value, $value, $this->ID, $this );
        }

        return $is_test ? $this->test_donation_value : $this->donation_value;
    }

    /**
     * Creates a donation record in the database
     *
     * @since     1.0.0
     *
     * @return    int|\WP_Error    Donation ID
     */
    private function insert_fundraiser() {
        if ( empty ( $this->fundraiser_name ) ) {
            $this->fundraiser_name = sprintf( __( 'Help Me Support %s!', 'peerraiser'), get_bloginfo( 'name') );
        }

        $fundraiser_id = wp_insert_post( array(
            'post_title' => $this->fundraiser_name,
            'post_content' => $this->fundraiser_content,
            'post_status' => 'publish',
            'post_type' => 'fundraiser'
        ) );

        $this->ID  = $fundraiser_id;
        $this->_ID = $fundraiser_id;

        do_action( 'peerraiser_fundraiser_added', $this );

        return $this->ID;
    }

}
