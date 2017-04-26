<?php
namespace PeerRaiser\Model\Database;

use PeerRaiser\Core\Database;

class Donor_Meta_Table extends Database {

    /**
     * Instaniate the class
     *
     * @access  public
     * @since   1.0.0
    */
    public function __construct() {

        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'pr_donormeta';
        $this->primary_key = 'meta_id';
        $this->version     = '1.0';

        add_action( 'plugins_loaded', array( $this, 'register_table'), 11 );

    }

    /**
     * Get table columns and data types
     *
     * @since     1.0.4
     * @return    array    Columns and data types
     */
    public function get_columns() {
        return array(
            'meta_id'     => '%d',
            'donor_id' => '%d',
            'meta_key'    => '%s',
            'meta_value'  => '%s',
        );
    }

    /**
     * Register the tahble with $wpdb
     *
     * @since     1.0.0
     * @return    [type]    [description]
     */
    public function register_table() {
        global $wpdb;
        $wpdb->donormeta = $this->table_name;
    }

    /**
     * Get donor meta data
     *
     * @since     1.0.4
     * @param     integer    $donor_id    Donor ID
     * @param     string     $meta_key    The meta key
     * @param     boolean    $single      Should a single value be returned, or an array
     * @return    mixed                   Array if $single is false, or the value of the meta key if true
     */
    public function get_meta( $donor_id = 0, $meta_key = '', $single = false ) {
        $donor_id = $this->sanitize_donor_id( $donor_id );
        if ( false === $donor_id )
            return false;

        return get_metadata( 'donor', $donor_id, $meta_key, $single );
    }

    /**
     * Add meta data field to a donor.
     *
     * @param   int    $donor_id   donor ID.
     * @param   string $meta_key      Metadata name.
     * @param   mixed  $meta_value    Metadata value.
     * @param   bool   $unique        Optional, default is false. Whether the same key should not be added.
     * @return  bool                  False for failure. True for success.
     *
     * @access  private
     * @since   1.0.4
     */
    public function add_meta( $donor_id = 0, $meta_key = '', $meta_value, $unique = false ) {
        $donor_id = $this->sanitize_donor_id( $donor_id );
        if ( false === $donor_id ) {
            return false;
        }

        return add_metadata( 'donor', $donor_id, $meta_key, $meta_value, $unique );
    }

    /**
     * Update donor meta field based on Donor ID.
     *
     * Use the $prev_value parameter to differentiate between meta fields with the
     * same key and donor ID.
     *
     * If the meta field for the donor does not exist, it will be added.
     *
     * @param   int    $donor_id      Donor ID.
     * @param   string $meta_key      Metadata key.
     * @param   mixed  $meta_value    Metadata value.
     * @param   mixed  $prev_value    Optional. Previous value to check before removing.
     * @return  bool                  False on failure, true if success.
     *
     * @access  public
     * @since   1.0.4
     */
    public function update_meta( $donor_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
        $donor_id = $this->sanitize_donor_id( $donor_id );
        if ( false === $donor_id )
            return false;

        return update_metadata( 'donor', $donor_id, $meta_key, $meta_value, $prev_value );
    }

    /**
     * Remove metadata matching criteria from a donor.
     *
     * @param   int    $donor_id      Donor ID.
     * @param   string $meta_key      Metadata name.
     * @param   mixed  $meta_value    Optional. Metadata value.
     * @return  bool                  False for failure. True for success.
     *
     * @access  public
     * @since   1.0.4
     */
    public function delete_meta( $donor_id = 0, $meta_key = '', $meta_value = '' ) {
        return delete_metadata( 'donor', $donor_id, $meta_key, $meta_value );
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   1.0.4
    */
    public function create_table() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE {$this->table_name} (
            meta_id bigint(20) NOT NULL AUTO_INCREMENT,
            donor_id bigint(20) NOT NULL,
            meta_key varchar(255) DEFAULT NULL,
            meta_value longtext,
            PRIMARY KEY  (meta_id),
            KEY donor_id (donor_id),
            KEY meta_key (meta_key)
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

    /**
     * Given a Donor ID, make sure it's a positive number, greater than zero before inserting or adding.
     *
     * @since  1.0.4
     * @param  int|stirng $donor_id A passed donor ID.
     * @return int|bool                The normalized donor ID or false if it's found to not be valid.
     */
    private function sanitize_donor_id( $donor_id ) {
        if ( ! is_numeric( $donor_id ) )
            return false;

        $donor_id = (int) $donor_id;

        if ( absint( $donor_id ) !== $donor_id )
            return false;

        if ( empty( $donor_id ) )
            return false;

        return absint( $donor_id );
    }

}