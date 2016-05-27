<?php

namespace PeerRaiser\Controller\Admin;

class Donations extends \PeerRaiser\Controller\Base {

    private static $instance = null;

    /**
     * @see \PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_cmb2_admin_init' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'register_meta_boxes' ),
            ),
            'peerraiser_do_meta_boxes' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'maybe_remove_metabox' ),
            ),
            'peerraiser_admin_enqueue_styles_post_new' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'load_assets' ),
            ),
            'peerraiser_admin_enqueue_styles_post_edit' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'load_assets' ),
            ),
            'peerraiser_admin_head' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'on_donations_view' ),
            ),
            'peerraiser_admin_menu' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'replace_submit_box' ),
            ),
            'peerraiser_after_post_meta_added' => array(
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'add_connections' ),
            ),
            'peerraiser_before_post_meta_updated' => array(
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'update_connections' ),
            ),
            'peerraiser_before_post_meta_deleted' => array(
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'delete_connections' ),
            ),
            'peerraiser_before_delete_post' => array(
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'handle_post_deleted' ),
            ),
            'peerraiser_new_donation' => array(
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'add_donation' ),
            ),
            'peerraiser_manage_donation_columns' => array(
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'manage_columns' ),
            )
        );
    }


    public function load_assets() {
        parent::load_assets();

        // If this isn't the Donation post type, exit early
        global $post_type;
        if ( 'pr_donation' != $post_type )
            return;

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


    public function register_meta_boxes( \PeerRaiser\Core\Event $event ) {

        $donations_model = \PeerRaiser\Model\Admin\Donations::get_instance();
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


    public function maybe_remove_metabox( \PeerRaiser\Core\Event $event ) {
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

        $event = new \PeerRaiser\Core\Event();
        $event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_admin_menu_data', $event );
        $results = (array) $event->get_result();

        // $donation_model = new \PeerRaiser\Model\Admin\Donations();
        // $default_menu = $model->get_menu_items();

        // $menu = array_merge( $default_menu, $results );

        $view_args = array(
            'object' => $object,
            'can_publish' => $can_publish,
            'is_published' => $is_published,
        );

        $this->assign( 'peerraiser', $view_args );

        $view_file = ( $is_published ) ? 'backend/partials/donation-box-edit' : 'backend/partials/donation-box-add';

        $this->render( $view_file );

    }


    public function on_donations_view( \PeerRaiser\Core\Event $event ) {
        global $typenow;

        if ( $this->is_edit_page( 'new' ) && "pr_donation" == $typenow ) {
            $message = __("A donor record is required. <a href=\"post-new.php?post_type=pr_donor\">Create one now</a> if it doesn't already exist");
            \PeerRaiser\Controller\Admin\Admin_Notices::add_notice( $message, 'notice-info', true );
        }

    }


    /**
     * After post meta is added, add the connections
     *
     * @since    1.0.0
     * @param    \PeerRaiser\Core\Event    $event
     * @return   null
     */
    public function add_connections( \PeerRaiser\Core\Event $event ) {
        list( $meta_id, $object_id, $meta_key, $_meta_value ) = $event->get_arguments();
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
                break;

            default:
                break;
        }

    }


    /**
     * Before the post meta is updated, update the connections
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     * @return    null
     */
    public function update_connections(  \PeerRaiser\Core\Event $event  ) {
        list( $meta_id, $object_id, $meta_key, $_meta_value ) = $event->get_arguments();
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
     * @param     \PeerRaiser\Core\Event    $event
     * @return    null
     */
    public function delete_connections(  \PeerRaiser\Core\Event $event  ) {
        list( $meta_id, $object_id, $meta_key, $_meta_value ) = $event->get_arguments();
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


    public function handle_post_deleted( \PeerRaiser\Core\Event $event ) {
        list( $post_id ) = $event->get_arguments();
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


    public function add_donation( \PeerRaiser\Core\Event $event ){
        $data = $event->get_arguments();

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

    }


    private function is_edit_page( $new_edit = null ){
        global $pagenow;
        if (!is_admin()) return false;

        if ($new_edit == "edit") {
            return in_array( $pagenow, array( 'post.php',  ) );
        } elseif ($new_edit == "new") {
            return in_array( $pagenow, array( 'post-new.php' ) );
        } else {
            return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
        }
    }


    private function make_donation_title( $data ) {
        return ( isset( $data['donor_name'] ) ) ? $data['donor_name'] : 'Donation';
    }


    public function manage_columns( \PeerRaiser\Core\Event $event ) {
        list( $column_name, $post_id ) = $event->get_arguments();

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


    private function is_existing_donation( $key ) {
        $query_args = array(
            'post_type'  => 'pr_donation',
            'meta_query' => array(
                array(
                    'key' => '_transaction_key',
                    'value' => $key,
                ),
            )
        );
        $donation_query = new \WP_Query( $query_args );
        return ( $donation_query->found_posts > 0 );
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


    private function add_donor( $data ) {
        $name = ( isset($data['first_name']) && isset($data['last_name']) ) ? $data['first_name'] . ' ' . $data['last_name'] : 'Anonymous';
        $donor_args = array(
            'post_type'    => 'pr_donor',
            'post_title'   => $name,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_author'  => 1,
        );

        return wp_insert_post( $donor_args );
    }


    private function is_valid_donation( $fields ) {
        $required_fields = array( 'date', 'payment_method', 'transaction_key', 'ip_address', 'test_mode', 'amount', 'campaign_id', 'email' );

        foreach ($fields as $key => $value) {
            if ( !in_array($key, $required_fields) )
                return false;
        }

        return true;
    }

}