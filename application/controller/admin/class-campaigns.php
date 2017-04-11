<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Controller\Base;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;
use \PeerRaiser\Model\Campaign;
use \PeerRaiser\Model\Currency;
use \PeerRaiser\Model\Admin\Campaign_List_Table;
use \PeerRaiser\Core\Setup;
use \PeerRaiser\Helper\Stats;
use \PeerRaiser\Helper\View;

class Campaigns extends Base {

    public function register_actions() {
		add_action( 'cmb2_admin_init',                      array( $this, 'register_meta_boxes' ) );
        add_action( 'peerraiser_page_peerraiser-campaigns', array( $this, 'load_assets' ) );
		add_action( 'peerraiser_add_campaign',	            array( $this, 'handle_add_campaign' ) );
		add_action( 'peerraiser_update_campaign',           array( $this, 'handle_update_campaign' ) );
		add_action( 'peerraiser_delete_campaign',           array( $this, 'delete_campaign' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $default_views = array( 'list', 'add', 'summary' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_campaign_admin_view', 'list', $view );

        // Assign data to the view
        $view_args = array(
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'admin_url'            => get_admin_url(),
            'list_table'           => new Campaign_List_Table(),
        );

	    if ( $view === 'summary' ) {
		    $view_args['campaign'] = new \PeerRaiser\Model\Campaign( $_REQUEST['campaign'] );
	    }

        $this->assign( 'peerraiser', $view_args );

        // Render the view
        $this->render( 'backend/campaign-' . $view );
    }

    public function load_assets() {
        parent::load_assets();

        // Register and enqueue styles
        wp_register_style(
            'peerraiser-admin',
            Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
            array('peerraiser-font-awesome'),
            Setup::get_plugin_config()->get('version')
        );
        wp_register_style(
            'peerraiser-admin-campaigns',
            Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-campaigns.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-campaigns' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-campaigns',
            Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-campaigns.js',
            array( 'jquery', 'peerraiser-admin' ),
            Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-campaigns' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-campaigns',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
            )
        );

    }

	public function register_meta_boxes() {
		$campaigns_model = new \PeerRaiser\Model\Admin\Campaigns();
		$campaign_field_groups = $campaigns_model->get_fields();
		foreach ($campaign_field_groups as $field_group) {
			$cmb = new_cmb2_box( array(
				'id'           => $field_group['id'],
				'title'        => $field_group['title'],
				'object_types' => array( 'post' ),
				'hookup'       => false,
				'save_fields'  => false,
			) );
			foreach ($field_group['fields'] as $key => $value) {
				$cmb->add_field($value);
			}
         }
    }

    public function display_fundraisers_list() {
        global $post;
        $paged = isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1;

        $campaigns    = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_fundraisers = $campaigns->get_fundraisers( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_fundraisers,
            'paged'        => isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1,
            'paged_name'   => 'fundraisers_page'
        );
        $pagination = View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_fundraisers' => $campaign_fundraisers->found_posts,
            'pagination'            => $pagination,
            'currency_symbol'       => $currency_symbol,
            'fundraisers'           => $campaign_fundraisers->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-fundraisers' );
    }

    public function display_donations_list() {
        global $post;
        $paged = isset($_GET['donations_page']) ? $_GET['donations_page'] : 1;

        $campaigns          = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_donations = $campaigns->get_donations( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_donations,
            'paged'        => isset($_GET['donations_page']) ? $_GET['donations_page'] : 1,
            'paged_name'   => 'donations_page'
        );
        $pagination = View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_donations' => $campaign_donations->found_posts,
            'pagination'          => $pagination,
            'currency_symbol'     => $currency_symbol,
            'donations'           => $campaign_donations->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-donations' );
    }

    public function display_teams_list() {
        global $post;
        $paged = isset($_GET['teams_page']) ? $_GET['teams_page'] : 1;

        $campaigns      = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_teams = $campaigns->get_teams( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_teams,
            'paged'        => isset($_GET['teams_page']) ? $_GET['teams_page'] : 1,
            'paged_name'   => 'teams_page'
        );
        $pagination = View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_teams' => $campaign_teams->found_posts,
            'pagination'      => $pagination,
            'currency_symbol' => $currency_symbol,
            'teams'           => $campaign_teams->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-teams' );
    }

    public function display_campaign_stats( $post ) {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $end_date = get_post_meta( $post->ID, '_peerraiser_campaign_end_date', true );
        $goal = get_post_meta( $post->ID, '_peerraiser_campaign_goal', true );
        $days_left = 0;

        if ( !empty( $end_date ) ) {
            $today = time();
            $difference = $end_date - $today;
            $days_left = floor($difference/60/60/24);
        }

        $total_donations = Stats::get_total_donations_by_campaign( $post->ID );

        $view_args = array(
            'currency_symbol' => $currency_symbol,
            'has_goal' => ( $goal !== '0.00' ),
            'has_end_date' => !empty( $end_date ),
            'total_donations' => number_format_i18n( $total_donations, 2),
            'goal_percent' => ( !empty( $goal ) && $goal !== '0.00' ) ? number_format( ( $total_donations / $goal ) * 100, 2) : 0,
            'days_left' => ( $days_left < 0 ) ? __( 'Campaign Ended', 'peerraiser' ) : $days_left,
            'days_left_class' => ( $days_left < 0 ) ? 'negative' : 'positive',
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-stats' );
    }

	/**
	 * Handle "Add Campaign" form submission
	 *
	 * @since 1.0.0
	 */
    public function handle_add_campaign() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_campaign_nonce' ) ) {
			die( __('Security check failed.', 'peerraiser' ) );
		}

		$validation = $this->is_valid_campaign();
		if ( ! $validation['is_valid'] ) {
			return;
		}

		$campaign = new Campaign();

	    $this->add_fields( $campaign );
	    $campaign->save();

		// Create redirect URL
		$location = add_query_arg( array(
			'page' => 'peerraiser-campaigns',
			'view' => 'summary',
			'campaign' => $campaign->ID
		), admin_url( 'admin.php' ) );

		// Redirect to the edit screen for this new donation
		wp_safe_redirect( $location );
	}

	public function handle_update_campaign() {
    	$campaigns_model = new \PeerRaiser\Model\Admin\Campaigns();
    	error_log( print_r( $campaigns_model->get_required_field_ids(), 1 ) );
	}

	/**
	 * Handle "delete campaign" action
	 *
	 * @since 1.0.0
	 */
	public function delete_campaign() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_delete_campaign_' . $_REQUEST['campaign_id'] ) ) {
			die( __('Security check failed.', 'peerraiser' ) );
		}

		// Delete the donation
		$campaign = new \PeerRaiser\Model\Campaign( $_REQUEST['campaign_id'] );
		$campaign->delete();

		// Create redirect URL
		$location = add_query_arg( array(
			'page' => 'peerraiser-campaigns'
		), admin_url( 'admin.php' ) );

		wp_safe_redirect( $location );
	}

	/**
	 * Checks if the fields are valid
	 *
	 * @todo Check formatting of goal amounts
	 * @since     1.0.0
	 * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
	 */
	private function is_valid_campaign() {
		$campaigns_model = new \PeerRaiser\Model\Admin\Campaigns();
		$required_fields = $campaigns_model->get_required_field_ids();

		$data = array(
			'is_valid'     => true,
			'field_errors' => array(),
		);

		// Make sure campaign name isn't already taken
        $campaign_exists = term_exists( $_REQUEST['_peerraiser_campaign_name'], 'peerraiser_campaign' );

        if ( $campaign_exists !== 0 && $campaign_exists !== null ) {
            $data['field_errors'][ '_peerraiser_campaign_name' ] = __( 'This campaign name already exists', 'peerraiser' );
        }

        // Check required fields
		foreach ( $required_fields as $field ) {
			if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
				$data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
			}
		}

		if ( ! empty( $data['field_errors'] ) ) {
			$message = __( 'There was an issue creating this campaign. Please fix the errors below.', 'peerraiser' );
			Admin_Notices_Model::add_notice( $message, 'notice-error', true );

			wp_localize_script(
				'jquery',
				'peerraiser_field_errors',
				$data['field_errors']
			);

			$data['is_valid'] = false;
		}

		return $data;
	}

	private function add_fields( $campaign) {
		$campaigns_model = new \PeerRaiser\Model\Admin\Campaigns();
		$field_ids = $campaigns_model->get_field_ids();

		$campaign->campaign_name = $_REQUEST['_peerraiser_campaign_name'];

		foreach ( $field_ids as $field_id ) {
			$short_field = substr( $field_id, 12 );

			switch ( $field_id ) {
				case "_peerraiser_start_date" :
					if ( isset( $_REQUEST['_peerraiser_start_date'] ) ) {
						$campaign->start_date = $_REQUEST['_peerraiser_start_date'];
					} else {
						$campaign->start_date = current_time( 'mysql' );
					}
					break;
				default :
					if ( isset( $_REQUEST[$field_id] ) ) {
						error_log( $short_field . ' = ' . $_REQUEST[$field_id] );
						$campaign->$short_field = $_REQUEST[$field_id];
					}
					break;
			}
		}
	}

}
