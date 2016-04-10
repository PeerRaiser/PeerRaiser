<?php

namespace PeerRaiser\Core\Logger\Handler;

/**
 * PeerRaiser core logger handler WordPress.
 */
class WordPress extends Handler_Abstract {

    /**
     *
     * @var    array
     */
    protected $records = array();

    /**
     * @param    integer    $level    The minimum logging level at which this handler will be triggered
     */
    public function __construct( $level = \PeerRaiser\Core\Logger::DEBUG ) {
        parent::__construct( $level );

        add_action( 'wp_footer',             array( $this, 'render_records' ), 1000 );
        add_action( 'admin_footer',          array( $this, 'render_records' ), 1000 );
        add_action( 'wp_enqueue_scripts',    array( $this, 'load_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
        add_action( 'admin_bar_menu',        array( $this, 'admin_bar_menu' ), 1000 );
    }


    /**
     * Added element into wp menu
     *
     * @global    type    $wp_admin_bar
     *
     * @return    void
     */
    public function admin_bar_menu() {
        global $wp_admin_bar;

        $args = array(
            'id'        => 'tr_js_toggleDebuggerVisibility',
            'parent'    => 'top-secondary',
            'title'     => __( 'PeerRaiser Debugger', 'peerraiser' )
        );

        $wp_admin_bar->add_menu( $args );
    }

    /**
     * To handle or not to handle
     *
     * @param    array    Record data
     *
     * @return    bool
     */
    public function handle( array $record ) {
        if ( $record['level'] < $this->level ) {
            return false;
        }

        $this->records[] = $record;
        return true;
    }


    /**
     * Load CSS and JS for debug pane.
     *
     * @wp-hook    wp_enqueue_scripts
     *
     * @return    void
     */
    public function load_assets() {
        wp_register_style(
            'peerraiser-debugger',
            $this->config->get( 'css_url' ) . 'peerraiser-debugger.css',
            array(),
            $this->config->version
        );

        wp_register_script(
            'peerraiser-debugger',
            $this->config->get( 'js_url' ) . 'peerraiser-debugger.js',
            array( 'jquery' ),
            $this->config->version
        );

        if ( $this->config->get( 'debug_mode' ) ) {
            wp_enqueue_style( 'peerraiser-debugger' );
            wp_enqueue_script( 'peerraiser-debugger' );
        }
    }

    /**
     * Callback to render all records to footer.
     *
     * @wp-hook    wp_footer
     *
     * @return    void
     */
    public function render_records() {
        $view_args = array(
            'memory_peak'       => memory_get_peak_usage() / pow( 1024, 2 ),
            'records'           => $this->records,
            'tabs'              => $this->get_tabs(),
            'formatted_records' => $this->get_formatter()->format_batch( $this->records ),
        );

        $this->assign( 'peerraiser_records', $view_args );

        echo $this->get_text_view( 'backend/logger/wordpress-handler-records' );
    }


    /**
     * @return    array    $tabs
     */
    protected function get_tabs() {
        $events = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $events->get_debug_data();
        return array(
            array(
                'name'      => __( 'Requests', 'peerraiser' ),
                'content'   => array_merge( $_GET, $_POST ),
                'type'      => 'array',
            ),
            array(
                'name'      => sprintf( __( 'Cookies<span class="pr_badge">%s</span>', 'peerraiser' ), count( $_COOKIE ) ),
                'content'   => $_COOKIE,
                'type'      => 'array',
            ),
            array(
                'name'      => __( 'System Config', 'peerraiser' ),
                'content'   => $this->get_system_info(),
                'type'      => 'array',
            ),
            array(
                'name'      => __( 'Plugin Config', 'peerraiser' ),
                'content'   => $this->config->get_all(),
                'type'      => 'array',
            ),
            array(
                'name'      => sprintf( __( 'Plugin Hooks<span class="pr_badge">%s</span>', 'peerraiser' ), count( $events ) ),
                'content'   => $this->get_formatter()->format_batch( $events ),
                'type'      => 'html',
            ),
        );
    }


    /**
     * Get system info
     *
     * @return    array
     */
    public function get_system_info() {
        // get theme data
        $theme_data = wp_get_theme();
        $theme      = $theme_data->Name . ' ' . $theme_data->Version;

        if ( ! function_exists( 'get_plugins' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        // get active plugin data
        $installed_plugins  = get_plugins();
        $active_plugins     = get_option( 'active_plugins', array() );
        $plugins            = array();

        foreach ( $installed_plugins as $plugin_path => $plugin ) {
            if ( ! in_array( $plugin_path, $active_plugins ) ) {
                continue;
            }

            array_push( $plugins, $plugin['Name'] . ' ' . $plugin['Version'] );
        }

        // get active network plugin data
        if ( is_multisite() ) {
            $network_plugins        = wp_get_active_network_plugins();
            $active_network_plugins = get_site_option( 'active_sitewide_plugins', array() );

            foreach ( $plugins as $plugin_path ) {
                $plugin_base = plugin_basename( $plugin_path );
                if ( ! array_key_exists( $plugin_base, $active_network_plugins ) ) {
                    continue;
                }

                $network_plugin = get_plugin_data( $plugin_path );

                array_push( $network_plugins, $network_plugin['Name'] . ' ' . $network_plugin['Version'] );
            }
        }

        // collect system info
        $system_info = array(
            'WordPress version'         => get_bloginfo( 'version' ),
            'Multisite'                 => is_multisite() ? __( 'yes', 'peerraiser' ) : __( 'no', 'peerraiser' ),
            'WordPress memory limit'    => ( $this->let_to_num( WP_MEMORY_LIMIT ) / 1024 ) . ' MB',
            'Active plugins'            => implode( ', ', $plugins ),
            'Network active plugins'    => is_multisite() ? $network_plugins : __( 'none', 'peerraiser' ),
            'Registered post types'     => implode( ', ', get_post_types( array( 'public' => true ) ) ),
            'Active theme'              => $theme,
            'PHP version'               => PHP_VERSION,
            'PHP memory limit'          => ini_get( 'memory_limit' ),
            'PHP modules'               => implode( ', ', get_loaded_extensions() ),
            'Web server info'           => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : '',
        );

        return $system_info;
    }

    /**
     * Convert sizes.
     *
     * @param     unknown       $v
     *
     * @return    int|string
     */
    static function let_to_num( $v ) {
        $l   = substr( $v, -1 );
        $ret = substr( $v, 0, -1 );

        switch ( strtoupper( $l ) ) {
            case 'P': // fall-through
            case 'T': // fall-through
            case 'G': // fall-through
            case 'M': // fall-through
            case 'K': // fall-through
                $ret *= 1024;
                break;
            default:
                break;
        }

        return $ret;
    }
}
