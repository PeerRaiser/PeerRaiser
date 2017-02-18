<?php
namespace PeerRaiser\Model\Database;

use PeerRaiser\Core\Database;

class Donor extends Database {

    /**
     * Instaniate the class
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
            'donor_id'      => '%d',
            'donation_id'   => '%d',
            'campaign_id'   => '%d',
            'team_id'       => '%d',
            'fundraiser_id' => '%d',
            'amount'        => '%f',
            'ip'            => '%s',
            'status'        => '%s',
            'date'          => '%s',
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
            'donor_id' => 0,
            'amount'      => '',
            'ip'          => '',
            'status'      => '',
            'date'        => date( 'Y-m-d H:i:s' ),
        );
    }

    /**
     * Retrieve donors from the database
     *
     * @access  public
     * @since   1.0.0
     * @param   array $args
     * @param   bool  $count  Return only the total number of results found (optional)
    */
    public function get_donors( $args = array(), $count = false ) {

        global $wpdb;

        $defaults = array(
            'number'        => 20,
            'offset'        => 0,
            'donor_id'      => 0,
            'donation_id'   => 0,
            'campaign_id'   => 0,
            'team_id'       => 0,
            'fundraiser_id' => 0,
            'status'        => '',
            'orderby'       => 'donor_id',
            'order'         => 'DESC',
        );

        $args  = wp_parse_args( $args, $defaults );

        if ( $args['number'] < 1 ) {
            $args['number'] = 999999999999;
        }

        $where = '';

        // specific donation
        if ( ! empty( $args['donation_id'] ) ) {
            if ( is_array( $args['donation_id'] ) ) {
                $donation_ids = implode( ',', $args['donation_id'] );
            } else {
                $donation_ids = intval( $args['donation_id'] );
            }

            $where .= "WHERE `donation_id` IN( {$donation_ids} ) ";
        }

        // specific donor
        if ( ! empty( $args['donor_id'] ) ) {
            if ( is_array( $args['donor_id'] ) ) {
                $donor_ids = implode( ',', $args['donor_id'] );
            } else {
                $donor_ids = intval( $args['donor_id'] );
            }

            $where .= "WHERE `donor_id` IN( {$donor_ids} ) ";
        }

        // specific campaign
        if ( ! empty( $args['campaign_id'] ) ) {
            if ( is_array( $args['campaign_id'] ) ) {
                $campaign_ids = implode( ',', $args['campaign_id'] );
            } else {
                $campaign_ids = intval( $args['campaign_id'] );
            }

            $where .= "WHERE `campaign_id` IN( {$campaign_ids} ) ";
        }

        // specific team
        if ( ! empty( $args['team_id'] ) ) {
            if ( is_array( $args['team_id'] ) ) {
                $team_ids = implode( ',', $args['team_id'] );
            } else {
                $team_ids = intval( $args['team_id'] );
            }

            $where .= "WHERE `team_id` IN( {$team_ids} ) ";
        }

        // specific fundraiser
        if ( ! empty( $args['fundraiser_id'] ) ) {
            if ( is_array( $args['fundraiser_id'] ) ) {
                $fundraiser_ids = implode( ',', $args['fundraiser_id'] );
            } else {
                $fundraiser_ids = intval( $args['fundraiser_id'] );
            }

            $where .= "WHERE `fundraiser_id` IN( {$fundraiser_ids} ) ";
        }


        if ( ! empty( $args['status'] ) ) {

            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            if ( is_array( $args['status'] ) ) {
                $where .= " `status` IN('" . implode( "','", $args['status'] ) . "') ";
            } else {
                $where .= " `status` = '" . $args['status'] . "' ";
            }

        }

        if ( ! empty( $args['date'] ) ) {

            if ( is_array( $args['date'] ) ) {

                if ( ! empty( $args['date']['start'] ) ) {

                    if ( false !== strpos( $args['date']['start'], ':' ) ) {
                        $format = 'Y-m-d H:i:s';
                    } else {
                        $format = 'Y-m-d 00:00:00';
                    }

                    $start = date( $format, strtotime( $args['date']['start'] ) );

                    if ( ! empty( $where ) ) {

                        $where .= " AND `date` >= '{$start}'";

                    } else {

                        $where .= " WHERE `date` >= '{$start}'";

                    }

                }

                if ( ! empty( $args['date']['end'] ) ) {

                    if ( false !== strpos( $args['date']['end'], ':' ) ) {
                        $format = 'Y-m-d H:i:s';
                    } else {
                        $format = 'Y-m-d 23:59:59';
                    }

                    $end = date( $format, strtotime( $args['date']['end'] ) );

                    if ( ! empty( $where ) ) {

                        $where .= " AND `date` <= '{$end}'";

                    } else {

                        $where .= " WHERE `date` <= '{$end}'";

                    }

                }

            } else {

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

        }

        $args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

        if ( 'amount' === $args['orderby'] ) {
            $args['orderby'] = 'amount+0';
        }

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
        date datetime NOT NULL,
        PRIMARY KEY  (donor_id)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );

        update_option( $this->table_name . '_db_version', $this->version );
    }

    /**
     * Check if table exists
     *
     * @since     1.0.4
     * @return    bool    True if table exists, false if it doesn't
     */
    public function table_exists( $table_name = '' ) {
        return parent::table_exists( $this->table_name );
    }
}