<?php

namespace PeerRaiser\Model;

use \PeerRaiser\Model\Database\Donor_Table;
use \PeerRaiser\Model\Database\Donor_Meta_Table;

class Donor {

    /**
     * The Donor ID
     *
     * @since 1.0.0
     * @var    integer
     */
    public    $ID  = 0;

    /**
     * The Protected Donor ID
     *
     * @since 1.0.0
     * @var    integer
     */
    protected $_ID = 0;

    /**
     * New or existing donor
     *
     * @since 1.0.0
     * @var boolean
     */
    protected $new = false;

    /**
     * The date the donor was created
     *
     * @since 1.0.0
     * @var string
     */
    protected $date = '';

    /**
     * The User ID of the donor (if they have one)
     *
     * @since 1.0.0
     * @var integer
     */
    protected $user_id = 0;

    /**
     * The first name of the donor
     *
     * @since 1.0.0
     * @var string
     */
    protected $first_name = '';

    /**
     * The last name of the donor
     *
     * @since 1.0.0
     * @var string
     */
    protected $last_name = '';

    /**
     * The donor's full name
     *
     * @since 1.0.0
     * @var string
     */
    protected $full_name = '';

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
	 * The total amount the donor has donated in test mode
	 *
	 * @var float
	 */
	protected $test_donation_value = 0.00;

	/**
	 * The number of test donations the donor has made
	 *
	 * @var int
	 */
	protected $test_donation_count = 0;

    /**
     * Donor notes
     *
     * @since 1.0.0
     * @var array
     */
    protected $notes = array();

    /**
     * Array of items that have changed since the last save() was run
     * This is for internal use, to allow fewer update_donor_meta calls to be run
     *
     * @since 1.0.0
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
     * @since 1.0.0
     * @param  int|boolean $id_or_email Donor ID or email address
     */
    public function __construct( $id_or_email = false ) {
        $this->db = new Donor_Table();

        if ( empty( $id_or_email ) ) {
            return false;
        }

        $args = array(
            'number'   => 1,
        );

        if ( is_email( $id_or_email ) ) {
            $args['email_address'] = $id_or_email;
        } else {
            $args['donor_id'] = $id_or_email;
        }

        $donors = $this->db->get_donors( $args );

        $donor = reset( $donors );

        if ( empty( $donor ) ) {
            return false;
        }

        $this->setup_donor( $donor );

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
     * Setup the donor properties
     *
     * @since 1.0.0
     * @param  object $donor A donor object
     *
     * @return bool True if the setup worked, false if not
     */
    private function setup_donor( $donor ) {
        // Perform your actions before the donor is loaded with this hook:
        do_action( 'peerraiser_before_setup_donor', $this, $donor );

        // Primary Identifier
        $this->ID = absint( $donor->donor_id );

        // Protected ID (can't be changed)
        $this->_ID = absint( $donor->donor_id);

        $this->first_name          = $donor->first_name;
        $this->last_name           = $donor->last_name;
        $this->full_name           = $donor->full_name;
        $this->email_address       = $donor->email_address;
        $this->date                = $donor->date;
        $this->donation_count      = $donor->donation_count;
        $this->donation_value      = $donor->donation_value;
	    $this->test_donation_count = $donor->test_donation_count;
	    $this->test_donation_value = $donor->test_donation_value;
        $this->user_id             = $donor->user_id;

        // Donor Notes
        $donor_notes = $this->get_meta( 'notes', true );
        $this->notes = ! empty( $donor_notes ) ? $donor_notes : array();

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
            $this->date = current_time( 'mysql' );
        }

        $donor_id = $this->db->add_donor( $this );

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

        // Attempt to connect donor to existing user
        if ( 0 === $this->user_id) {
            $this->user_id = $this->maybe_connect_user();
        }

        $bulk_update = array();
        $updated     = array();

        if ( ! empty( $this->pending ) ) {
            foreach ( $this->pending as $key => $value ) {
                switch( $key ) {
                    case 'first_name' :
                    case 'last_name' :
                    case 'full_name' :
                    case 'email_address' :
                    case 'date' :
                    case 'user_id' :
                        $bulk_update[$key] = $value;
                        $updated[$key] = $value;
                        break;
                    default :
                        $this->update_meta( $key, $value );
                        $updated[$key] = $value;
                        break;
                }
            }
        }

        if ( ! empty ( $bulk_update ) ) {
            $this->update( $bulk_update );
        }

        $cache_key = md5( 'peerraiser_donor_' . $this->ID );
        wp_cache_set( $cache_key, $this, 'donors' );

        $this->pending = array();

        do_action( 'peerraiser_donor_saved', $this, $updated );
        foreach ( $updated as $key => $value ) {
            do_action( "peerraiser_donor_updated_{$key}", $this, $key, $value );
        }

        return true;
    }

    /**
     * Delete this donor
     */
    public function delete() {
        global $wpdb;

        do_action( 'peerraiser_donor_delete', $this );

        $this->db->delete( $this->ID );

        // Delete the donor meta
        $wpdb->delete( $wpdb->prefix . 'pr_donormeta', array( 'donor_id' => $this->ID ), array( '%d' ) );

        do_action( 'peerraiser_donor_delete', $this );
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
            $this->setup_donor( reset( $donor ) );

            $updated = true;
        }

        return $updated;
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

        $donor_meta = new Donor_Meta_Table();

        return $donor_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Delete donor meta
     *
     * @since     1.0.0
     * @param     string    $meta_key      Meta key to update
     * @param     string    $meta_value    Meta value
     * @return    int|bool                 Optional. Metadata value.
     */
    public function delete_meta( $meta_key = '', $meta_value = '' ) {
        $meta_value = apply_filters( 'peerraiser_delete_donor_meta_' . $meta_key, $meta_value, $this->ID );

        $donor_meta = new Donor_Meta_Table();

        switch( $meta_key ) {
            case 'first_name' :
            case 'last_name' :
            case 'full_name' :
            case 'email_address' :
            case 'user_id' :
                $result = $this->db->update( $this->ID, array( $meta_key => '' ) );
                break;
            case 'date' :
                $result = $this->db->update(  $this->ID, array( $meta_key => current_time() ) );
                break;
            default :
                $result = $donor_meta->delete_meta( $this->ID, $meta_key, $meta_value );
                break;
        }

        return $result;
    }

    /**
     * Get donor meta
     *
     * @param string $meta_key
     * @param bool $single
     *
     * @return mixed
     */
    public function get_meta( $meta_key= '', $single = false ) {
        $donor_meta = new Donor_Meta_Table();
        $result = $donor_meta->get_meta( $this->ID, $meta_key, $single );

        return $result;
    }

    public function get_donors( $args ) {
        $donor_rows = $this->db->get_donors( $args );

        $donors = array();

        foreach( $donor_rows as $row ) {
            $donors[] = new self( $row->donor_id );
        }

        return $donors;
    }

    /**
     * Add a note to a donor
     *
     * @param string $what The donor note content
     * @param string $when When the note was added
     * @param string $who  Who added the note
     *
     * @return array
     */
    public function add_note( $what = '', $who = 'bot', $when = 'now' ) {
        $notes = $this->notes;

        if ( 'now' === $when ) {
            $when = current_time( 'mysql' );
        }

        if ( 'bot' === $who ) {
            $who = __( 'PeerRaiser Bot', 'peerraiser' );
        }

        $notes[] = array(
            'id'   => md5( $what . time() ),
            'when' => esc_attr( $when ),
            'who'  => esc_attr( $who ),
            'what' => wp_strip_all_tags( $what )
        );

        $this->notes = $notes;
        $this->pending['notes'] = $this->notes;

        return $this->notes;
    }

    /**
     * Increase the donation count of the donor
     *
     * @since 1.0.0
     * @param integer $count   The number to increment by
     * @param bool    $is_test Whether the donation is a test donation or not
     *
     * @return int The donation count
     */
    public function increase_donation_count( $count = 1, $is_test = false ) {
        if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
            return false;
        }

	    if ( $is_test ) {
		    do_action( 'peerraiser_donor_pre_increase_test_donation_count', $count, $this->ID );

		    $new_total = (int) $this->test_donation_count + (int) $count;

		    if ( $this->update( array( 'test_donation_count' => $new_total ) ) ) {
			    $this->test_donation_count = $new_total;
		    }

		    do_action( 'peerraiser_donor_post_increase_test_donation_count', $this->donation_count, $count, $this->ID );
	    } else {
		    do_action( 'peerraiser_donor_pre_increase_donation_count', $count, $this->ID );

		    $new_total = (int) $this->donation_count + (int) $count;

		    if ( $this->update( array( 'donation_count' => $new_total ) ) ) {
			    $this->donation_count = $new_total;
		    }

		    do_action( 'peerraiser_donor_post_increase_donation_count', $this->donation_count, $count, $this->ID);
	    }

	    return $is_test ? $this->test_donation_count : $this->donation_count;
    }

    /**
     * Decrease the donor donation count
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

	        do_action( 'peerraiser_donor_pre_decrease_test_donation_count', $count, $this->ID );

	        if ( $this->update( array( 'test_donation_count' => $new_total ) ) ) {
		        $this->test_donation_count = $new_total;
	        }

	        do_action( 'peerraiser_donor_post_decrease_test_donation_count', $this->donation_count, $count, $this->ID );
        } else {
	        $new_total = (int) $this->donation_count - (int) $count;

	        if ( $new_total < 0 ) {
		        $new_total = 0;
	        }

	        do_action( 'peerraiser_donor_pre_decrease_donation_count', $count, $this->ID );

	        if ( $this->update( array( 'donation_count' => $new_total ) ) ) {
		        $this->donation_count = $new_total;
	        }

	        do_action( 'peerraiser_donor_post_decrease_donation_count', $this->donation_count, $count, $this->ID );
        }

        return $is_test ? $this->test_donation_count : $this->donation_count;
    }

    /**
     * Increase the donor's lifetime value
     *
     * @since 1.0.0
     * @param float $value   The value to increase by
     * @param bool  $is_test Whether the donation was made in test mode or not
     *
     * @return mixed If successful, the new value, otherwise false
     */
    public function increase_value( $value = 0.00, $is_test = false ) {
    	if ( $is_test ) {
		    $value = apply_filters( 'peerraiser_donor_increase_test_value', $value, $this );

		    $new_value = floatval( $this->test_donation_value ) + $value;

		    do_action( 'peerraiser_donor_pre_increase_test_value', $value, $this->ID, $this );

		    if ( $this->update( array( 'test_donation_value' => $new_value ) ) ) {
			    $this->test_donation_value = $new_value;
		    }

		    do_action( 'peerraiser_donor_post_increase_value', $this->test_donation_value, $value, $this->ID, $this );
	    } else {
		    $value = apply_filters( 'peerraiser_donor_increase_value', $value, $this );

		    $new_value = floatval( $this->donation_value ) + $value;

		    do_action( 'peerraiser_donor_pre_increase_value', $value, $this->ID, $this );

		    if ( $this->update( array( 'donation_value' => $new_value ) ) ) {
			    $this->donation_value = $new_value;
		    }

		    do_action( 'peerraiser_donor_post_increase_value', $this->donation_value, $value, $this->ID, $this );
	    }

        return $is_test ? $this->test_donation_value : $this->donation_value;
    }

    /**
     * Decrease a donor's lifetime value
     *
     * @since 1.0.0
     * @param  float  $value The value to decrease by
     * @param bool  $is_test Whether the donation was made in test mode or not
     *
     * @return mixed If successful, the new value, otherwise false
     */
    public function decrease_value( $value = 0.00, $is_test = false ) {
    	if ( $is_test ) {
		    $value = apply_filters( 'peerraiser_donor_decrease_test_value', $value, $this );

		    $new_value = floatval( $this->test_donation_value ) - $value;

		    if( $new_value < 0 ) {
			    $new_value = 0.00;
		    }

		    do_action( 'peerraiser_donor_pre_decrease_test_value', $value, $this->ID, $this );

		    if ( $this->update( array( 'test_donation_value' => $new_value ) ) ) {
			    $this->test_donation_value = $new_value;
		    }

		    do_action( 'peerraiser_donor_post_decrease_test_value', $this->test_donation_value, $value, $this->ID, $this );
	    } else {
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
	    }

        return $is_test ? $this->test_donation_value : $this->donation_value;
    }

    /**
     * Get the top donors to a campaign, based on donation value
     *
     * @param int $count Maximum number of results to return
     *
     * @return array Top donors
     */
    public function get_top_donors( $count = 20 ) {
        $donor_database = new Donor_Table();

        return $donor_database->get_top_donors( $count );
    }

    /**
     * Get the top donors to a campaign, based on donation value
     *
     * @param int $id    The campaign ID
     * @param int $count Maximum number of results to return
     *
     * @return array Top donors
     */
    public function get_top_donors_to_campaign( $id, $count = 20 ) {
        $donor_database = new Donor_Table();

        return $donor_database->get_top_donors_to_campaign( $id, $count );
    }

    /**
     * Get the top donors to a fundraiser, based on donation value
     *
     * @param int $id    The fundraiser ID
     * @param int $count Maximum number of results to return
     *
     * @return array Top donors
     */
    public function get_top_donors_to_fundraiser( $id, $count = 20 ) {
        $donor_database = new Donor_Table();

        return $donor_database->get_top_donors_to_fundraiser( $id, $count );
    }

    public function get_profile_image() {
        $hash = md5( strtolower( trim( $this->email_address ) ) );
        $uri  = 'http://www.gravatar.com/avatar/' . $hash . '?d=404&s=192';

        $data = wp_cache_get( $hash );

        if ( false === $data ) {
            $response = wp_remote_head( $uri );

            if ( is_wp_error( $response ) ) {
                $data = '404';
            } else {
                $data = $response['response']['code'];
            }
            wp_cache_set( $hash, $data, $group = '', $expire = 60 * 5 );
        }
        if ( $data == '200' ) {
            return $uri . '.jpg';
        } else {
            return \PeerRaiser\Core\Setup::get_plugin_config()->get('images_url') . 'profile-mask.png';
        }
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

        $donation_table = new Donor_Table();
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
     * @since 1.0.0
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

    public function get_street_address_1() {
        return  $this->get_meta('street_address_1', true );
    }

    public function get_street_address_2() {
        return  $this->get_meta('street_address_2', true );
    }

    public function get_city() {
        return  $this->get_meta('city', true );
    }

    public function get_state_province() {
        return  $this->get_meta('state_province', true );
    }

    public function get_zip_postal() {
        return  $this->get_meta('zip_postal', true );
    }

    public function get_country() {
        return  $this->get_meta('country', true );
    }

    public function get_total_donors() {
        $donation_table = new Donor_Table();
        return $donation_table->get_donors( array( 'number' => -1 ), true );
    }
}
