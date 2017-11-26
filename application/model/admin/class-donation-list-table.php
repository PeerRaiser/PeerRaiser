<?php
namespace PeerRaiser\Model\Admin;

// Load the parent class if it doesn't exist.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use PeerRaiser\Model\Database\Donation_Table;
use PeerRaiser\Model\Donation;
use \PeerRaiser\Model\Donor;
use \WP_List_Table;

/**
 * Class for displaying the list of donations
 *
 * @since 1.0.1
 */
class Donation_List_Table extends WP_List_Table {

    /** Class constructor */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Donation', 'peerraiser' ),
            'plural'   => __( 'Donations', 'peerraiser' ),
            'ajax'     => false
        ) );
    }

    /**
     * Method for name column
     *
     * @param array $donation an array of DB data
     *
     * @return string
     */
    function column_name( $donation ) {
        // create a nonce
        $delete_nonce = wp_create_nonce( 'peerraiser_delete_donation_' . $donation->donation_id );

        $title = '<strong><a href="' . add_query_arg( array( 'donation' => $donation->donation_id, 'view' => 'summary' ) ) . '">Donation #' . $donation->donation_id . '</a></strong>';

        $actions = array(
            'edit' => sprintf( '<a href="?page=%s&view=%s&donation=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $donation->donation_id ) ),
            'delete' => sprintf( '<a href="?page=%s&peerraiser_action=%s&donation_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete_donation', absint( $donation->donation_id ), $delete_nonce ),
        );

        return $title . $this->row_actions( apply_filters( 'peerraiser_donation_actions', $actions ) );
    }

    /**
     * Retrieve the view types
     *
     * @access public
     * @since 1.4
     * @return array $views All the views available
     */
    public function get_views() {
        $base           = admin_url('admin.php?page=peerraiser-donations');

        $current        = isset( $_GET['status'] ) ? $_GET['status'] : '';

        $views = array(
            'all'      => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'peerraiser')),
            'completed'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'completed', $base ), $current === 'completed' ? ' class="current"' : '', __('Completed', 'peerraiser')),
            'pending' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __('Pending', 'peerraiser')),
            'refunded' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'refunded', $base ), $current === 'refunded' ? ' class="current"' : '', __('Refunded', 'peerraiser')),
            'failed' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'failed', $base ), $current === 'failed' ? ' class="current"' : '', __('Failed', 'peerraiser')),
        );

        return $views;
    }

    public function current_action() {
        if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
            return false;

        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];

        return false;
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $donation
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $donation, $column_name ) {
        switch ( $column_name ) {
            case 'donor':
                $donor = new Donor( $donation->donor_id );
                return '<a href="' . add_query_arg( array( 'donor' => $donation->donor_id, 'view' => 'summary' ), 'admin.php?page=peerraiser-donors' ) . '">' . $donor->full_name . '</a>';
            case 'amount':
                return empty( $donation->total ) ? '$0.00' : '$'. number_format( $donation->total, 2 );
            case 'date':
                $date = strtotime( $donation->$column_name );
                return date('m-d-Y', $date);
            case 'status':
                return ucfirst( $donation->$column_name );
            default:
                return print_r( $donation, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $donation
     *
     * @return string
     */
    function column_cb( $donation ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $donation->donation_id
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'     => '<input type="checkbox" />',
            'name'   => __( 'Donation', 'peerraiser' ),
            'donor'  => __( 'Donor', 'peerraiser' ),
            'amount' => __( 'Amount', 'peerraiser' ),
            'date'   => __( 'Date', 'peerraiser' ),
            'status' => __( 'Status', 'peerraiser' ),
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
            'donation' => array( 'donation', true ),
            'amount'   => array( 'total', false ),
            'date'     => array( 'date', false ),
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
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'donations_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $donations = $this->get_donations( $per_page, $current_page );

        $this->items = $donations;
    }

    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ( 'bulk-delete' === $this->current_action() ) {
            foreach ( $_POST['bulk-delete'] as $id ) {
                self::delete_donation( absint( $id ) );
            }
        }
    }

    /** Text displayed when no donation data is available */
    public function no_items() {
        _e( 'No donations found.', 'peerraiser' );
    }

    /**
     * Retrieve donation’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public function get_donations( $per_page = 10, $page_number = 1 ) {
        $donations = new Donation_Table();

        $args = array(
            'number' => $per_page,
            'offset' => ( $page_number - 1 ) * $per_page,
        );

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
        }

        if ( ! empty( $_REQUEST['status'] ) ) {
            $args['status'] = $_REQUEST['status'];
        }

        return $donations->get_donations( $args );
    }

    /**
     * Delete a donation record.
     *
     * @param int $id donation ID
     */
    public function delete_donation( $id ) {
        $donation = new Donation( $id );
        $donation->delete();
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}pr_donations";

        return $wpdb->get_var( $sql );
    }

    /**
     *
     * @return array
     */
    protected function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'donations', 'peerraiser-list-table' );
    }

}
