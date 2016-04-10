<?php

namespace PeerRaiser\Module;

class Appearance extends \PeerRaiser\Core\View implements \PeerRaiser\Core\Event\Subscriber_Interface {

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_shared_events()
     */
    public static function get_shared_events() {
        return array(
            'peerraiser_on_admin_view' => array(
                array( 'on_admin_view' ),
            ),
            'peerraiser_on_plugin_is_active' => array(
                array( 'on_plugin_is_active' ),
            ),
            'peerraiser_on_plugins_page_view' => array(
                array( 'on_plugins_page_view' ),
            ),
            'peerraiser_on_plugin_is_working' => array(
                array( 'on_plugin_is_working' ),
            ),
            'peerraiser_on_preview_post_as_admin' => array(
                array( 'on_preview_post_as_admin' ),
            ),
            'peerraiser_on_visible_test_mode' => array(
                array( 'on_visible_test_mode' ),
            ),
            'peerraiser_on_ajax_send_json' => array(
                array( 'on_ajax_send_json' ),
            ),
            'peerraiser_on_ajax_user_can_activate_plugins' => array(
                array( 'on_ajax_user_can_activate_plugins' ),
            ),
        );
    }

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_post_content' => array(
                array( 'modify_post_content', 0 ),
                array( 'on_preview_post_as_admin', 100 ),
                array( 'on_enabled_post_type', 100 ),
            ),
            'peerraiser_check_url_encrypt' => array(
                array( 'on_check_url_encrypt' ),
            ),
        );
    }

    /**
     * Stops event bubbling for admin with preview_post_as_visitor option disabled
     *
     * @param PeerRaiser\Core\Event $event
     */
    public function on_preview_post_as_admin( \PeerRaiser\Core\Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        $preview_post_as_visitor   = \PeerRaiser\Helper\User::preview_post_as_visitor( $post );
        $user_has_unlimited_access = \PeerRaiser\Helper\User::can( 'peerraiser_has_full_access_to_content', $post );
        if ( $user_has_unlimited_access && ! $preview_post_as_visitor ) {
            $event->stop_propagation();
        }
        $event->add_argument( 'attributes', array( 'data-preview-post-as-visitor' => $preview_post_as_visitor ) );
        $event->set_argument( 'preview_post_as_visitor', $preview_post_as_visitor );
    }

    /**
     * Checks, if the current post is rendered in visible test mode
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_visible_test_mode( \PeerRaiser\Core\Event $event ) {
        $is_in_visible_test_mode = get_option( 'peerraiser_is_in_visible_test_mode' )
                                   && ! $this->config->get( 'is_in_live_mode' );

        $event->set_argument( 'is_in_visible_test_mode', $is_in_visible_test_mode );
    }

    /**
     * Checks, if the current area is admin
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_admin_view( \PeerRaiser\Core\Event $event ) {
        if ( ! is_admin() ) {
            $event->stop_propagation();
        }
    }

    /**
     * Checks, if the current area is plugins manage page.
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_plugins_page_view( \PeerRaiser\Core\Event $event ) {
        if ( empty( $GLOBALS['pagenow'] ) || $GLOBALS['pagenow'] !== 'plugins.php' ) {
            $event->stop_propagation();
        }
    }

    /**
     * Checks, if the plugin is active.
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_plugin_is_active( \PeerRaiser\Core\Event $event ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $config = \PeerRaiser\Core\Setup::get_plugin_config();

        // continue, if plugin is active
        if ( ! is_plugin_active( $config->get( 'plugin_base_name' ) ) ) {
            $event->stop_propagation();
        }
    }

    /**
     * Checks, if the plugin is working.
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_plugin_is_working( \PeerRaiser\Core\Event $event ) {
        // check, if the plugin is correctly configured and working
        // if ( ! \PeerRaiser\Helper\View::plugin_is_working() ) {
        //     $event->stop_propagation();
        // }
    }

    /**
     * Modify the post content of paid posts.
     *
     * @wp-hook the_content
     *
     * @param PeerRaiser_Core_Event $event
     *
     * @return string $content
     */
    public function modify_post_content( \PeerRaiser\Core\Event $event ) {
        $content            = $event->get_result();
        $caching_is_active  = (bool) $this->config->get( 'caching.compatible_mode' );
        if ( $caching_is_active ) {
            // if caching is enabled, wrap the teaser in a div, so it can be replaced with the full content,
            // if the post is / has already been purchased
            $content = '<div id="pr_js_postContentPlaceholder">' . $content . '</div>';
        }

        $event->set_result( $content );
    }

    /**
     * Set type as JSON
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_ajax_send_json( \PeerRaiser\Core\Event $event ) {
        $event->set_type( \PeerRaiser\Core\Event::TYPE_JSON );
    }

    /**
     * Stops event if user can't activate plugins
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_ajax_user_can_activate_plugins( \PeerRaiser\Core\Event $event ) {
        // check for required capabilities to perform action
        if ( ! current_user_can( 'activate_plugins' ) ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'You don\'t have sufficient user capabilities to do this.', 'peerraiser' )
                )
            );
            $event->stop_propagation();
        }
    }

    /**
     * @param  PeerRaiser_Core_Event $event
     * @return void
     */
    public function on_check_url_encrypt( \PeerRaiser\Core\Event $event ) {
        $event->set_echo( false );
        $event->set_result( true );
    }

}