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

        // Only display fields on a "new" donations, not existing ones
        if ( $this->is_edit_page( 'edit' ) )
            return;

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

}