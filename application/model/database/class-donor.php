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
            'donor_name'    => '%s',
            'user_id'       => '%d',
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
            'donor_id'   => 0,
            'donor_name' => '',
            'user_id'    => 0,
            'date'       => date( 'Y-m-d H:i:s' ),
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
            'number'        => 20,
            'offset'        => 0,
            'donor_id'      => 0,
            'donor_name'    => '',
            'orderby'       => 'donor_id',
            'order'         => 'ASC',
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

        // By donor name
        if ( ! empty( $args['donor_name'] ) ) {
            if ( empty( $where ) ) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }

            $where .= sprintf(" `donor_name` LIKE '%s' ", "%%" . $wpdb->esc_like( $args['donor_name']) . "%%" );
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
        donor_name text NOT NULL,
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
     * @since   1.0.4
     * @param   string  $table_name The name of the table
     * @return    bool    True if table exists, false if it doesn't
     */
    public function table_exists( $table_name = '' ) {
        return parent::table_exists( $this->table_name );
    }
}
