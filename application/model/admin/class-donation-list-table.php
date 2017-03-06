<?php
namespace PeerRaiser\Model\Admin;

// Load the parent class if it doesn't exist.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

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
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name( $item ) {
        // create a nonce
        $delete_nonce = wp_create_nonce( 'peerraiser_delete_donation' );

        $donor = new \PeerRaiser\Model\Database\Donor();
        $donor = $donor->get_donors( array( 'donor_id' => $item['donor_id'] ) );

        $title = '<a href="' . add_query_arg( array( 'donation' => $item['donation_id'], 'view' => 'donation-details' ) ) . '">' . $donor[0]->donor_name . '</a>';


        $actions = array(
            'view' => sprintf( '<a href="?page=%s&action=%s&donation=%s">View</a>', esc_attr( $_REQUEST['page'] ), 'view', absint( $item['donor_id'] ) ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&donation=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['donor_id'] ), $delete_nonce ),
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
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'donation_id':
                return '#' . $item[ $column_name ];
            case 'amount':
                return empty( $item[ 'total' ] ) ? '$0.00' : '$'. number_format( $item[ 'total' ], 2 );
            case 'date':
                $date = strtotime( $item[ $column_name ] );
                return date('m-d-Y', $date);
            case 'status':
                return ucfirst( $item[ $column_name ] );
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
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['donation_id']
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'donation_id'  => __( 'ID', 'peerraiser' ),
            'name'         => __( 'Name', 'peerraiser' ),
            'amount'       => __( 'Amount', 'peerraiser' ),
            'date'         => __( 'Date', 'peerraiser' ),
            'status'       => __( 'Status', 'peerraiser' ),
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
            'donation_id' => array( 'id', true ),
            'amount'      => array( 'amount', false ),
            'date'        => array( 'date', false ),
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
        if ( 'delete' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'peerraiser_delete_donation' ) ) {
                die( 'Sorry, you cannot do that.' );
            }
            else {
                self::delete_donation( absint( $_GET['donation'] ) );

                wp_redirect( esc_url( add_query_arg() ) );
                exit;
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

          $delete_ids = esc_sql( $_POST['bulk-delete'] );

          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
            self::delete_donation( $id );
          }

          wp_redirect( esc_url( add_query_arg() ) );
          exit;
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

        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}pr_donations";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $results = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $results;

    }

    /**
     * Delete a donation record.
     *
     * @param int $id donation ID
     */
    public function delete_donation( $id ) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}pr_donations",
            array( 'donation_id' => $id ),
            array( '%d' )
        );
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

}
