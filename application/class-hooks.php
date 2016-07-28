<?php

namespace PeerRaiser;

class Hooks {
    private static $wp_action_prefix      = 'wp_action_';
    private static $wp_filter_prefix      = 'wp_filter_';
    private static $wp_shortcode_prefix   = 'wp_shcode_';
    private static $pr_filter_suffix      = '_filter';
    private static $pr_filter_args_suffix = '_arguments';
    private static $instance              = null;
    private static $pr_actions            = array();
    private static $pr_shortcodes         = array();


    /**
     * Singleton to get only one event dispatcher
     *
     * @return PeerRaiser\Hooks
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Magic method to process WordPress actions/filters.
     *
     * @param    string    $name Method name.
     * @param    array     $args Method arguments.
     *
     * @return    mixed
     */
    public function __call( $name, $args ) {
        $method = substr( $name, 0, 10 );
        $action = substr( $name, 10 );
        $result = null;

        try {
            switch ( $method ) {
                case self::$wp_action_prefix:
                    $this->run_wp_action( $action, $args );
                    break;
                case self::$wp_filter_prefix:
                    $result = $this->run_wp_filter( $action, $args );
                    break;
                case self::$wp_shortcode_prefix:
                    $result = $this->run_wp_shortcode( $action, $args );
                    break;
                default:
                    throw new \RuntimeException( sprintf( 'Method "%s" is not found within PeerRaiser\Core\Event\Dispatcher class.', $name ) );
            }
        } catch ( \Exception $e ) {
            $logger = \PeerRaiser\Core\Logger::get_logger();
            $logger->error( $e->getMessage(), array( 'trace' => $e->getTraceAsString() ) );
        }

        return $result;
    }


    /**
     * Registers WordPress hooks to trigger internal plugin events.
     */
    public function init() {
        // add_filter( 'the_content',                        array( $this, self::$wp_filter_prefix . 'peerraiser_post_content' ), 1 );
        // add_filter( 'get_post_metadata',                  array( $this, self::$wp_filter_prefix . 'peerraiser_post_metadata' ), 10, 4 );
        // add_filter( 'the_posts',                          array( $this, self::$wp_filter_prefix . 'peerraiser_posts' ) );

        // add_filter( 'terms_clauses',                      array( $this, self::$wp_filter_prefix . 'peerraiser_terms_clauses' ) );
        // add_filter( 'date_query_valid_columns',           array( $this, self::$wp_filter_prefix . 'peerraiser_date_query_valid_columns' ) );

        // add_filter( 'wp_get_attachment_image_attributes', array( $this, self::$wp_filter_prefix . 'peerraiser_attachment_image_attributes' ), 10, 3 );
        // add_filter( 'wp_get_attachment_url',              array( $this, self::$wp_filter_prefix . 'peerraiser_attachment_get_url' ), 10, 2 );
        // add_filter( 'prepend_attachment',                 array( $this, self::$wp_filter_prefix . 'peerraiser_attachment_prepend' ) );

        // add_action( 'template_redirect',                  array( $this, self::$wp_action_prefix . 'peerraiser_loaded' ) );
        // add_action( 'wp_footer',                          array( $this, self::$wp_action_prefix . 'peerraiser_post_footer' ) );
        add_action( 'wp_enqueue_scripts',                       array( $this, self::$wp_action_prefix . 'peerraiser_enqueue_scripts' ) );
        add_action( 'init',                                     array( $this, self::$wp_action_prefix . 'peerraiser_wordpress_init' ) );
        add_action( 'admin_init',                               array( $this, self::$wp_action_prefix . 'peerraiser_admin_init' ) );
        add_action( 'admin_head',                               array( $this, self::$wp_action_prefix . 'peerraiser_admin_head' ) );
        add_action( 'admin_menu',                               array( $this, self::$wp_action_prefix . 'peerraiser_admin_menu' ) );
        add_action( 'admin_notices',                            array( $this, self::$wp_action_prefix . 'peerraiser_admin_notices' ) );
        add_action( 'in_admin_header',                          array( $this, self::$wp_action_prefix . 'peerraiser_in_admin_header' ) );
        add_action( 'cmb2_admin_init',                          array( $this, self::$wp_action_prefix . 'peerraiser_cmb2_admin_init' ) );
        add_action( 'admin_print_styles-post.php',              array( $this, self::$wp_action_prefix . 'peerraiser_admin_enqueue_styles_post_edit' ) );
        add_action( 'admin_print_styles-post-new.php',          array( $this, self::$wp_action_prefix . 'peerraiser_admin_enqueue_styles_post_new' ) );
        add_action( 'admin_enqueue_scripts',                    array( $this, self::$wp_action_prefix . 'peerraiser_admin_enqueue_scripts' ) );
        add_action( 'p2p_init',                                 array( $this, self::$wp_action_prefix . 'peerraiser_p2p_init' ) );
        add_action( 'pre_get_posts',                            array( $this, self::$wp_action_prefix . 'peerraiser_pre_get_posts' ) );
        add_action( 'add_post_meta',                            array( $this, self::$wp_action_prefix . 'peerraiser_before_post_meta_added' ), 10, 3 );
        add_action( 'added_post_meta',                          array( $this, self::$wp_action_prefix . 'peerraiser_after_post_meta_added' ), 10, 4 );
        add_action( 'update_post_meta',                         array( $this, self::$wp_action_prefix . 'peerraiser_before_post_meta_updated' ), 10, 4 );
        add_action( 'updated_post_meta',                        array( $this, self::$wp_action_prefix . 'peerraiser_after_post_meta_updated' ), 10, 4 );
        add_action( 'delete_post_meta',                         array( $this, self::$wp_action_prefix . 'peerraiser_before_post_meta_deleted' ), 10, 4 );
        add_action( 'before_delete_post',                       array( $this, self::$wp_action_prefix . 'peerraiser_before_delete_post' ), 10, 1 );
        add_action( 'cmb2_save_post_fields',                    array( $this, self::$wp_action_prefix . 'peerraiser_cmb2_save_post_fields' ), 10, 4 );
        add_action( 'save_post',                                array( $this, self::$wp_action_prefix . 'peerraiser_post_saved' ), 10, 3 );
        add_action( 'publish_pr_donation',                      array( $this, self::$wp_action_prefix . 'peerraiser_donation_published' ), 10, 2 );
        add_action( 'save_post_pr_donation',                    array( $this, self::$wp_action_prefix . 'peerraiser_donation_post_saved' ), 10, 3 );
        add_action( 'delete_post',                              array( $this, self::$wp_action_prefix . 'peerraiser_post_deleted' ), 10, 1 );
        add_action( 'add_meta_boxes',                           array( $this, self::$wp_action_prefix . 'peerraiser_meta_boxes' ) );
        add_action( 'do_meta_boxes',                            array( $this, self::$wp_action_prefix . 'peerraiser_do_meta_boxes' ) );
        add_action( 'manage_pr_campaign_posts_custom_column',   array( $this, self::$wp_action_prefix . 'peerraiser_manage_campaign_columns' ), 10, 2 );
        add_action( 'manage_fundraiser_posts_custom_column',    array( $this, self::$wp_action_prefix . 'peerraiser_manage_fundraiser_columns' ), 10, 2 );
        add_action( 'manage_pr_team_posts_custom_column',       array( $this, self::$wp_action_prefix . 'peerraiser_manage_team_columns' ), 10, 2 );
        add_action( 'manage_pr_donation_posts_custom_column',   array( $this, self::$wp_action_prefix . 'peerraiser_manage_donation_columns' ), 10, 2 );
        add_action( 'manage_pr_donor_posts_custom_column',      array( $this, self::$wp_action_prefix . 'peerraiser_manage_donor_columns' ), 10, 2 );
        add_action( 'template_redirect',                        array( $this, self::$wp_action_prefix . 'peerraiser_template_redirect' ), 10, 2 );
        add_action( 'user_register',                            array( $this, self::$wp_action_prefix . 'peerraiser_user_registered' ), 10, 1 );
        add_action( 'admin_post_nopriv_peerraiser_login',       array( $this, self::$wp_action_prefix . 'peerraiser_login_form' ) );
        add_action( 'admin_post_peerraiser_login',              array( $this, self::$wp_action_prefix . 'peerraiser_login_form' ) );
        add_action( 'admin_post_nopriv_peerraiser_signup',      array( $this, self::$wp_action_prefix . 'peerraiser_signup_form' ) );
        add_action( 'admin_post_peerraiser_signup',             array( $this, self::$wp_action_prefix . 'peerraiser_signup_form' ) );

        add_filter( 'enter_title_here',                         array( $this, self::$wp_filter_prefix . 'peerraiser_enter_title_here' ), 1 );
        add_action( 'manage_edit-pr_campaign_sortable_columns', array( $this, self::$wp_filter_prefix . 'peerraiser_sortable_campaign_columns' ), 1 );
        add_filter( 'query_vars',                               array( $this, self::$wp_filter_prefix . 'peerraiser_query_vars' ), 1 );

        // add_action( 'admin_footer',                       array( $this, self::$wp_action_prefix . 'peerraiser_admin_footer' ), 1000 );
        // add_action( 'admin_bar_menu',                     array( $this, self::$wp_action_prefix . 'peerraiser_admin_bar_menu' ), 1000 );
        // add_action( 'admin_print_footer_scripts',         array( $this, self::$wp_action_prefix . 'peerraiser_admin_footer_scripts' ) );

        // add_action( 'load-post.php',                      array( $this, self::$wp_action_prefix . 'peerraiser_post_edit' ) );
        // add_action( 'load-post-new.php',                  array( $this, self::$wp_action_prefix . 'peerraiser_post_new' ) );
        // add_action( 'delete_term_taxonomy',               array( $this, self::$wp_action_prefix . 'peerraiser_delete_term_taxonomy' ) );
        // add_action( 'save_post',                          array( $this, self::$wp_action_prefix . 'peerraiser_post_save' ) );
        // add_action( 'edit_attachment',                    array( $this, self::$wp_action_prefix . 'peerraiser_attachment_edit' ) );
        // add_action( 'transition_post_status',             array( $this, self::$wp_action_prefix . 'peerraiser_transition_post_status' ), 10, 3 );

        // cache helper to purge the cache on update_option()
        // $options = array(
        //     'peerraiser_global_price',
        //     'peerraiser_global_price_revenue_model',
        //     'peerraiser_currency',
        //     'peerraiser_enabled_post_types',
        //     'peerraiser_teaser_content_only',
        //     'peerraiser_plugin_is_in_live_mode',
        // );
        // foreach ( $options as $option_name ) {
        //     add_action( 'update_option_' . $option_name, array( $this, self::$wp_action_prefix . 'peerraiser_option_update' ) );
        // }
    }


    /**
     * Dynamically register WordPress actions.
     *
     * @param    string        $name Wordpress hook name.
     * @param    string|null   $event_name PeerRaiser internal event name.
     */
    public static function add_wp_action( $name, $event_name = null) {
        if ( empty( $event_name ) ) {
            $event_name = 'peerraiser_' . $name;
        }
        add_action( $name, array( self::get_instance(), self::$wp_action_prefix . $event_name ) );

    }


    /**
     * Registers PeerRaiser event in WordPress actions pool.
     *
     * @param    string    $event_name    Event name.
     */
    public static function register_peerraiser_action( $event_name ) {
        if ( ! in_array( $event_name, self::$pr_actions ) ) {
            self::add_wp_action( $event_name, $event_name );
            self::$pr_actions[] = $event_name;
        }
    }


    /**
     * Registers PeerRaiser event in WordPress shortcode pool.
     *
     * @param    string    $event_name    Event name.
     */
    public static function register_peerraiser_shortcode( $event_name ) {
        if ( ! in_array( $event_name, self::$pr_shortcodes ) ) {
            if ( strpos( $event_name, 'peerraiser_shortcode_' ) !== false ) {
                $name = substr( $event_name, 21 );
            }
            self::add_wp_shortcode( $name, $event_name );
            self::$pr_shortcodes[] = $event_name;
        }
    }


    /**
     * Register dynamic WordPress filters.
     *
     * @param    string        $name          Wordpress hook name.
     * @param    string|null   $event_name    PeerRaiser internal event name.
     */
    public static function add_wp_filter( $name, $event_name = null) {
        if ( empty( $event_name ) ) {
            $event_name = 'peerraiser_' . $name;
        }
        add_filter( $name, array( self::get_instance(), self::$wp_filter_prefix . $event_name ) );
    }


    /**
     * Register WordPress shortcodes.
     *
     * @param    string        $name          Wordpress hook name.
     * @param    string|null   $event_name    PeerRaiser internal event name.
     */
    public static function add_wp_shortcode( $name, $event_name = null) {
        if ( empty( $event_name ) ) {
            $event_name = 'peerraiser_' . $name;
        }
        add_shortcode( $name, array( self::get_instance(), self::$wp_shortcode_prefix . $event_name ) );
    }


    /**
     * Triggered by WordPress for registered actions.
     *
     * @param     string          $action     Action name.
     * @param     array           $args       Action arguments.
     * @return    array|string
     */
    protected function run_wp_action( $action, $args = array() ) {
        // argument can have value == null, so 'isset' function is not suitable
        $default = array_key_exists( 0, $args ) ? $args[0]: '';
        try {
            $event = new \PeerRaiser\Core\Event( $args );
            if ( strpos( $action, 'wp_ajax' ) !== false ) {
                $event->set_ajax( true );
            }
            $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
            $dispatcher->dispatch( $action, $event );
            $result = $event->get_result();
        } catch ( \Exception $e ) {
            $logger = \PeerRaiser\Core\Logger::get_logger();
            $logger->error( $e->getMessage(), array( 'trace' => $e->getTraceAsString() ) );
            $result = $default;
        }
        return $result;
    }


    /**
     * Triggered by WordPress for registered filters.
     *
     * @param     string          $event_name Event name.
     * @param     array           $args Filter arguments. first argument is filtered value.
     * @return    array|string    Filtered result
     */
    protected function run_wp_filter( $event_name, $args = array() ) {
        $default = array_key_exists( 0, $args ) ? $args[0]: '';
        try {
            $event = new \PeerRaiser\Core\Event( $args );
            $event->set_result( $default );
            $event->set_echo( false );

            $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
            $dispatcher->dispatch( $event_name, $event );

            $result = $event->get_result();
        } catch ( \Exception $e ) {
            $logger = \PeerRaiser\Core\Logger::get_logger();
            $logger->error( $e->getMessage(), array( 'trace' => $e->getTraceAsString() ) );
            $result = $default;
        }
        return $result;
    }


    /**
     * Triggered by WordPress for registered shortcode.
     *
     * @param     string    $event_name    Event name.
     * @param     array     $args          Shortcode arguments.
     * @return    mixed                    Filtered result
     */
    protected function run_wp_shortcode( $event_name, $args = array() ) {
        $event = new \PeerRaiser\Core\Event( $args );
        $event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( $event_name, $event );

        return $event->get_result();
    }


    /**
     * Applies filters to triggered by PeerRaiser events.
     *
     * @param     string          $action    Action name.
     * @param     array           $value     Value to filter.
     * @return    string|array
     */
    public static function apply_filters( $action, $value ) {
        return apply_filters( $action . self::$pr_filter_suffix, $value );
    }


    /**
     * Applies filters to triggered by PeerRaiser events.
     *
     * @param     string          $action    Action name.
     * @param     array           $value     Value to filter.
     * @return    string|array
     */
    public static function apply_arguments_filters( $action, $value ) {
        return apply_filters( $action . self::$pr_filter_args_suffix, $value );
    }


    /**
     * Late load event for other plugins to remove / add own actions to the PeerRaiser plugin.
     *
     * @return void
     */
    public function peerraiser_ready() {
        /**
         * Late loading event for PeerRaiser.
         *
         * @param    PeerRaiser\Core\Bootstrap    $this
         */
        do_action( 'peerraiser_ready', $this );
    }

}
