<?php

namespace PeerRaiser\Model;

use \PeerRaiser\Model\Donor as Donor_Model;
use \PeerRaiser\Model\Database\Donation as Donation_Database;
use \PeerRaiser\Model\Database\Donation_Meta;

//TODO: 1. Set the team when a donation is made
//      2. Save donation notes

/**
 * Donation Model
 *
 * Use this class to interact with PeerRaiser Donations
 */
class Donation {

    /**
     * The Donation ID
     *
     * @since  1.0.0
     * @var    integer
     */
    public    $ID  = 0;

    /**
     * The Protected Donation ID
     *
     * @since  1.0.0
     * @var    integer
     */
    protected $_ID = 0;

    /**
     * New or existing donation
     *
     * @since  1.0.0
     * @var boolean
     */
    protected $new = false;

    /**
     * The Gateway mode the donation was made in
     *
     * @since  1.0.0
     * @var string
     */
    protected $mode = 'live';

    /**
     * The total amount the donation is for, after fees are applied
     *
     * @since  1.0.0
     * @var float
     */
    protected $total = 0.00;

    /**
     * The Subtotal for the donation before fees
     *
     * @since  1.0.0
     * @var float
     */
    protected $subtotal = 0;

    /**
     * Fees for this donation
     *
     * @since  1.0.0
     * @var float
     */
    protected $fees = 0;

    /**
     * The date the donation was created
     *
     * @since  1.0.0
     * @var string
     */
    protected $date = '';

    /**
     * The status of the donation
     *
     * @since  1.0.0
     * @var string
     */
    protected $status = 'pending';

    /**
     * When updating, the old status prior to the change
     *
     * @since  1.0.0
     * @var string
     */
    protected $old_status = '';

    /**
     * The display name of the current donation status
     *
     * @since  1.0.0
     * @var string
     */
    protected $status_nicename = '';

    /**
     * The ID of the donor that made the donation
     *
     * @since  1.0.0
     * @var integer
     */
    protected $donor_id = null;

    /**
     * The User ID of the donor (if they have one) that made the donation
     *
     * @since  1.0.0
     * @var integer
     */
    protected $user_id = 0;

    /**
     * The Campaign ID this donation was made to
     *
     * @since  1.0.0
     * @var integer
     */
    protected $campaign_id = 0;

    /**
     * The Fundraiser ID this donation was made to (if applicable)
     *
     * @since  1.0.0
     * @var integer
     */
    protected $fundraiser_id = 0;

    /**
     * The Team ID this donation was made to (if applicable)
     *
     * @since  1.0.0
     * @var integer
     */
    protected $team_id = 0;

    /**
     * The first name of the donor
     *
     * @since  1.0.0
     * @var string
     */
    protected $first_name = '';

    /**
     * The last name of the donor
     *
     * @since  1.0.0
     * @var string
     */
    protected $last_name = '';

    /**
     * The email used for the donation
     *
     * @since  1.0.0
     * @var string
     */
    protected $email = '';

    /**
     * The physical address used for the donation if provided
     *
     * @since  1.0.0
     * @var array
     */
    protected $address = array();

    /**
     * The transaction ID returned by the gateway
     *
     * @since  1.0.0
     * @var string
     */
    protected $transaction_id = '';

    /**
     * IP Address donation was made from
     *
     * @since  1.0.0
     * @var string
     */
    protected $ip = '';

    /**
     * The gateway used to process the donation
     *
     * @since  1.0.0
     * @var string
     */
    protected $gateway = '';

    /**
     * The donation type (Credit card, check, cash, etc)
     *
     * @since  1.0.0
     * @var string
     */
    protected $donation_type = '';

    /**
     * The currency the donation was made with
     *
     * @since  1.0.0
     * @var string
     */
    protected $currency = '';

    /**
     * Donation notes
     *
     * @since 1.0.0
     * @var array
     */
    protected $notes = array();

    /**
     * Array of items that have changed since the last save() was run
     * This is for internal use, to allow fewer update_donation_meta calls to be run
     *
     * @since  1.0.0
     * @var array
     */
    private $pending;

    /**
     * Setup donation class
     *
     * @since    1.0.0
     * @param    int|boolean    $id    Donation ID or Transaction ID
     * @param    int|boolean    $by    What to lookup the donation by (donation_id or transaction_id)
     */
    public function __construct( $id = false, $by = 'donation_id' ) {
        if ( empty( $id ) ) {
            return false;
        }

        $donation_table = new Donation_Database();

        $args = array( 'number' => 1 );

        switch ( strtolower($by) ) {
            case 'transaction':
            case 'transaction_id':
                $args['transaction_id'] = $id;
                break;
            case 'id':
            case 'donation_id':
                $args['donation_id'] = absint( $id );
                break;
            default:
                return false;
                break;
        }

        $donation = current( $donation_table->get_donations( $args ) );

        if ( empty( $donation ) ) {
            return false;
        }

        $this->setup_donation( $donation );

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

        if ( $key === 'status' ) {
            $this->old_status = $this->status;
        }

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
     * Setup the donation properties
     *
     * @since     1.0.0
     * @param     object    $donation    A donation object
     * @return    bool                   True if the setup worked, false if not
     */
    private function setup_donation( $donation ) {
        $this->pending = array();

        // Perform your actions before the donation is loaded with this hook:
        do_action( 'peerraiser_before_setup_donation', $this, $donation );

        // Primary Identifier
        $this->ID = absint( $donation->donation_id );

        // Protected ID (can't be changed)
        $this->_ID = absint( $donation->donation_id);

        // We have a donation, get the generic donation_meta item to reduce calls to it
        //$this->donation_meta    = $this->get_meta();

        // Status and Dates
        $this->date            = $donation->date;
        $this->status          = $donation->status;

        // Money related
        $this->total          = $donation->total;
        $this->subtotal       = $donation->subtotal;
        // $this->currency       = $this->setup_currency();

        // Gateway related
        $this->transaction_id = $donation->transaction_id;

        // User related
        $this->ip             = $donation->ip;
        $this->donor_id       = $donation->donor_id;
        // $this->user_id        = $this->setup_user_id();
        $this->campaign_id    = $donation->campaign_id;
        $this->fundraiser_id  = $donation->fundraiser_id;
        $this->team_id        = $donation->team_id;
        // $this->email          = $this->setup_email();
        // $this->first_name     = $this->user_info['first_name'];
        // $this->last_name      = $this->user_info['last_name'];

        // Donation Notes
        $donation_notes = $this->get_meta( 'notes' );
        $this->notes = ! empty( $donation_notes ) ? $donation_notes : array();

        // Add your own items to this object via this hook:
        do_action( 'peerraiser_after_setup_donation', $this, $donation );

        return true;
    }

    /**
     * Creates a donation record in the database
     *
     * @since     1.0.0
     * @return    int    Donation ID
     */
    private function insert_donation() {
        if ( empty( $this->transaction_id ) ) {
            $this->transaction_id = $this->generate_transaction_id();
        }

        if ( empty( $this->donor_id ) ) {
			return new WP_Error( 'peerraiser_missing_donor_id', __( "A donor ID is required to make a donation", "peerraiser" ) );
        }

        $this->adjust_donor_amounts();

        if ( empty( $this->ip ) ) {
            $this->ip = $this->get_ip_address();
        }

        if ( empty( $this->date ) ) {
            $this->date = current_time( 'mysql' );
        }

        $donation_table = new Donation_Database();
        $donation_id    = $donation_table->add_donation( $this );

        $this->ID  = $donation_id;
        $this->_ID = $donation_id;

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
            $this->insert_donation();
        }

        if ( $this->ID !== $this->_ID ) {
            $this->ID = $this->_ID;
        }

        if ( ! empty( $this->pending ) ) {
            foreach ( $this->pending as $key => $value ) {
                switch( $key ) {
                    case 'donation_type' :
                        $this->update_meta( 'donation_type', $this->donation_type );
                        break;

                    case 'gateway' :
                        $this->update_meta( 'gateway', $this->gateway );
                        break;

                    default :
                        do_action( 'peerraiser_donation_save', $this, $key );
                        break;
                }
            }
        }

        do_action( 'peerraiser_donation_saved', $this->ID, $this );

        $cache_key = md5( 'peerraiser_donation_' . $this->ID );
        wp_cache_set( $cache_key, $this, 'donations' );

        return true;
    }

    public function delete() {
		$donation_table = new Donation_Database();
		$donation_table->delete( $this->ID );
	}

	public function add_note( $note ) {
        $notes = $this->notes;
        $this->notes = $notes[] = array(
            'time' => current_time('mysql'),
            'note' => $note
        );

        return $this->notes;
    }

    /**
     * Gets the IP address
     *
     * Returns the IP address of the current user
     *
     * @since 1.0.0
     * @return string $ip User's IP address
     */
    private function get_ip_address() {

        $ip = '127.0.0.1';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $ip_array = explode( ',', $ip );
        $ip_array = array_map( 'trim', $ip_array );

        return apply_filters( 'peerraiser_set_ip', $ip_array[0] );
    }

    /**
     * Generate a random transaction ID
     *
     * This is used if a transaction ID wasn't passed by PeerRaiser.com
     * For example, if this donation was added manually.
     *
     * @since     1.0.0
     * @return    string    Random transaction id
     */
    private function generate_transaction_id() {
        return md5( mt_rand() . time() );
    }

    /**
     * Update donation meta
     *
     * @since     1.0.0
     * @param     string    $meta_key      Meta key to update
     * @param     string    $meta_value    Meta value
     * @param     string    $prev_value    Previous value
     * @return    int|bool                 Meta ID if the key didn't exist, true on success, false on failure
     */
    public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
        $meta_value = apply_filters( 'peerraiser_update_donation_meta_' . $meta_key, $meta_value, $this->ID );

        $donation_meta = new Donation_Meta();

        $result = $donation_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Get donation meta
     *
     * @param string $meta_key
     * @param bool $single
     * @return mixed
     */
    public function get_meta( $meta_key= '', $single = false ) {
        $donation_meta = new Donation_Meta();
        return $donation_meta->get_meta( $this->ID, $meta_key, $single );
    }

    private function adjust_donor_amounts() {
        $donor = new Donor_Model( $this->donor_id );

        if ( $this->total < 0) {
            $donor->decrease_donation_count( 1 );
            $donor->decrease_value( abs( $this->total ) );
        } else {
            $donor->increase_donation_count( 1 );
            $donor->increase_value( $this->total );
        }
    }
}
