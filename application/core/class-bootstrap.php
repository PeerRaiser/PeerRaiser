<?php

namespace PeerRaiser\Core;

use PeerRaiser\Model\Config;

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
    public function __construct( Config $config ) {
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
     * @throws    \Exception
     *
     * @return    bool|\PeerRaiser\Controller\Base    $controller    instance of the given controller name
     */
    public static function get_controller( $name ) {
        $class = "\\PeerRaiser\\Controller\\" . (string) $name;

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

        $this->register_upgrade_checks();
        $this->register_custom_post_types();
        $this->register_taxonomies();
        $this->register_admin_actions();
        $this->register_frontend_actions();
        $this->register_shortcodes();
        $this->register_activity_log();
        $this->register_tables();

        add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        include_once( PEERRAISER_PATH . 'application/helper/functions.php' );

        do_action( 'peerraiser_ready', $this );
    }

    /**
     * Internal function to register global actions for frontend.
     *
     * @return    void
     */
    private function register_frontend_actions() {
        $frontend_controller = self::get_controller( 'Frontend\Frontend' );
        $frontend_controller->register_actions();

        $post_controller = self::get_controller( 'Frontend\Post' );
        $post_controller->register_actions();

        $shortcode_controller = self::get_controller( 'Frontend\Shortcode' );
        $shortcode_controller->register_actions();

        $registration_controller = self::get_controller( 'Frontend\Registration' );
        $registration_controller->register_actions();

        $account_controller = self::get_controller( 'Frontend\Account' );
        $account_controller->register_actions();

        $dashboard_controller = self::get_controller( 'Frontend\Participant_Dashboard' );
        $dashboard_controller->register_actions();

        $template_controller = self::get_controller( 'Frontend\Template' );
        $template_controller->register_actions();

        $donation_controller = self::get_controller( 'Frontend\Donation' );
        $donation_controller->register_actions();

        $widget_controller = self::get_controller( 'Frontend\Widget' );
        $widget_controller->register_actions();
    }

    /**
     * Internal function to register custom post types.
     *
     * @return    void
     */
    private function register_custom_post_types() {
        $custom_post_type_controller = self::get_controller( 'Custom_Post_Type' );
        $custom_post_type_controller->register_actions();
    }

    /**
     * Internal function to register taxonomies.
     *
     * @return    void
     */
    private function register_taxonomies() {
        $taxonomy_controller = self::get_controller( 'Taxonomy' );
        $taxonomy_controller->register_actions();
    }

    /**
     * Internal function to register all shortcodes.
     *
     * @return    void
     */
    private function register_shortcodes() {
        $shortcode_controller = self::get_controller( 'Frontend\Shortcode' );
        $shortcode_controller->register_actions();
    }

    /**
     * Internal function to register the admin actions after the 'plugin_is_working' check.
     *
     * @return    void
     */
    private function register_admin_actions() {
        // add the admin panel
        $admin_controller = self::get_controller( 'Admin' );
        $admin_controller->register_actions();

        // admin notices
        $admin_notices_controller = self::get_controller( 'Admin\Admin_Notices' );
        $admin_notices_controller->register_actions();

        // dashboard controller
        $dashboard_controller = self::get_controller( 'Admin\Dashboard' );
        $dashboard_controller->register_actions();

        // campaigns controller
        $campaigns_controller = self::get_controller( 'Admin\Campaigns' );
        $campaigns_controller->register_actions();

        // fundraiser controller
        $fundraisers_controller = self::get_controller( 'Admin\Fundraisers' );
        $fundraisers_controller->register_actions();

        // teams controller
        $teams_controller = self::get_controller( 'Admin\Teams' );
        $teams_controller->register_actions();

        // settings controller
        $settings_controller = self::get_controller( 'Admin\Settings' );
        $settings_controller->register_actions();

        // donations controller
        $donations_controller = self::get_controller( 'Admin\Donations' );
        $donations_controller->register_actions();

        // donors controller
        $donors_controller = self::get_controller( 'Admin\Donors' );
        $donors_controller->register_actions();

        // participants controller
        $participants_controller = self::get_controller( 'Admin\Participants' );
        $participants_controller->register_actions();
    }

    private function register_activity_log() {
        $activity_feed_controller = self::get_controller( 'Activity_Feed' );
        $activity_feed_controller->register_actions();
    }

    /**
     * Internal function to register all upgrade checks.
     *
     * @return    void
     */
    private function register_upgrade_checks() {
        $install_controller = self::get_controller( 'Install' );
        $install_controller->register_actions();
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
    }

    /**
     * Callback to deactivate the plugin.
     *
     * @wp-hook register_deactivation_hook
     *
     * @return    void
     */
    public function deactivate() {
        // de-register any cron jobs
    }

    private function register_tables() {
        global $wpdb;
        $wpdb->donormeta    = $wpdb->prefix . 'pr_donormeta';
        $wpdb->donationmeta = $wpdb->prefix . 'pr_donationmeta';
    }

    public function register_routes() {
        $donation_rest_controller = self::get_controller( 'Api\Donation_Rest_Controller' );
        $donation_rest_controller->register_routes();

        $connection_rest_controller = self::get_controller( 'Api\Connection_Rest_Controller' );
        $connection_rest_controller->register_routes();
    }

}