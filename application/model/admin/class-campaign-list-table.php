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
     * @param array $campaign PeerRaiser campaign
     *
     * @return string
     */
	function column_name( $campaign ) {
		// create a nonce
		$delete_nonce = wp_create_nonce( 'peerraiser_delete_campaign_' . $campaign->ID );

		$campaign = new Campaign(  $campaign->ID );

		$title = '<a href="' . add_query_arg( array( 'campaign' => $campaign->ID, 'view' => 'campaign-details' ) ) . '">' . $campaign->campaign_name . '</a>';


		$actions = array(
			'edit' => sprintf( '<a href="?page=%s&view=%s&campaign=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $campaign->ID ) ),
			'delete' => sprintf( '<a href="?page=%s&peerraiser_action=%s&campaign_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete_campaign', absint( $campaign->ID ), $delete_nonce ),
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
            'private'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'private', $base ), $current === 'private' ? ' class="current"' : '', __('Private', 'peerraiser')),
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
     * @param object $campaign
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $campaign, $column_name ) {
    	$admin_campaigns_model = new \PeerRaiser\Model\Admin\Campaigns();

        switch ( $column_name ) {
            case 'count' :
                return $campaign->get_total_fundraisers();
            case 'donations' :
                return $campaign->donation_count;
            case 'teams' :
                return $campaign->get_total_teams();
            case 'raised' :
                return peerraiser_money_format( $campaign->donation_value );
	        case 'status' :
		        return $admin_campaigns_model->get_campaign_status_by_key( $campaign->status );
            default:
                return print_r( $campaign, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $campaign
     *
     * @return string
     */
    public function column_cb( $campaign ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $campaign->ID
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
            'status'      => __( 'Status', 'peerraiser' ),
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
	    $campaign_model = new \PeerRaiser\Model\Campaign();

    	$args = array(
			'count'      => $per_page,
	        'offset'     => $per_page * ( $page_number - 1 )
        );

	    if ( ! empty( $_REQUEST['orderby'] ) ) {
		    $args['orderby'] = $_REQUEST['orderby'];
		    $args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
	    }

	    if ( ! empty( $_REQUEST['status'] ) ) {
		    $args['meta_query'] = array(
			    array(
				    'key'       => '_peerraiser_campaign_status',
				    'value'     => $_REQUEST['status'],
				    'compare'   => '='
			    )
		    );
	    }

	    return $campaign_model->get_campaigns( $args );
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
