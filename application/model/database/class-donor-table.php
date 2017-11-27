<?php
namespace PeerRaiser\Model\Database;

use PeerRaiser\Core\Database;
use PeerRaiser\Helper\View;

class Donor_Table extends Database {

    /**
     * Instantiate the class
     *
     * @access  public
     * @since   1.0.0
    */
    public function __construct() {

        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'pr_donors';
        $this->primary_key = 'donor_id';
        $this->version     = '1.0';

    }

    /**
     * Get columns and formats
     *
     * @access  public
     * @since   1.0.0
    */
    public function get_columns() {
        return array(
            'donor_id'            => '%d',
            'user_id'             => '%d',
            'first_name'          => '%s',
            'last_name'           => '%s',
            'full_name'           => '%s',
            'email_address'       => '%s',
            'donation_value'      => '%f',
            'donation_count'      => '%d',
            'test_donation_value' => '%f',
            'test_donation_count' => '%d',
            'date'                => '%s',
        );
    }

    /**
     * Get default column values
     *
     * @access  public
     * @since   1.0.0
    */
    public function get_column_defaults() {
        return array(
            'donor_id'            => 0,
            'first_name'          => '',
            'last_name'           => '',
            'full_name'           => '',
            'email_address'       => '',
            'user_id'             => 0,
            'donation_value'      => '0.00',
            'donation_count'      => 0,
            'test_donation_value' => '0.00',
            'test_donation_count' => 0,
            'date'                => date( 'Y-m-d H:i:s' ),
        );
    }

    /**
     * Retrieve donors from the database
     *
     * @access  public
     * @since   1.0.0
     * @param   array $args
     * @param   bool  $count  Return only the total number of results found (optional)
     *
     * @return array Donor records
    */
    public function get_donors( $args = array(), $count = false ) {

        global $wpdb;

        $defaults = array(
            'number'         => 20,
            'offset'         => 0,
            'donor_id'       => 0,
            'user_id'        => 0,
            'first_name'     => '',
            'last_name'      => '',
            'full_name'      => '',
            'email_address'  => '',
            'donation_count' => '',
            'orderby'        => 'donor_id',
            'order'          => 'ASC',
        );

        $args  = wp_parse_args( $args, $defaults );

        if ( $args['number'] < 1 ) {
            $args['number'] = 999999999999;
        }

        $where = '';

        // specific donor
        if ( ! empty( $args['donor_id'] ) ) {
            if ( is_array( $args['donor_id'] ) ) {
                $donor_ids = implode( ',', $args['donor_id'] );
            } else {
                $donor_ids = intval( $args['donor_id'] );
            }

            $where .= "WHERE `donor_id` IN( {$donor_ids} ) ";
        }

        // by user id
        if ( ! empty( $args['user_id'] ) ) {
            if ( is_array( $args['user_id'] ) ) {
                $user_ids = implode( ',', $args['user_id'] );
            } else {
                $user_ids = intval( $args['user_id'] );
            }

            $where .= "WHERE `user_id` IN( {$user_ids} ) ";
        }

        // By donor first name
        if ( ! empty( $args['first_name'] ) ) {
            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= sprintf(" `first_name` LIKE '%s' ", "%%" . $wpdb->esc_like( $args['first_name']) . "%%" );
        }

        // By donor last name
        if ( ! empty( $args['last_name'] ) ) {
            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= sprintf(" `last_name` LIKE '%s' ", "%%" . $wpdb->esc_like( $args['last_name']) . "%%" );
        }

        // By donor name
        if ( ! empty( $args['donor_name'] ) ) {
            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= sprintf(" `full_name` LIKE '%s' ", "%%" . $wpdb->esc_like( $args['donor_name']) . "%%" );
        }

        // By donor name
        if ( ! empty( $args['email_address'] ) ) {
            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= sprintf(" `email_address` LIKE '%s' ", "%%" . $wpdb->esc_like( $args['email_address']) . "%%" );
        }

        // By number of donations
        if ( ! empty( $args['donation_count'] ) ) {
            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= sprintf(" `donation_count` LIKE '%s' ", "%%" . $wpdb->esc_like( $args['donation_count']) . "%%" );
        }

        // By date
        if ( ! empty( $args['date'] ) ) {
            $year  = date( 'Y', strtotime( $args['date'] ) );
            $month = date( 'm', strtotime( $args['date'] ) );
            $day   = date( 'd', strtotime( $args['date'] ) );

            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= " $year = YEAR ( date ) AND $month = MONTH ( date ) AND $day = DAY ( date )";
        }

        $args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

        $cache_key = ( true === $count ) ? md5( 'pr_donors_count' . serialize( $args ) ) : md5( 'pr_donors_' . serialize( $args ) );

        $results = wp_cache_get( $cache_key, 'donors' );

        if ( false === $results ) {

            if ( true === $count ) {

                $results = absint( $wpdb->get_var( "SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};" ) );

            } else {

                $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
                        absint( $args['offset'] ),
                        absint( $args['number'] )
                    )
                );

            }

            wp_cache_set( $cache_key, $results, 'donors', 3600 );

        }

        return $results;

    }

    public function get_top_donors( $count = 20 ) {
        global $wpdb;

	    $test_mode = ( View::get_plugin_mode() === 'test' ) ? 1 : 0;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pr_donations.donor_id, sum(pr_donations.total) as total, pr_donors.full_name
                FROM {$wpdb->prefix}pr_donations as pr_donations
                INNER JOIN {$wpdb->prefix}pr_donors as pr_donors
                ON pr_donors.donor_id = pr_donations.donor_id
                AND pr_donations.status = 'completed'  
                AND pr_donations.is_test = {$test_mode}
                GROUP BY pr_donations.donor_id
                ORDER BY total DESC
                LIMIT %d",
                absint( $count )
            )
        );

        return $results;
    }

    public function get_top_donors_to_campaign( $id, $count = 20 ) {
        global $wpdb;

	    $test_mode = ( View::get_plugin_mode() === 'test' ) ? 1 : 0;

	    $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pr_donations.donor_id, sum(pr_donations.total) as total, pr_donors.full_name
                FROM {$wpdb->prefix}pr_donations as pr_donations
                INNER JOIN {$wpdb->prefix}pr_donors as pr_donors
                ON pr_donors.donor_id = pr_donations.donor_id
                WHERE pr_donations.campaign_id = %d 
                AND pr_donations.status = 'completed'  
                AND pr_donations.is_test = {$test_mode}
                GROUP BY pr_donations.donor_id
                ORDER BY total DESC
                LIMIT %d",
                absint( $id ), absint( $count )
            )
        );

        return $results;
    }

    /**
     * Return the number of results found for a given query
     *
     * @param  array  $args
     * @return int
     */
    public function count( $args = array() ) {
        return $this->get_donors( $args, true );
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   1.0.0
    */
    public function create_table() {

        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE " . $this->table_name . " (
        donor_id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL DEFAULT 0,
        email_address varchar(254) NOT NULL,
        first_name text NOT NULL,
        last_name text NOT NULL,
        full_name text NOT NULL,
        donation_value decimal(13,4) NOT NULL DEFAULT '0.00',
        donation_count bigint(20) NOT NULL DEFAULT 0,
        test_donation_value decimal(13,4) NOT NULL DEFAULT '0.00',
        test_donation_count bigint(20) NOT NULL DEFAULT 0,
        date datetime NOT NULL,
        PRIMARY KEY  (donor_id)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );

        update_option( $this->table_name . '_db_version', $this->version );
    }

    /**
     * Check if table exists
     *
     * @since   1.0.4
     * @param   string  $table_name The name of the table
     * @return    bool    True if table exists, false if it doesn't
     */
    public function table_exists( $table_name = '' ) {
        return parent::table_exists( $this->table_name );
    }

    public function add_donor( \PeerRaiser\Model\Donor $donor ) {
        global $wpdb;

        $data = array(
            'first_name'          => $donor->first_name,
            'last_name'           => $donor->last_name,
            'full_name'           => $donor->full_name,
            'email_address'       => $donor->email_address,
            'user_id'             => $donor->user_id,
            'donation_value'      => $donor->donation_value,
            'donation_count'      => $donor->donation_count,
            'test_donation_value' => $donor->donation_value,
            'test_donation_count' => $donor->donation_count,
            'date'                => $donor->date,
        );

        $this->insert( $data );

        return $wpdb->insert_id;
    }
}
