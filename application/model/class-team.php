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
     * Team Headline
     *
     * @var string
     */
    protected $team_headline = '';

    /**
     * Team Description
     *
     * @var string
     */
    protected $team_content = '';

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
	 * The total amount the team has received in test mode
	 *
	 * @var float
	 */
	protected $test_donation_value = 0.00;

	/**
	 * The number of test donations the team has received
	 *
	 * @var int
	 */
	protected $test_donation_count = 0;

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
        $this->team_headline       = get_term_meta( $this->ID, '_peerraiser_team_headline', true );
        $this->team_content        = get_term_meta( $this->ID, '_peerraiser_team_content', true );
        $this->thumbnail_image     = get_term_meta( $this->ID, '_peerraiser_thumbnail_image', true );
        $this->thumbnail_image_id  = get_term_meta( $this->ID, '_peerraiser_thumbnail_image_id', true );

        // Money
        $this->team_goal           = get_term_meta( $this->ID, '_peerraiser_team_goal', true );
        $donation_value            = get_term_meta( $this->ID, '_peerraiser_donation_value', true );
        $this->donation_value      = $donation_value ? floatval( $donation_value ) : 0.00;
        $donation_count            = get_term_meta( $this->ID, '_peerraiser_donation_count', true );
        $this->donation_count      = $donation_count ? intval( $donation_count ) : 0;
	    $test_donation_value       = get_term_meta( $this->ID, '_peerraiser_test_donation_value', true );
	    $this->test_donation_value = $test_donation_value ? floatval( $test_donation_value ) : 0.00;
	    $test_donation_count       = get_term_meta( $this->ID, '_peerraiser_test_donation_count', true );
	    $this->test_donation_count = $test_donation_count ? intval( $test_donation_count ) : 0;

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

        $team = wp_insert_term( $this->team_name, 'peerraiser_team', array( 'slug' => $this->team_slug ) );

        if ( is_wp_error( $team ) ) {
            error_log( $team->get_error_message() );
        }

        $this->ID  = $team['term_id'];
        $this->_ID = $team['term_id'];

        // Set stats to 0
        $this->update_meta( '_peerraiser_donation_count', 0 );
        $this->update_meta( '_peerraiser_donation_value', 0.00 );

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

        if ( isset( $args['orderby'] ) ) {
            switch ( $args['orderby'] ) {
                case 'raised' :
                    $args['meta_key'] = '_peerraiser_donation_value';
                    $args['orderby'] = 'meta_value_num';
                    break;
                default :
                    // do nothing
            }
        }

        if ( isset( $args['campaign'] ) ) {
            $fundraiser_model = new Fundraiser();
            $fundraisers = $fundraiser_model->get_fundraisers( );
        }

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

    public function get_team_by_slug( $value ) {
        $term = get_term_by( 'slug', $value, 'peerraiser_team' );

        if ( empty( $term ) ) {
            return array();
        }

        return new self( $term->term_id );
    }

    public function get_teams_by_campaign( $campaign_id, $count = 20 ) {
        $args = array(
            'offset' => 0,
            'hide_empty' => false,
            'taxonomy' => array( 'peerraiser_team'),
            'meta_key'   => '_peerraiser_campaign_id',
            'meta_value' => $campaign_id,
            'count' => $count,
        );

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
     * Get the top teams sort by value
     *
     * @param int   $count Number of top fundraisers to get
     * @param array $args
     *
     * @return array Fundraisers
     */
    public function get_top_teams( $count = 20, $args = array() ) {
        $defaults = array(
            'offset'     => 0,
            'hide_empty' => false,
            'taxonomy'   => array( 'peerraiser_team' ),
            'count'      => $count,
            'meta_key'   => '_peerraiser_donation_value',
            'orderby'    => 'meta_value_num',
            'order'      => 'DESC'
        );

        $args = wp_parse_args( $args, $defaults );

        $term_query = new WP_Term_Query( $args );

        $results = array();
        if ( ! empty( $term_query->terms ) ) {
            foreach ( $term_query->terms as $term ) {
                $team = new self( $term->term_id );
                if ( $team->donation_value > 0 ){
                    $results[] = $team;
                }
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

	        do_action( 'peerraiser_team_pre_increase_test_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_test_donation_count', $new_total );
	        $this->test_donation_count = $new_total;

	        do_action( 'peerraiser_team_post_increase_test_donation_count', $this->test_donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count + (int) $count;

	        do_action( 'peerraiser_team_pre_increase_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_team_post_increase_donation_count', $this->donation_count, $count, $this->ID );
        }

        return $is_test ? $this->test_donation_count : $this->donation_count;
    }

    /**
     * Decrease the team donation count
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

	        do_action( 'peerraiser_team_pre_decrease_test_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_test_donation_count', $new_total );
	        $this->test_donation_count = $new_total;

	        do_action( 'peerraiser_team_post_decrease_test_donation_count', $this->test_donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count - (int) $count;

	        if ( $new_total < 0 ) {
		        $new_total = 0;
	        }

	        do_action( 'peerraiser_team_pre_decrease_donation_count', $count, $this->ID );

	        $this->update_meta( '_peerraiser_donation_count', $new_total );
	        $this->donation_count = $new_total;

	        do_action( 'peerraiser_team_post_decrease_donation_count', $this->donation_count, $count, $this->ID );
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
	        $value = apply_filters( 'peerraiser_team_increase_test_value', $value, $this );

	        $new_value = floatval( $this->test_donation_value ) + $value;

	        do_action( 'peerraiser_team_pre_increase_test_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_test_donation_value', $new_value );
	        $this->test_donation_value = $new_value;

	        do_action( 'peerraiser_team_post_increase_value', $this->test_donation_value, $value, $this->ID, $this );
        } else {
	        $value = apply_filters( 'peerraiser_team_increase_value', $value, $this );

	        $new_value = floatval( $this->donation_value ) + $value;

	        do_action( 'peerraiser_team_pre_increase_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_donation_value', $new_value );
	        $this->donation_value = $new_value;

	        do_action( 'peerraiser_team_post_increase_value', $this->donation_value, $value, $this->ID, $this );
        }

        return $is_test ? $this->test_donation_value : $this->donation_value;
    }

    /**
     * Decrease a customer's lifetime value
     *
     * @since 1.0.0
     * @param float $value The value to decrease by
     * @param bool  $is_test Whether the donation was made in test mode or not
     *
     * @return mixed If successful, the new value, otherwise false
     */
    public function decrease_value( $value = 0.00, $is_test = false ) {
        if ( $is_test ) {
	        $value = apply_filters( 'peerraiser_team_decrease_test_value', $value, $this );

	        $new_value = floatval( $this->test_donation_value ) - $value;

	        if( $new_value < 0 ) {
		        $new_value = 0.00;
	        }

	        do_action( 'peerraiser_team_pre_decrease_test_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_test_donation_value', $new_value );
	        $this->test_donation_value = $new_value;

	        do_action( 'peerraiser_team_post_decrease_value', $this->test_donation_value, $value, $this->ID, $this );
        } else {
	        $value = apply_filters( 'peerraiser_team_decrease_value', $value, $this );

	        $new_value = floatval( $this->donation_value ) - $value;

	        if( $new_value < 0 ) {
		        $new_value = 0.00;
	        }

	        do_action( 'peerraiser_team_pre_decrease_value', $value, $this->ID, $this );

	        $this->update_meta( '_peerraiser_donation_value', $new_value );
	        $this->donation_value = $new_value;

	        do_action( 'peerraiser_team_post_decrease_value', $this->donation_value, $value, $this->ID, $this );
        }

        return $is_test ? $this->test_donation_value : $this->donation_value;
    }

	/**
	 * Returns the total number of members of the team
	 *
	 * @return int
	 */
    public function get_total_members() {
        $term = get_term( $this->ID, 'peerraiser_team' );
        return $term->count;
    }

	/**
	 * Returns the Team Page URL
	 *
	 * @return string|\WP_Error
	 */
    public function get_permalink() {
        return get_term_link( (int) $this->ID, 'peerraiser_team' );
    }

    public function get_display_link() {
        $post_name_html = '<span id="editable-post-name">' . esc_html( $this->get_name_abridged() ) . '</span>';

        return \PeerRaiser\Helper\Text::str_replace_last( $this->team_slug, $post_name_html, $this->get_permalink() );
    }

    public function get_name_abridged() {
        if ( mb_strlen( $this->team_slug ) > 34 ) {
            $post_name_abridged = mb_substr( $this->team_slug, 0, 16 ) . '&hellip;' . mb_substr( $this->team_slug, -16 );
        } else {
            $post_name_abridged = $this->team_slug;
        }

        return $post_name_abridged;
    }

    public function get_team_leader_name() {
        if ( empty( $this->team_leader ) ) {
            return false;
        }

        $user_info = get_userdata( $this->team_leader );

        return trim( $user_info->first_name . ' ' . $user_info->last_name );
    }

    public function get_teams_for_current_user() {
        $current_user_id  = get_current_user_id();

        $args = array(
            'post_type'		 =>	'fundraiser',
            'fields'         => 'ids',
            'posts_per_page' => 9999,
            'meta_query'	 =>	array(
                array(
                    'key'   =>  '_peerraiser_fundraiser_participant',
                    'value'	=>	$current_user_id
                )
            )
        );
        $fundraisers = new \WP_Query( $args );
        $fundraiser_ids = $fundraisers->posts;

        $team_ids = wp_get_object_terms( $fundraiser_ids, 'peerraiser_team', array( 'fields' => 'ids' ) );

        $teams = array();
        foreach ( $team_ids as $team_id ) {
            $teams[] = new self( $team_id );
        }

        return $teams;
    }

    public function get_thumbnail_image() {
        if ( ! empty ( $this->thumbnail_image ) ) {
            return $this->thumbnail_image;
        }

        $plugin_options = get_option( 'peerraiser_options', array() );

        return esc_url( $plugin_options['team_thumbnail_image'] );
    }

    public function get_fundraisers( $options = array() ) {
        $defaults = array(
            'number' => -1,
        );

        $options = wp_parse_args( $options, $defaults );

        $args = array(
            'post_type' => 'fundraiser',
            'posts_per_page' => $options['number'],
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'peerraiser_team',
                    'field'    => 'id',
                    'terms'    => array( $this->ID ),
                ),
            ),
        );

        if ( isset( $options['orderby'] ) ) {
            switch ( $options['orderby'] ) {
                case 'raised' :
                    $args['meta_key'] = '_peerraiser_donation_value';
                    $args['orderby'] = 'meta_value_num';
                    break;
                default :
                    // do nothing
            }
        }

        $query = new \WP_Query( $args );

        $fundraisers = array();
        foreach( $query->posts as $fundraiser_id ) {
            $fundraisers[] = new Fundraiser( $fundraiser_id );
        }

        return $fundraisers;
    }

    /**
     * Generate a safe team slug
     *
     * @param bool $slug
     *
     * @return bool|mixed|string
     */
    private function generate_team_slug( $slug = false ) {
        if ( ! $slug ) {
            $slug = $this->team_name;
        }

        // Remove special characters
        $slug = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities( wp_strip_all_tags( $slug ) ) );

        // Replace whitespaces with dashes.
        $slug = sanitize_title_with_dashes( $slug, null, 'save' );

        if ( get_term_by( 'slug', $slug, 'peerraiser_team' ) ) {
            $slug = $this->increment_slug( $slug );
            $slug = $this->generate_team_slug( $slug );
            return $slug;
        } else {
            return $slug;
        }
    }

    function increment_slug( $slug ) {
        preg_match("/(.*?)-(\d+)$/", $slug, $matches );

        if ( isset( $matches[2] ) ) {
            $new_slug = $matches[1] . '-' . ( intval($matches[2]) + 1 );
        } else {
            $new_slug = $slug . '-2';
        }

        return $new_slug;
    }
}
