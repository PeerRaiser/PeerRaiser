<?php
namespace PeerRaiser\Model\Admin;

// Load the parent class if it doesn't exist.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use \PeerRaiser\Model\Donor;
use \PeerRaiser\Model\Database\Donor_Table as Donor_DB;
use \WP_List_Table;

/**
 * Class for displaying the list of donors
 *
 * @since 1.0.1
 */
class Donor_List_Table extends WP_List_Table {

    /** Class constructor */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Donor', 'peerraiser' ),
            'plural'   => __( 'Donors', 'peerraiser' ),
            'ajax'     => false
        ) );
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name( $item ) {
        // create a nonce
        $delete_nonce = wp_create_nonce( 'peerraiser_delete_donor_' . $item->donor_id );

        $donor = new Donor( $item->donor_id );

        $title = '<strong><a href="' . add_query_arg( array( 'donor' => $item->donor_id, 'view' => 'donor-details' ) ) . '">' . $donor->full_name . '</a></strong>';

        $actions = array(
            'edit' => sprintf( '<a href="?page=%s&view=%s&donor=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $item->donor_id ) ),
            'delete' => sprintf( '<a href="?page=%s&peerraiser_action=%s&donor_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete_donor', absint( $item->donor_id ), $delete_nonce ),
        );

        return $title . $this->row_actions( apply_filters( 'peerraiser_donor_actions', $actions ) );
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        $donor = new Donor( $item->donor_id );
        $is_test = peerraiser_is_test_mode();

        switch ( $column_name ) {
            case 'email_address':
                return  empty( $donor->email_address) ? '&mdash;' : $donor->email_address;
            case 'donations' :
                return $is_test ? $donor->test_donation_count : $donor->donation_count;
            case 'amount':
            	$amount = $is_test ? $donor->test_donation_value : $donor->donation_value;
                return $amount ? peerraiser_money_format( $amount ) : 0 ;
            case 'date':
                $date = strtotime( $donor->date );
                return date('m-d-Y', $date);
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->donor_id
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => __( 'Name', 'peerraiser' ),
            'email_address' => __( 'Email', 'peerraiser' ),
            'donations'     => __( 'Donations', 'peerraiser' ),
            'amount'        => __( 'Total Donated', 'peerraiser' ),
            'date'          => __( 'Date', 'peerraiser' ),
        );

      return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'name'      => array( 'name', true ),
            'donations' => array( 'donation_count', true ),
            'amount'    => array( 'donation_value', false ),
            'date'      => array( 'date', false ),
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'bulk-delete' => 'Delete'
        );

        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden = array( 'donor_id' );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'donors_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $this->items = $this->get_donors( $per_page, $current_page );
    }

    public function process_bulk_action() {
        // Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'peerraiser_delete_donor' ) ) {
                die( 'Sorry, you cannot do that.' );
            }
            else {
                self::delete_donor( absint( $_GET['donor'] ) );
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

          $delete_ids = esc_sql( $_POST['bulk-delete'] );

          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
              self::delete_donor( $id );
          }
        }
    }

    /** Text displayed when no donor data is available */
    public function no_items() {
        _e( 'No donors found.', 'peerraiser' );
    }

    public function get_donors( $per_page = 10, $page_number = 1 ) {
        $donors = new Donor_DB();

        $args = array(
            'number' => $per_page,
            'offset' => ( $page_number - 1 ) * $per_page
        );

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
        }

        return $donors->get_donors( $args );
    }

    /**
     * Delete a donor record.
     *
     * @param int $id donor ID
     */
    public function delete_donor( $id ) {
        $donor = new \PeerRaiser\Model\Donor( $id );
        $donor->delete();
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}pr_donors";

        return $wpdb->get_var( $sql );
    }

    /**
     *
     * @return array
     */
    protected function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'donors', 'peerraiser-list-table' );
    }

}
