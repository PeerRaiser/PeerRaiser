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
     * @param array $team an array of Team objects
     *
     * @return string
     */
    function column_name( $team ) {
        // create a nonce
        $delete_nonce = wp_create_nonce( 'peerraiser_delete_team_' . $team->ID );

        $title = '<strong><a href="' . add_query_arg( array( 'team' => $team->ID, 'view' => 'summary' ) ) . '">' . $team->team_name . '</a></strong>';

        $actions = array(
            'edit'   => sprintf( '<a href="?page=%1$s&view=%2$s&team=%3$s">%4$s</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $team->ID ), __( 'Edit', 'peerraiser') ),
            'view'   => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $team->get_permalink() ), __( 'View', 'peerraiser' ) ),
            'delete' => sprintf( '<a href="?page=%1$s&peerraiser_action=%2$s&team_id=%3$s&_wpnonce=%4$s">%5$s</a>', esc_attr( $_REQUEST['page'] ), 'delete_team', absint( $team->ID ), $delete_nonce, __( 'Delete', 'peerraiser' ) ),
        );

        return $title . $this->row_actions( apply_filters( 'peerraiser_team_actions', $actions ) );
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array  $team
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $team, $column_name ) {
        switch ( $column_name ) {
            case 'count' :
                return $team->get_total_members();
                break;
            case 'raised' :
                return peerraiser_is_test_mode() ? peerraiser_money_format( $team->test_donation_value ) : peerraiser_money_format( $team->donation_value );
                break;
            case 'leader' :
                $user_info = get_userdata( $team->team_leader );
                return sprintf( '<a href="user-edit.php?user_id=%1$d">%2$s %3$s</a>', $team->team_leader, $user_info->first_name, $user_info->last_name);
                break;
            case 'campaign' :
                $campaign = new \PeerRaiser\Model\Campaign( $team->campaign_id );
                return sprintf( '<a href="admin.php?page=peerraiser-campaigns&view=summary&campaign=%1$d">%2$s</a>', $campaign->ID, $campaign->campaign_name );
                break;
            default:
                return print_r( $team, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $team
     *
     * @return string
     */
    function column_cb( $team ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $team->ID
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'name'     => __( 'Name', 'peerraiser' ),
            'leader'   => __( 'Team Leader', 'peerraiser' ),
            'campaign' => __( 'Campaign', 'peerraiser' ),
            'count'    => __( 'Fundraisers', 'peerraiser' ),
            'raised'   => __( 'Raised', 'peerraiser' ),
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
            'raised' => array( 'raised', false ),
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
        $team_model = new \PeerRaiser\Model\Team();

        $args = array(
            'count'      => $per_page,
            'offset'     => $per_page * ( $page_number - 1 )
        );

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
        }

        return $team_model->get_teams( $args );
    }

    /**
     * Delete a team record.
     *
     * @param int $id team ID
     */
    public function delete_team( $id ) {
        $team = new \PeerRaiser\Model\Team( $id );
        $team->delete();
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count() {
        $team = new \PeerRaiser\Model\Team();
        return $team->get_total_teams();
    }

    /**
     *
     * @return array
     */
    protected function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'teams', 'peerraiser-list-table' );
    }

}
