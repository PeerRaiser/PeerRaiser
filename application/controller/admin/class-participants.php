<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Model\Participant as Participant_Model;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;

class Participants extends \PeerRaiser\Controller\Base {

	public function register_actions() {
		add_action( 'cmb2_admin_init',                         array( $this, 'register_meta_boxes' ) );
		add_action( 'peerraiser_page_peerraiser-participants', array( $this, 'load_assets' ) );
	}

	/**
	 * Singleton to get only one Campaigns controller
	 *
	 * @return    \PeerRaiser\Controller\Admin\Participants
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @see \PeerRaiser\Core\View::render_page
	 */
	public function render_page() {
		$plugin_options = get_option( 'peerraiser_options', array() );

		$default_views = array( 'list', 'add', 'summary' );

		// Get the correct view
		$view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
		$view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_participant_admin_view', 'list', $view );

		$view_args = array(
			'admin_url'         => get_admin_url(),
			'list_table'        => new \PeerRaiser\Model\Admin\Participant_List_Table(),
		);

		if ( $view === 'summary' ) {
			$view_args['participant'] = new \PeerRaiser\Model\Participant( $_REQUEST['participant'] );
		}

		$this->assign( 'peerraiser', $view_args );

		$this->render( 'backend/participant-' . $view );
	}

	public function register_meta_boxes() {

		$participants_model = new \PeerRaiser\Model\Admin\Participants_Admin();
		$participant_field_groups = $participants_model->get_fields();

		foreach ($participant_field_groups as $field_group) {
			$cmb = new_cmb2_box( array(
				'id'           => $field_group['id'],
				'title'         => $field_group['title'],
				'object_types'  => array( 'participant' ),
				'context'       => $field_group['context'],
				'priority'      => $field_group['priority'],
			) );
			foreach ($field_group['fields'] as $key => $value) {
				$cmb->add_field($value);
			}
		}

	}

	public function load_assets() {
		parent::load_assets();

		// Register and enqueue styles
		wp_register_style(
			'peerraiser-admin',
			\PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
			array('peerraiser-font-awesome'),
			\PeerRaiser\Core\Setup::get_plugin_config()->get('version')
		);
		wp_register_style(
			'peerraiser-admin-participants',
			\PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-participants.css',
			array('peerraiser-font-awesome', 'peerraiser-admin'),
			\PeerRaiser\Core\Setup::get_plugin_config()->get('version')
		);
		wp_enqueue_style( 'peerraiser-admin' );
		wp_enqueue_style( 'peerraiser-admin-participants' );
		wp_enqueue_style( 'peerraiser-font-awesome' );
		wp_enqueue_style( 'peerraiser-select2' );

		// Register and enqueue scripts
		wp_register_script(
			'peerraiser-admin-participants',
			\PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-participants.js',
			array( 'jquery', 'peerraiser-admin' ),
			\PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
			true
		);
		wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
		wp_enqueue_script( 'peerraiser-admin-participants' );
		wp_enqueue_script( 'peerraiser-select2' );

		// Localize scripts
		wp_localize_script(
			'peerraiser-admin-participants',
			'peerraiser_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'template_directory' => get_template_directory_uri(),
			)
		);

	}
}
