<?php
namespace PeerRaiser\Model\Admin;

// Load the parent class if it doesn't exist.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use \WP_List_Table;
use \WP_Term_Query;
use \PeerRaiser\Model\Campaign;

/**
 * Class for displaying the list of campaigns
 *
 * @since 1.0.1
 */
class Campaign_List_Table extends WP_List_Table {

    /** Class constructor */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Campaign', 'peerraiser' ),
            'plural'   => __( 'Campaigns', 'peerraiser' ),
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
		$delete_nonce = wp_create_nonce( 'peerraiser_delete_campaign_' . $item['id'] );

		$campaign = new Campaign(  $item['id'] );

		$title = '<a href="' . add_query_arg( array( 'campaign' => $item['id'], 'view' => 'campaign-details' ) ) . '">' . $campaign->campaign_name . '</a>';


		$actions = array(
			'edit' => sprintf( '<a href="?page=%s&view=%s&campaign=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $item['id'] ) ),
			'delete' => sprintf( '<a href="?page=%s&peerraiser_action=%s&campaign_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete_campaign', absint( $item['id'] ), $delete_nonce ),
		);

		return $title . $this->row_actions( apply_filters( 'peerraiser_campaign_actions', $actions ) );
	}

    /**
     * Retrieve the view types
     *
     * @access public
     * @since 1.4
     * @return array $views All the views available
     */
    public function get_views() {
        $base           = admin_url('admin.php?page=peerraiser-campaigns');

        $current        = isset( $_GET['status'] ) ? $_GET['status'] : '';

        $views = array(
            'all'      => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'peerraiser')),
            'active'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'peerraiser')),
            'ended' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'ended', $base ), $current === 'ended' ? ' class="current"' : '', __('Ended', 'peerraiser')),
        );

        return $views;
    }

    public function current_action() {
        if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
            return false;

        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];

        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return $_REQUEST['action2'];

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
            case 'count' :
                return $item[ $column_name ];
            case 'donations' :
                return $item[ $column_name ];
            case 'teams' :
                return count( $item[ $column_name ] );
            case 'raised' :
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
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'name'        => __( 'Name', 'peerraiser' ),
            'count'       => __( 'Fundraisers', 'peerraiser' ),
            'donations'   => __( 'Donations', 'peerraiser' ),
            'teams'       => __( 'Teams', 'peerraiser' ),
            'raised'      => __( 'Raised', 'peerraiser' ),
        );

      return apply_filters( 'peerraiser_campaign_columns', $columns );
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
        );

        return apply_filters( 'peerraiser_campaign_sortable_columns', $sortable_columns);
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

        return apply_filters( 'peerraiser_campaign_bulk_actions', $actions );
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden = array( 'campaign_id' );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'campaigns_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $campaigns = $this->get_campaigns( $per_page, $current_page );

        $this->items = $campaigns;
    }

    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'peerraiser_delete_campaign' ) ) {
                die( 'Sorry, you cannot do that.' );
            }
            else {
                self::delete_campaign( absint( $_GET['campaign'] ) );

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
            self::delete_campaign( $id );
          }

          wp_redirect( esc_url( add_query_arg() ) );
          exit;
        }
    }

    /** Text displayed when no campaign data is available */
    public function no_items() {
        _e( 'No campaigns found.', 'peerraiser' );
    }

    /**
     * Retrieve campaign’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public function get_campaigns( $per_page = 10, $page_number = 1 ) {

        $args = array(
            'taxonomy'   => array( 'peerraiser_campaign' ),
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

        $donations = new \PeerRaiser\Model\Database\Donation();
        $teams     = new \PeerRaiser\Model\Admin\Teams();

        foreach ( $term_query->terms as $term ) {
	        $campaign = new \PeerRaiser\Model\Campaign( $term->term_id );

            $results[] = array(
                'id'          => $campaign->ID,
                'name'        => $campaign->campaign_name,
                'count'       => $term->count,
                'donations'   => $donations->get_donations( array( 'campaign_id' => $term->term_id ), true ),
                'teams'       => $teams->get_teams_by_campaign( (int) $term->term_id ),
                'raised'      => peerraiser_money_format( $campaign->donation_value ),
            );
        }

        return $results;
    }

    /**
     * Delete a campaign record.
     *
     * @param int $id campaign ID
     */
    public function delete_campaign( $id ) {
        //TODO: delete term by id
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
