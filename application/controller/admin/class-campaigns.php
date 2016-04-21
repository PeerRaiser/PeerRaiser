<?php

namespace PeerRaiser\Controller\Admin;

class Campaigns extends \PeerRaiser\Controller\Base {

    private static $instance = null;

    /**
     * @see \PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_cmb2_admin_init' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'register_meta_boxes' )
            ),
            'peerraiser_admin_enqueue_styles_post_new' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'load_assets' )
            ),
            'peerraiser_admin_enqueue_styles_post_edit' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugin_is_active', 200 ),
                array( 'load_assets' )
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
        );
    }


    /**
     * Singleton to get only one Campaigns controller
     *
     * @return    \PeerRaiser\Admin\Campaigns
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function __construct(){}


    public function register_meta_boxes() {

        $cmb = new_cmb2_box( array(
            'id'           => 'peerraiser-campaign',
            'title'         => 'Campaign Options',
            'object_types'  => array( 'pr_campaign' ),
            'context'       => 'normal',
            'priority'      => 'default',
        ) );

        $campaigns_model = \PeerRaiser\Model\Admin\Campaigns::get_instance();
        $campaign_fields = $campaigns_model->get_fields();

        foreach ($campaign_fields as $key => $value) {
            $cmb->add_field($value);
        }

    }


    public function load_assets() {
        parent::load_assets();

        global $post_type;
        if ( 'pr_campaign' == $post_type ) {

            // Register and enqueue styles
            wp_register_style(
                'peerraiser-admin',
                \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
                array('peerraiser-font-awesome'),
                \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
            );
            wp_register_style(
                'peerraiser-admin-campaigns',
                \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-campaigns.css',
                array('peerraiser-font-awesome', 'peerraiser-admin'),
                \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
            );
            wp_enqueue_style( 'peerraiser-admin' );
            wp_enqueue_style( 'peerraiser-admin-campaigns' );
            wp_enqueue_style( 'peerraiser-font-awesome' );
            wp_enqueue_style( 'peerraiser-select2' );

            // Register and enqueue scripts
            wp_register_script(
                'peerraiser-admin-campaigns',
                \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-campaigns.js',
                array( 'jquery', 'peerraiser-admin' ),
                \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
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
                    'template_directory' => get_template_directory_uri()
                )
            );

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
        $fields = array( '_campaign_participants' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_campaign_participants':
                foreach ($_meta_value as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->connect( $object_id, $value, array(
                        'date' => current_time('mysql')
                    ) );
                }
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
        $fields = array( '_campaign_participants' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_campaign_participants':
                $removed = array_diff($old_value, $_meta_value);
                $added = array_diff($_meta_value, $old_value);
                // Remove the value from connection
                foreach ($removed as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->disconnect( $object_id, $value );
                }
                // Add the new connection
                foreach ($added as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->connect( $object_id, $value, array(
                        'date' => current_time('mysql')
                    ) );
                }
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
        $fields = array( '_campaign_participants' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_campaign_participants':
                // Remove the value from connection
                foreach ($old_value as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->disconnect( $object_id, $value );
                }
                break;

            default:
                break;
        }

    }

}