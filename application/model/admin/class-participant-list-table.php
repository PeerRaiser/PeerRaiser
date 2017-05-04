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
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		// create a nonce
		$delete_nonce = wp_create_nonce( 'peerraiser_delete_participant_' . $item->participant_id );

		$participant = new Participant( $item->participant_id );

		$title = '<strong><a href="' . add_query_arg( array( 'participant' => $item->participant_id, 'view' => 'participant-details' ) ) . '">' . $participant->full_name . '</a></strong>';

		$actions = array(
			'view' => sprintf( '<a href="?page=%s&view=%s&participant=%s">View</a>', esc_attr( $_REQUEST['page'] ), 'summary', absint( $item->participant_id ) ),
			'delete' => sprintf( '<a href="?page=%s&peerraiser_action=%s&participant_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete_participant', absint( $item->participant_id ), $delete_nonce ),
		);

		return $title . $this->row_actions( apply_filters( 'peerraiser_participant_actions', $actions ) );
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
		$participant = new Participant( $item->participant_id );

		switch ( $column_name ) {
			case 'email_address':
				return  empty( $participant->email_address) ? '&mdash;' : $participant->email_address;
			case 'donations' :
				return $participant->donation_count;
			case 'amount':
				return '$'. number_format( $participant->donation_value, 2 );
			case 'date':
				$date = strtotime( $participant->date );
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
			'name'   => array( 'name', true ),
			'amount' => array( 'amount', false ),
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

		$participants = new Participant_DB();
		$participants = $participants->get_participants( array(
			'number' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page
		) );

		$this->items = $participants;
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