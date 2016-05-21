<?php

namespace PeerRaiser\Core;

class Bootstrap {

    /**
     * Contains all controller instances.
     * @var    array
     */
    private static $controllers = array();

    /**
     * Contains all settings for the plugin.
     *
     * @var    \PeerRaiser\Model\Config
     */
    private $config;


    /**
     * @param    \PeerRaiser\Model\Config    $config
     *
     * @return    \PeerRaiser\Core\Bootstrap
     */
    public function __construct( \PeerRaiser\Model\Config $config ) {
        $this->config = $config;

        // Load the textdomain for 'plugins_loaded', 'register_activation_hook', and 'register_deactivation_hook'
        $textdomain_dir  = dirname( $this->config->get( 'plugin_base_name' ) );
        $textdomain_path = $textdomain_dir . $this->config->get( 'text_domain_path' );
        load_plugin_textdomain(
            'peerraiser',
            false,
            $textdomain_path
        );
    }


    /**
     * Internal function to create and get controllers.
     *
     * @param     string                         $name    name of the controller without prefix.
     * @throws    \PeerRaiser\Core\Exception
     *
     * @return    bool|\PeerRaiser\Controller\Base    $controller    instance of the given controller name
     */
    public static function get_controller( $name ) {
        $class = "\PeerRaiser\\Controller\\" . (string) $name;

        if ( ! class_exists( $class ) ) {
            $msg = __( '%s: <code>%s</code> not found', 'peerraiser' );
            $msg = sprintf( $msg, __METHOD__, $class );
            throw new \Exception( $msg );
        }

        if ( ! array_key_exists( $class, self::$controllers ) ) {
            self::$controllers[ $class ] = new $class( \PeerRaiser\Core\Setup::get_plugin_config() );
        }

        return self::$controllers[ $class ];
    }


    /**
     * Start the plugin on plugins_loaded hook.
     *
     * @wp-hook    plugins_loaded
     *
     * @return    void
     */
    public function run() {
        $this->register_wordpress_hooks();
        $this->register_modules();

        $this->register_cache_helper();
        $this->register_upgrade_checks();

        $this->register_custom_post_types();
        $this->register_admin_actions();
        $this->register_frontend_actions();
        $this->register_shortcodes();
        $this->register_connections();

        // PeerRaiser loaded finished. Triggering event for other plugins
        \PeerRaiser\Hooks::get_instance()->peerraiser_ready();
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_init_finished' );
    }


    /**
     * Internal function to register global actions for frontend.
     *
     * @return    void
     */
    private function register_frontend_actions() {
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();

        $listener_controller = self::get_controller( 'Frontend\Listener' );
        $dispatcher->add_subscriber( $listener_controller );
    }


    /**
     * Internal function to register global actions for backend.
     *
     * @return    void
     */
    private function register_custom_post_types() {
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();

        $cpt_controller = self::get_controller( 'Custom_Post_Type' );
        $dispatcher->add_subscriber( $cpt_controller );

        // $post_controller = self::get_controller( 'Frontend_Post' );
        // $dispatcher->add_subscriber( $post_controller );

        // set up unique visitors tracking
        // $statistics_controller = self::get_controller( 'Frontend_Statistic' );
        // $dispatcher->add_subscriber( $statistics_controller );
    }


    /**
     * Internal function to register all shortcodes.
     *
     * @return    void
     */
    private function register_shortcodes() {
        // $shortcode_controller = self::get_controller( 'Frontend_Shortcode' );

        // \PeerRaiser\Hooks::add_wp_shortcode( 'peerraiser_premium_download', 'peerraiser_shortcode_premium_download' );
        // \PeerRaiser\Hooks::add_wp_shortcode( 'peerraiser_box_wrapper', 'peerraiser_shortcode_box_wrapper' );

        // $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        // $dispatcher->add_subscriber( $shortcode_controller );
    }


    /**
     * Internal function to register P2P connections
     *
     * @since     1.0.0
     * @return    void
     */
    private function register_connections() {
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();

        $connections_controller = self::get_controller( 'Connections' );
        $dispatcher->add_subscriber( $connections_controller );
    }


    /**
     * Internal function to register the admin actions after the 'plugin_is_working' check.
     *
     * @return    void
     */
    private function register_admin_actions() {
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();

        // add the admin panel
        $admin_controller = self::get_controller( 'Admin' );
        $dispatcher->add_subscriber( $admin_controller );

        // admin notices
        $admin_notices_controller = self::get_controller( 'Admin\Admin_Notices' );
        $dispatcher->add_subscriber( $admin_notices_controller );

        // dashboard controller
        $dashboard_controller = self::get_controller( 'Admin\Dashboard' );
        $dispatcher->add_subscriber( $dashboard_controller );

        // campaigns controller
        $campaigns_controller = self::get_controller( 'Admin\Campaigns' );
        $dispatcher->add_subscriber( $campaigns_controller );

        // fundraiser controller
        $fundraisers_controller = self::get_controller( 'Admin\Fundraisers' );
        $dispatcher->add_subscriber( $fundraisers_controller );

        // teams controller
        $teams_controller = self::get_controller( 'Admin\Teams' );
        $dispatcher->add_subscriber( $teams_controller );

        // settings controller
        $settings_controller = self::get_controller( 'Admin\Settings' );
        $dispatcher->add_subscriber( $settings_controller );

        // donations controller
        $donations_controller = self::get_controller( 'Admin\Donations' );
        $dispatcher->add_subscriber( $donations_controller );

        // donors controller
        $donors_controller = self::get_controller( 'Admin\Donors' );
        $dispatcher->add_subscriber( $donors_controller );

    }


    /**
     * Internal function to register the cache helper for {update_option_} hooks.
     *
     * @return    void
     */
    private function register_cache_helper() {
        // cache helper to purge the cache on update_option()
        $cache_helper = new \PeerRaiser\Helper\Cache();

        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->add_listener( 'peerraiser_option_update', array( $cache_helper, 'purge_cache' ) );
    }


    /**
     * Internal function to register all upgrade checks.
     *
     * @return    void
     */
    private function register_upgrade_checks() {
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->add_subscriber( self::get_controller( 'Install' ) );
    }


    /**
     * Late load event for other plugins to remove / add own actions to the plugin.
     *
     * @return    void
     */
    public function late_load() {
        /**
         * Late loading event.
         *
         * @param     PeerRaiser\Core\Bootstrap    $this
         */
        do_action( 'peerraiser_and_wp_loaded', $this );
    }


    /**
     * Install callback to create custom database tables.
     *
     * @wp-hook register_activation_hook
     *
     * @return     void
     */
    public function activate() {
        $install_controller = self::get_controller( 'Install' );
        $install_controller->install();

        // register the 'refresh dashboard' cron job
        // wp_schedule_event( time(), 'hourly', 'peerraiser_refresh_dashboard_data' );
        // register the 'delete old post views' cron job
        // wp_schedule_event( time(), 'daily', 'peerraiser_delete_old_post_views', array( '3 month' ) );
    }


    /**
     * Callback to deactivate the plugin.
     *
     * @wp-hook register_deactivation_hook
     *
     * @return    void
     */
    public function deactivate() {
        // de-register the 'refresh dashboard' cron job
        // wp_clear_scheduled_hook( 'peerraiser_refresh_dashboard_data' );
        // de-register the 'delete old post views' cron job
        // wp_clear_scheduled_hook( 'peerraiser_delete_old_post_views', array( '3 month' ) );
    }


    /**
     * Internal function to register event subscribers.
     *
     * @return    void
     */
    private function register_modules() {
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->add_subscriber( new \PeerRaiser\Module\Appearance() );
    }


    /**
     * Internal function to register event subscribers.
     *
     * @return    void
     */
    private function register_wordpress_hooks() {
        \PeerRaiser\Hooks::get_instance()->init();
    }

}