<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Model\Donor as Donor_Model;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;

class Donors extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                     array( $this, 'register_meta_boxes' ) );
		add_action( 'peerraiser_page_peerraiser-donors',   array( $this, 'load_assets' ) );
        add_action( 'add_meta_boxes',                      array( $this, 'add_meta_boxes' ) );
        add_action( 'admin_menu',                          array( $this, 'maybe_replace_submit_box' ) );
        add_action( 'user_register',                       array( $this, 'maybe_connect_user_to_donor' ) );
        add_action( 'manage_pr_donor_posts_custom_column', array( $this, 'manage_columns' ) );
        add_action( 'peerraiser_add_donor',          	   array( $this, 'handle_add_donor' ) );
    }

    /**
     * Singleton to get only one Campaigns controller
     *
     * @return    \PeerRaiser\Controller\Admin\Donors
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

        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

		$default_views = array( 'list', 'add', 'summary' );

		// Get the correct view
		$view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
		$view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_donation_admin_view', 'list', $view );

	    $view_args = array(
		    'currency_symbol'   => $currency_symbol,
		    'standard_currency' => $plugin_options['currency'],
		    'admin_url'         => get_admin_url(),
		    'list_table'        => new \PeerRaiser\Model\Admin\Donor_List_Table(),
	    );

	    if ( $view === 'summary' ) {
		    $view_args['donor'] = new \PeerRaiser\Model\Donor( $_REQUEST['donor'] );
		    $view_args['profile_image_url'] = $view_args['donor']->get_donor_image();
	    }

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/donor-' . $view );
    }

    public function register_meta_boxes() {

        $donors_model = new \PeerRaiser\Model\Admin\Donors();
        $donor_field_groups = $donors_model->get_fields();

        foreach ($donor_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'         => $field_group['title'],
                'object_types'  => array( 'pr_donor' ),
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
            'peerraiser-admin-donors',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-donors.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-donors' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-donors',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-donors.js',
            array( 'jquery', 'peerraiser-admin' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-donors' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-donors',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
            )
        );

    }


    public function add_meta_boxes() {
        if ( !$this->is_edit_page( 'edit' ) )
            return;

        add_meta_box(
            'donor_info',
            __('Donor Info', 'peerraiser'),
            array( $this, 'display_donor_box' ),
            'pr_donor',
            'normal',
            'high'
        );

        add_meta_box(
            'donor_donations',
            __('Donations', 'peerraiser'),
            array( $this, 'display_donation_list' ),
            'pr_donor'
        );

    }

    public function maybe_replace_submit_box() {

        if ( !$this->is_edit_page( 'edit' ) )
            return;

        remove_meta_box('submitdiv', 'pr_donor', 'core');
        add_meta_box('submitdiv', __('Donor'), array( $this, 'get_submit_box'), 'pr_donor', 'side', 'low');
    }

    public function get_submit_box( $object ) {
        $post_type_object = get_post_type_object($object->post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        $is_published = ( in_array( $object->post_status, array('publish', 'future', 'private') ) );

        $view_args = array(
            'object'             => $object,
            'can_publish'        => $can_publish,
            'is_published'       => $is_published,
            'lifetime_donations' => $this->get_lifetime_donation_amount( $object->ID ),
            'largest_donation'   => $this->get_largest_donation_amount( $object->ID ),
            'latest_donation'    => $this->get_latest_donation_amount( $object->ID ),
            'first_donation'     => $this->get_first_donation_amount( $object->ID ),
        );

        $this->assign( 'peerraiser', $view_args );

        $view_file = ( $is_published ) ? 'backend/partials/donor-box-edit' : 'backend/partials/donor-box-add';

        $this->render( $view_file );

    }

    public function display_donor_box( $object ) {
        $donor_user_account = get_post_meta( $object->ID, '_donor_user_account', true);
        $donor_user_info = get_userdata($donor_user_account);

        $view_args = array(
            'profile_image_url' => ( !empty($donor_user_account) ) ? get_avatar_url( $donor_user_account ) : \PeerRaiser\Core\Setup::get_plugin_config()->get('images_url') . 'profile-mask.png',
            'first_name' => get_post_meta( $object->ID, '_donor_first_name', true ),
            'last_name' => get_post_meta( $object->ID, '_donor_last_name', true ),
            'donor_email' => get_post_meta( $object->ID, '_donor_email', true ),
            'donor_id' => $object->ID,
            'donor_user_account' => ( !empty($donor_user_account) ) ? '<a href="user-edit.php?user_id='.$donor_user_account.'">'.$donor_user_info->user_login.'</a>' : __('None', 'peerraiser'),
            'donor_since' => get_the_date(),
            'donor_class' => ( !empty($donor_user_account) ) ? 'user' : 'guest',
        );
        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/donor-card' );
    }

    public function display_donation_list() {
        global $post;
        $paged = isset($_GET['donations_page']) ? $_GET['donations_page'] : 1;

        $donors_model = new \PeerRaiser\Model\Admin\Donors();
        $donor_donations = $donors_model->get_donations( $post->ID, $paged );

        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $donor_donations,
            'paged' => isset($_GET['donations_page']) ? $_GET['donations_page'] : 1,
            'paged_name' => 'donations_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_donations' => $donor_donations->found_posts,
            'pagination'          => $pagination,
            'currency_symbol'     => $currency_symbol,
            'donations'           => $donor_donations->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/donor-donations' );
    }

    public function maybe_connect_user_to_donor( $user_id ){
        $user_info = get_userdata( $user_id );
        $email_address = $user_info->user_email;

        $donor = $this->get_donor_by_email( $email_address );
        update_post_meta( $donor->ID, '_donor_user_account', $user_id );
    }

    private function get_donor_by_email( $email ) {
        $query_args = array(
            'post_type'  => 'pr_donor',
            'meta_query' => array(
                array(
                    'key' => '_donor_email',
                    'value' => $email,
                ),
            )
        );
        $donor_query = new \WP_Query( $query_args );

        if ( $donor_query->found_posts == 0 ) {
            return false;
        }

        $donors = $donor_query->get_posts();
        return $donors[0];

    }

    public static function get_lifetime_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'posts_per_page'  => -1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donations = get_posts( $args );

        if ( empty($donations) ) {
            return $currency_symbol. '0.00';
        }

        $donor_value = 0.00;

        foreach ($donations as $donation) {
            $donor_value += get_post_meta( $donation->ID, '_donation_amount', true );
        }

        return $currency_symbol . number_format_i18n( $donor_value, 2 );

    }

    public static function get_latest_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'order'           => 'DESC',
            'orderby'         => 'date',
            'posts_per_page'  => 1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donation = get_posts( $args );

        if ( !empty($donation) ){
            $amount = $currency_symbol . number_format_i18n( get_post_meta( $donation[0]->ID, '_donation_amount', true ), 2 );
        } else {
            $amount = $currency_symbol. '0.00';
        }

        return $amount;
    }

    public static function get_first_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'order'           => 'ASC',
            'orderby'         => 'date',
            'posts_per_page'  => 1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donation = get_posts( $args );

        if ( !empty($donation) ){
            $amount = $currency_symbol . number_format_i18n( get_post_meta( $donation[0]->ID, '_donation_amount', true ), 2 );
        } else {
            $amount = $currency_symbol. '0.00';
        }

        return $amount;
    }

    public static function get_largest_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'meta_key'        => '_donation_amount',
            'order'           => 'DESC',
            'orderby'         => 'meta_value_num',
            'posts_per_page'  => 1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donation = get_posts( $args );

        if ( !empty($donation) ){
            $amount = $currency_symbol . number_format_i18n( get_post_meta( $donation[0]->ID, '_donation_amount', true ), 2 );
        } else {
            $amount = $currency_symbol. '0.00';
        }

        return $amount;
    }

    public function manage_columns( $column_name, $post_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        switch ( $column_name ) {

            case 'id':
                echo $post_id;
                break;

            case 'link':
                echo '<a href="post.php?action=edit&post=' . $post_id . '">' . __( 'View Details', 'peerraiser' ) . '</a>';
                break;

            case 'first_name':
                echo get_post_meta( $post_id, '_donor_first_name', true );
                break;

            case 'last_name':
                echo get_post_meta( $post_id, '_donor_last_name', true );
                break;

            case 'email_address':
                echo get_post_meta( $post_id, '_donor_email', true );
                break;

            case 'username':
                $user_id = get_post_meta( $post_id, '_donor_user_account', true );
                $user_info = get_userdata( $user_id );
                echo ( $user_id ) ? '<a href="user-edit.php?user_id='.$user_id.'">' . $user_info->user_login  . '</a>' : '&mdash;';
                break;

            case 'total_donated':
                echo $this->get_lifetime_donation_amount( $post_id );
                break;

        }

    }

	public function handle_add_donor() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_donor_nonce' ) ) {
			die( __('Security check failed.', 'peerraiser' ) );
		}

		$validation = $this->is_valid_donor();
		if ( ! $validation['is_valid'] ) {
			return;
		}

		$donor = new Donor_Model();

		// Required Fields
		$donor->donor_name    = esc_attr( $this->generate_donor_name() );
		$donor->email_address = $_REQUEST['_peerraiser_donor_email'];

		// Optional Fields
		$donor->user_id          = isset( $_REQUEST['_peerraiser_donor_user_account'] ) ? absint( $_REQUEST['_peerraiser_donor_user_account'] ) : 0;
		$donor->first_name       = isset( $_REQUEST['_peerraiser_donor_first_name'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_first_name'] ) : '';
        $donor->last_name        = isset( $_REQUEST['_peerraiser_donor_last_name'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_last_name'] ) : '';
        $donor->street_address_1 = isset( $_REQUEST['_peerraiser_donor_street_1'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_street_1'] ) : '';
        $donor->street_address_2 = isset( $_REQUEST['_peerraiser_donor_street_2'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_street_2'] ) : '';
        $donor->city			 = isset( $_REQUEST['_peerraiser_donor_city'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_city'] ) : '';
        $donor->state_province   = isset( $_REQUEST['_peerraiser_donor_state'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_state'] ) : '';
        $donor->zip_postal 		 = isset( $_REQUEST['_peerraiser_donor_zip'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_zip'] ) : '';
        $donor->country 		 = isset( $_REQUEST['_peerraiser_donor_country'] ) ? esc_attr( $_REQUEST['_peerraiser_donor_country'] ) : '';

		// Save to the database
		$donor->save();

		// Create redirect URL
		$location = add_query_arg( array(
			'page' => 'peerraiser-donors',
			'view' => 'summary',
			'donor_id' => $donor->ID
		), admin_url( 'admin.php' ) );

		// Redirect to the edit screen for this new donor
		wp_safe_redirect( $location );
	}

	/**
	 * Checks if the fields are valid
	 *
	 * @since     1.0.0
	 * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
	 */
	private function is_valid_donor() {
		$required_fields = array( '_peerraiser_donor_first_name', '_peerraiser_donor_email' );

		$data = array(
			'is_valid'     => true,
			'field_errors' => array(),
		);

		foreach ( $required_fields as $field ) {
			if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
				$data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
			}
		}

		if ( isset( $_REQUEST['_peerraiser_donor_email'] ) && ! empty( $_REQUEST['_peerraiser_donor_email'] ) && ! is_email( $_REQUEST['_peerraiser_donor_email'] ) ) {
			$data['field_errors'][ '_peerraiser_donor_email' ] = __( 'Not a valid email address.', 'peerraiser' );
		}

		// TODO: Check if $_REQUEST['_peerraiser_donor_user_account'] is already tied to a donor account

		if ( ! empty( $data['field_errors'] ) ) {
			$message = __( 'One or more of the required fields was empty, please fix them and try again.', 'peerraiser' );
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

	private function generate_donor_name() {
		$first = trim( $_REQUEST['_peerraiser_donor_first_name'] );
		$last  = trim( $_REQUEST['_peerraiser_donor_last_name'] );

		if ( isset( $last ) && ! empty( $last ) ) {
			$first .= ' ' . $last;
		}

		return $first;
	}

}
