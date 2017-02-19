<?php
namespace PeerRaiser\Model\Admin;

// Load the parent class if it doesn't exist.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use \WP_List_Table;
use \WP_Term_Query;

/**
 * Class for displaying the list of teams
 *
 * @since 1.0.1
 */
class Team_List_Table extends WP_List_Table {

    /** Class constructor */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Team', 'peerraiser' ),
            'plural'   => __( 'Teams', 'peerraiser' ),
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
        $delete_nonce = wp_create_nonce( 'peerraiser_delete_team' );

        $title = '<strong><a href="' . add_query_arg( array( 'team' => $item['id'], 'view' => 'team-details' ) ) . '">' . $item['name'] . '</a> <span class="meta">('.$item['id'].')</span></strong>';

        $actions = array();

        return $title . $this->row_actions( $actions );
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
            case 'count':
                return $item[ $column_name ];
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
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'    => '<input type="checkbox" />',
            'name'  => __( 'Name', 'peerraiser' ),
            'count' => __( 'Members', 'peerraiser' ),
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
            'name'  => array( 'name', true ),
            'count' => array( 'count', false ),
            // 'date'   => array( 'date', false ),
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
        $hidden = array( 'team_id' );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'teams_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $teams = $this->get_teams( $per_page, $current_page );

        $this->items = $teams;
    }

    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'peerraiser_delete_team' ) ) {
                die( 'Sorry, you cannot do that.' );
            }
            else {
                self::delete_team( absint( $_GET['team'] ) );

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
            self::delete_team( $id );
          }

          wp_redirect( esc_url( add_query_arg() ) );
          exit;
        }
    }

    /** Text displayed when no team data is available */
    public function no_items() {
        _e( 'No teams found.', 'peerraiser' );
    }

    /**
     * Retrieve team’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public function get_teams( $per_page = 10, $page_number = 1 ) {

        $args = array(
            'taxonomy'   => array( 'peerraiser_team' ),
            'count'      => $per_page,
            'offset'     => $per_page * ( $page_number - 1 ),
            'hide_empty' => false,
        );

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
        }

        $term_query = new WP_Term_Query( $args );

        $results = array();
        foreach ( $term_query->terms as $term ) {
            $results[] = array(
                'id'    => $term->term_id,
                'name'  => $term->name,
                'count' => $term->count,
            );
        }

        return $results;

    }

    /**
     * Delete a team record.
     *
     * @param int $id team ID
     */
    public function delete_team( $id ) {
        // TODO: Delete term by id
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count() {
        // TODO: Get term count
    }

}
