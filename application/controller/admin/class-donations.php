<?php

namespace PeerRaiser\Controller\Admin;

class Donations extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                      array( $this, 'register_meta_boxes' ) );
        add_action( 'do_meta_boxes',                        array( $this, 'maybe_remove_metabox' ) );
        add_action( 'peerraiser_page_peerraiser-donations', array( $this, 'load_assets' ) );
        add_action( 'peerraiser_page_peerraiser-donations', array( $this, 'on_donations_view' ) );
        // add_action( 'admin_head',                             array( $this, 'on_donations_view' ) );
        // add_action( 'admin_menu',                             array( $this, 'replace_submit_box' ) );
        // add_action( 'added_post_meta',                        array( $this, 'add_connections' ) );
        // add_action( 'update_post_meta',                       array( $this, 'update_connections' ) );
        // add_action( 'delete_post_meta',                       array( $this, 'delete_connections' ) );
        // add_action( 'before_delete_post',                     array( $this, 'handle_post_deleted' ) );
        add_action( 'peerraiser_new_donation',                array( $this, 'add_donation' ) );
        add_action( 'manage_pr_donation_posts_custom_column', array( $this, 'manage_columns' ) );
        add_action( 'add_meta_boxes',                         array( $this, 'add_meta_boxes' ) );
        add_action( 'publish_pr_donation',                    array( $this, 'delete_transient' ) );
        add_action( 'admin_post_peerraiser_add_donation',     array( $this, 'handle_add_donation' ) );
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

        $default_views = array( 'list', 'add', 'edit' );

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

        $this->assign( 'peerraiser', $view_args );

        // Render the view
        $this->render( 'backend/donation-' . $view );
    }

    public function register_meta_boxes() {

        $donations_model = new \PeerRaiser\Model\Admin\Donations();
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

    public function maybe_remove_metabox() {
        // Only display fields on a "new" donations, not existing ones
        if ( $this->is_edit_page( 'edit' ) )
            remove_meta_box( 'offline-donation', 'pr_donation', 'normal' );
    }

    public function replace_submit_box() {
        remove_meta_box('submitdiv', 'pr_donation', 'core');
        add_meta_box('submitdiv', __('Donation'), array( $this, 'get_submit_box'), 'pr_donation', 'side', 'low');
    }

    public function get_submit_box( $object ) {
        $post_type_object = get_post_type_object($object->post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        $is_published = ( in_array( $object->post_status, array('publish', 'future', 'private') ) );

        $view_args = array(
            'object' => $object,
            'can_publish' => $can_publish,
            'is_published' => $is_published,
        );

        $this->assign( 'peerraiser', $view_args );

        $view_file = ( $is_published ) ? 'backend/partials/donation-box-edit' : 'backend/partials/donation-box-add';

        $this->render( $view_file );

    }

    public function on_donations_view() {
        if ( isset( $_REQUEST['view'] ) && $_REQUEST['view'] === 'add' ) {
            $message = __("A donor record is required. <a href=\"admin.php?page=peerraiser-donors&view=add\">Create one now</a> if it doesn't already exist");
            \PeerRaiser\Model\Admin\Admin_Notices::add_notice( $message, 'notice-info', true );
        }
    }

    /**
     * After post meta is added, add the connections
     *
     * @since    1.0.0
     * @return   null
     */
    public function add_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_donor', '_campaign', '_fundraiser' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_donor':
                p2p_type( 'donation_to_donor' )->connect( $_meta_value, $object_id, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_campaign':
                p2p_type( 'donation_to_campaign' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_fundraiser':
                p2p_type( 'donation_to_fundraiser' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                $participant = get_post_meta( $_meta_value, '_fundraiser_participant', true);
                p2p_type( 'donation_to_participant' )->connect( $object_id, $participant, array(
                    'date' => current_time('mysql')
                ) );
                break;

            default:
                break;
        }

    }

    /**
     * Before the post meta is updated, update the connections
     *
     * @since     1.0.0
     * @return    null
     */
    public function update_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_donor', '_campaign', '_fundraiser' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_donor':
                // Remove the value from connection
                p2p_type( 'donation_to_donor' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'donation_to_donor' )->connect( $_meta_value, $object_id, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_campaign':
                // Remove the value from connection
                p2p_type( 'donation_to_campaign' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'donation_to_campaign' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_fundraiser':
                // Remove the value from connection
                p2p_type( 'donation_to_fundraiser' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'donation_to_fundraiser' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            default:
                break;
        }
    }

    /**
     * Before post meta is deleted, delete the connections
     *
     * @since     1.0.0
     * @return    null
     */
    public function delete_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_donor', '_campaign', '_fundraiser' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_donor':
                // Remove the value from connection
                p2p_type( 'donation_to_donor' )->disconnect( $old_value, $object_id );
                break;

            case '_campaign':
                // Remove the value from connection
                p2p_type( 'donation_to_campaign' )->disconnect( $old_value, $object_id );
                break;

            case '_fundraiser':
                // Remove the value from connection
                p2p_type( 'donation_to_fundraiser' )->disconnect( $old_value, $object_id );
                break;

            default:
                break;
        }
    }

    public function handle_post_deleted( $post_id ) {
        global $post_type;

        if ( 'pr_donation' != $post_type )
            return;

        $donor = get_post_meta( $post_id, '_donor', true );
        $campaign = get_post_meta( $post_id, '_campaign', true);
        $fundraiser = get_post_meta( $post_id, '_fundraiser', true);

        p2p_type( 'donation_to_donor' )->disconnect( $donor, $post_id );
        p2p_type( 'donation_to_campaign' )->disconnect( $campaign, $post_id );
        p2p_type( 'donation_to_fundraiser' )->disconnect( $fundraiser, $post_id );
    }

    public function add_donation( $data ){
        if ( $this->is_existing_donation( $data['transaction_key'] ) ){
            exit;
        }

        $donor = $this->get_donor_by_email( $data['email'] );

        if ( !$donor ) {
            $donor_id = $this->add_donor( $data );
            update_post_meta( $donor_id, '_donor_first_name', $data['first_name'] );
            update_post_meta( $donor_id, '_donor_last_name', $data['last_name'] );
            update_post_meta( $donor_id, '_donor_email', $data['email'] );
        } else {
            $donor_id = $donor->ID;
        }

        $donation_args = array(
            'post_type'    => 'pr_donation',
            'post_title'   => $this->make_donation_title(),
            'post_content' => '',
            'post_status'  => 'publish',
            'post_author'  => 1,
            'post_date'    => date('Y-m-d H:i:s', $data['date'] )
        );

        $donation_id = wp_insert_post( $donation_args );

        update_post_meta( $donation_id, '_payment_method', $data['payment_method'] );
        update_post_meta( $donation_id, '_transaction_key', $data['transaction_key'] );
        update_post_meta( $donation_id, '_ip_address', $data['ip_address'] );
        update_post_meta( $donation_id, '_test_mode', $data['test_mode'] );
        update_post_meta( $donation_id, '_donation_amount', $data['amount'] );

        // Setup connections
        p2p_type( 'donation_to_donor' )->connect( $donor_id, $donation_id, array(
            'date' => current_time('mysql')
        ) );
        p2p_type( 'donation_to_campaign' )->connect( $donation_id, $data['campaign_id'], array(
            'date' => current_time('mysql')
        ) );
        if ( isset($data['fundraiser_id']) ) {
            p2p_type( 'donation_to_fundraiser' )->connect( $donation_id, $data['fundraiser_id'], array(
                'date' => current_time('mysql')
            ) );
        }

        // Clear transient
        delete_transient( 'peerraiser_donations_total' );
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

            case 'donor':
                $donor_id = get_post_meta( $post_id, '_donor', true );
                echo get_post_meta( $donor_id, '_donor_first_name', true) . ' ' . get_post_meta( $donor_id, '_donor_last_name', true);
                break;

            case 'donation_amount':
                echo $currency_symbol . get_post_meta( $post_id, '_donation_amount', true );
                break;

            case 'method':
                echo get_post_meta( $post_id, '_payment_method', true );
                break;

            case 'campaign':
                $campaign_id = get_post_meta( $post_id, '_campaign', true);
                echo '<a href="post.php?action=edit&post='.$campaign_id.'">' . get_the_title( $campaign_id ) . '</a>';
                break;

            case 'fundraiser':
                $fundraiser_id = get_post_meta( $post_id, '_fundraiser', true);
                echo ( $fundraiser_id ) ? '<a href="post.php?action=edit&post='.$fundraiser_id.'">' . get_the_title( $fundraiser_id ) . '</a>' : '&mdash;';
                break;

            case 'test_mode':
                $test_mode = get_post_meta( $post_id, '_test_mode', true );
                echo ( $test_mode ) ? __( 'No', 'peerraiser' ) : __( 'Yes', 'peerraiser');
                break;
        }
    }

    public function add_meta_boxes() {
        if ( !$this->is_edit_page( 'edit' ) )
            return;

        add_meta_box(
            'donor_info',
            __('Donor Info', 'peerraiser'),
            array( $this, 'display_donor_box' ),
            'pr_donation',
            'normal',
            'high'
        );

        add_meta_box(
            'transaction_summary',
            __('Transaction Summary', 'peerraiser'),
            array( $this, 'display_transaction_summary' ),
            'pr_donation'
        );

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

    public function delete_transient() {
        delete_transient( 'peerraiser_donations_total' );
    }

    public function handle_add_donation() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_donation_nonce' ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        if ( ! $this->is_valid_donation() ) {
            die( __('Form is invalid.', 'peerraiser' ) );
        }

        $donation = new \PeerRaiser\Model\Database\Donation();
        //$donation_id = $donation->add_donation( $_REQUEST );

        error_log( $donation_id );
    }

    private function is_valid_donation() {
        $required_fields = array( '_donor', '_donation_amount', '_campaign', '_donation_status', '_donation_type' );

        foreach ($_REQUEST as $key => $value) {
            if ( ! in_array( $key, $required_fields ) ) {
                return false;
            }
        }

        return true;
    }
}