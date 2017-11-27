<?php
namespace PeerRaiser\Model\Admin;

// Load the parent class if it doesn't exist.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use \PeerRaiser\Model\Participant;
use \WP_List_Table;

/**
 * Class for displaying the list of participants
 *
 * @since 1.0.1
 */
class Participant_List_Table extends WP_List_Table {

    /** Class constructor */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Participant', 'peerraiser' ),
            'plural'   => __( 'Participants', 'peerraiser' ),
            'ajax'     => false
        ) );
    }

    /**
     * Method for name column
     *
     * @param \WP_User $user
     *
     * @return string
     */
    function column_name( $user ) {
        $participant = new Participant( $user->ID );

        // create a nonce
        $delete_nonce = wp_create_nonce( 'peerraiser_delete_participant_' . $participant->ID );

        $title = '<strong><a href="' . add_query_arg( array( 'participant' => $participant->ID, 'view' => 'participant-details' ) ) . '">' . $participant->full_name . '</a></strong>';

        $actions = array(
            'edit' => sprintf( '<a href="?page=%s&view=%s&participant=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $participant->ID ) ),
            'delete' => sprintf( '<a href="?page=%s&peerraiser_action=%s&participant_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete_participant', absint( $participant->ID ), $delete_nonce ),
        );

        return $title . $this->row_actions( apply_filters( 'peerraiser_participant_actions', $actions ) );
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param WP_User $user
     * @param string  $column_name
     *
     * @return mixed
     */
    public function column_default( $user, $column_name ) {
        $participant = new Participant( $user->ID );

        switch ( $column_name ) {
            case 'email_address':
                return  empty( $participant->email_address) ? '&mdash;' : $participant->email_address;
            case 'user_account':
                return sprintf( '<a href="user-edit.php?user_id=%1$d">%2$s</a>', $user->data->ID, $user->data->user_login);
            case 'raised':
            	$raised = peerraiser_is_test_mode() ? $participant->test_donation_value : $participant->donation_value;
                return peerraiser_money_format( $raised );
            case 'date':
                $date = strtotime( $participant->date );
                return date('m-d-Y', $date);
            default:
                return print_r( $participant, true ); //Show the whole array for troubleshooting purposes
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
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->participant_id
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
            'user_account'  => __( 'User Account', 'peerraiser' ),
            'raised'        => __( 'Total Raised', 'peerraiser' ),
            'date'          => __( 'Date Joined', 'peerraiser' ),
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
            'name'   => array( 'name', true ),
            'raised' => array( 'raised', false ),
            'date'   => array( 'date', false ),
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
        $hidden = array( 'participant_id' );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'participants_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $this->items = $this->get_participants( $per_page, $current_page );
    }

    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'peerraiser_delete_participant' ) ) {
                die( 'Sorry, you cannot do that.' );
            }
            else {
                self::delete_participant( absint( $_GET['participant'] ) );
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

            $delete_ids = esc_sql( $_POST['bulk-delete'] );

            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_participant( $id );
            }
        }
    }

    public function get_participants( $per_page = 10, $page_number = 1 ) {
        $participants = new Participant();

        $args = array(
            'number' => $per_page,
            'offset' => ( $page_number - 1 ) * $per_page
        );

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
        }

        return $participants->get_participants( $args );
    }

    /** Text displayed when no participant data is available */
    public function no_items() {
        _e( 'No participants found.', 'peerraiser' );
    }

    /**
     * Delete a participant record.
     *
     * @param int $id participant ID
     */
    public function delete_participant( $id ) {
        $participant = new \PeerRaiser\Model\Participant( $id );
        $participant->delete();
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count() {
        $participant = new \PeerRaiser\Model\Participant();
        $participant->get_total_participants();
    }

    /**
     *
     * @return array
     */
    protected function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'participants', 'peerraiser-list-table' );
    }

}
