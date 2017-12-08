<?php

namespace PeerRaiser\Controller\Admin;

class Donations extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'peerraiser_page_peerraiser-donations', array( $this, 'load_assets' ) );
        add_action( 'admin_init',                           array( $this, 'on_donations_view' ) );
        add_action( 'cmb2_admin_init',                      array( $this, 'register_meta_boxes' ) );
        add_action( 'peerraiser_after_donation_metaboxes',  array( $this, 'donation_notes_metabox' ), 50, 1 );
        add_action( 'publish_pr_donation',                  array( $this, 'delete_transient' ) );
        add_action( 'peerraiser_add_donation',              array( $this, 'handle_add_donation' ) );
        add_action( 'peerraiser_update_donation',           array( $this, 'handle_update_donation' ) );
        add_action( 'peerraiser_delete_donation', 			array( $this, 'delete_donation' ) );
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
            'peerraiser-admin-donations',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-donations.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-donations' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-donations',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-donations.js',
            array( 'jquery', 'peerraiser-admin' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-donations' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-donations',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri()
            )
        );

    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $default_views = array( 'list', 'add', 'summary' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_donation_admin_view', 'list', $view );

        // Assign data to the view
        $view_args = array(
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'admin_url'            => get_admin_url(),
            'list_table'           => new \PeerRaiser\Model\Admin\Donation_List_Table(),
        );

        if ( $view === 'summary' ) {
            $view_args['donation'] = new \PeerRaiser\Model\Donation( $_REQUEST['donation'] );
            $view_args['donor']    = new \PeerRaiser\Model\Donor( $view_args['donation']->donor_id );
        }

        $this->assign( 'peerraiser', $view_args );

        // Render the view
        $this->render( 'backend/donation-' . $view );
    }

    public function register_meta_boxes() {
        $donations_model = new \PeerRaiser\Model\Admin\Donations_Admin();
        $donation_field_groups = $donations_model->get_fields();

        foreach ($donation_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'         => $field_group['title'],
                'object_types'  => array( 'pr_donation' ),
                'context'       => $field_group['context'],
                'priority'      => $field_group['priority'],
            ) );
            foreach ($field_group['fields'] as $key => $value) {
                $cmb->add_field($value);
            }
        }
    }

    public function on_donations_view() {
        if ( isset( $_REQUEST['page'], $_REQUEST['view'] ) && $_REQUEST['page'] === 'peerraiser-donations' && $_REQUEST['view'] === 'add' ) {
            $message = __( 'A donor record is required. <a href="admin.php?page=peerraiser-donors&view=add">Create one now</a> if it does not already exist', 'peerraiser' );
            \PeerRaiser\Model\Admin\Admin_Notices::add_notice( $message, 'notice-info', true );
        }
    }

    public function display_donor_box( $object ) {
        $donor_id = get_post_meta( $object->ID, '_donor', true );
        $donor_user_account = get_post_meta( $donor_id, '_donor_user_account', true);
        $donor_user_info = get_userdata($donor_user_account);

        $view_args = array(
            'profile_image_url' => ( !empty($donor_user_account) ) ? get_avatar_url( $donor_user_account ) : \PeerRaiser\Core\Setup::get_plugin_config()->get('images_url') . 'profile-mask.png',
            'first_name' => get_post_meta( $donor_id, '_donor_first_name', true ),
            'last_name' => get_post_meta( $donor_id, '_donor_last_name', true ),
            'donor_email' => get_post_meta( $donor_id, '_donor_email', true ),
            'donor_id' => $donor_id,
            'donor_user_account' => ( !empty($donor_user_account) ) ? '<a href="user-edit.php?user_id='.$donor_user_account.'">'.$donor_user_info->user_login.'</a>' : __('None', 'peerraiser'),
            'donor_since' => get_the_date( get_option( 'date_format' ), $donor_id),
            'donor_class' => ( !empty($donor_user_account) ) ? 'user' : 'guest',
        );
        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/donation-card' );
    }

    public function display_transaction_summary( $object ) {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $donor_id = get_post_meta( $object->ID, '_donor', true );
        $campaign_id = get_post_meta( $object->ID, '_campaign', true);
        $fundraiser_id = get_post_meta( $object->ID, '_fundraiser', true);
        $team_id = get_post_meta( $fundraiser_id, '_fundraiser_team', true);

        $view_args = array(
            'currency_symbol' => $currency_symbol,
            'first_name' => get_post_meta( $donor_id, '_donor_first_name', true ),
            'last_name' => get_post_meta( $donor_id, '_donor_last_name', true ),
            'donation_amount' => number_format_i18n( get_post_meta( $object->ID, '_donation_amount', true ), 2),
            'donation_date' => date_i18n( get_option( 'date_format' ), get_the_date( $object->ID) ),
            'donation_date' => get_the_date( get_option( 'date_format' ), $object->ID),
            'campaign_id' => $campaign_id,
            'campaign_title' => get_the_title( $campaign_id ),
            'fundraiser_id' => ( $fundraiser_id ) ? $fundraiser_id : false,
            'fundraiser_title' => ( $fundraiser_id ) ? get_the_title( $fundraiser_id ) : __( 'N/A', 'peerraiser' ),
            'team_id' => ( $team_id ) ? $team_id : false,
            'team_title' => ( $team_id ) ? get_the_title( $team_id ) : __( 'N/A', 'peerraiser' )
        );
        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/donation-summary' );
    }

    public function donation_notes_metabox( $peerraiser ) {
        if ( ! apply_filters( 'peerraiser_show_donation_notes_metabox', true ) )
            return;

        $this->assign( 'peerraiser', $peerraiser );

        $this->render( 'backend/partials/donation-box-notes' );
    }

    /**
     * Handle "Add Offline Donation" submission
     *
     * @since     1.0.0
     */
    public function handle_add_donation() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_donation_nonce' ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_donation();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $donation = new \PeerRaiser\Model\Donation();

        // Required Fields
        $donation->donor_id      = absint( $_REQUEST['donor'] );
        $donation->total         = $_REQUEST['donation_amount'];
        $donation->subtotal      = $_REQUEST['donation_amount'];
        $donation->campaign_id   = absint( $_REQUEST['campaign'] );
        $donation->donation_type = $_REQUEST['donation_type'];
        $donation->gateway       = 'offline';
        $donation->status        = $_REQUEST['donation_status'];

        // Optional Fields
        $donation->fundraiser_id = isset( $_REQUEST['fundraiser'] ) ? absint( $_REQUEST['fundraiser'] ) : 0;

        $donation_note = trim( $_REQUEST['donation_note'] );
        if ( isset( $_REQUEST['donation_note'] ) && ! empty( $donation_note ) ) {
            $user = wp_get_current_user();
            $donation->add_note( $_REQUEST['donation_note'], $user->user_login );
        }

        // Save to the database
        $donation->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-donations',
            'view'               => 'summary',
            'donation'           => $donation->ID,
            'peerraiser_notice' => 'donation_added',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    public function handle_update_donation() {
        $donation_id = intval( $_REQUEST['donation_id'] );

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_update_donation_' . $donation_id ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $donation = new \PeerRaiser\Model\Donation( (int) $_REQUEST['donation_id'] );

        $donation->status = $_REQUEST['donation_status'];

        $donation_note = trim( $_REQUEST['donation_note'] );
        if ( isset( $_REQUEST['donation_note'] ) && ! empty( $donation_note ) ) {
            $user = wp_get_current_user();
            $donation->add_note( $_REQUEST['donation_note'], $user->user_login );
        }

        $donation->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-donations',
            'view'               => 'summary',
            'donation'           => $donation->ID,
            'peerraiser_notice' => 'donation_updated',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    /**
     * Handle "delete donation" action
     *
     * @since     1.0.0
     */
    public function delete_donation() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_delete_donation_' . $_REQUEST['donation_id'] ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        // Models
        $donation = new \PeerRaiser\Model\Donation( (int) $_REQUEST['donation_id'] );

        // Delete the donation
        $donation->delete();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-donations',
            'peerraiser_notice' => 'donation_deleted'
        ), admin_url( 'admin.php' ) );

        wp_safe_redirect( $location );
    }

    /**
     * Checks if the fields are valid
     *
     * @since     1.0.0
     * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
     */
    private function is_valid_donation() {
        $donations_model = new \PeerRaiser\Model\Admin\Donations_Admin();
        $required_fields = $donations_model->get_required_field_ids();

        $required_fields['donation_status'] = 'donation_status';

        $data = array(
            'is_valid'     => true,
            'field_errors' => array(),
        );

        foreach ( $required_fields as $field ) {
            if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
                $data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
            }
        }

        if ( ! empty( $data['field_errors'] ) ) {
            $message = __( 'One or more of the required fields was empty, please fix them and try again.', 'peerraiser' );
            \PeerRaiser\Model\Admin\Admin_Notices::add_notice( $message, 'notice-error', true );

            wp_localize_script(
                'jquery',
                'peerraiser_field_errors',
                $data['field_errors']
            );

            $data['is_valid'] = false;
        }

        return $data;
    }
}
