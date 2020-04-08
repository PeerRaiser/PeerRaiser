<?php
/**
 * Plugin Name: PeerRaiser
 * Plugin URI:  https://PeerRaiser.com
 * Description: PeerRaiser makes it easy to create powerful peer-to-peer fundraising campaigns on your own WordPress site.
 * Version:     1.3.2
 * Author:      Nate Allen
 * Author URI:  https://peerraiser.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: peerraiser
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The main plugin file
 */
if ( ! defined( 'PEERRAISER_FILE' ) ) {
    define( 'PEERRAISER_FILE', __FILE__ );
}

if ( ! defined( 'PEERRAISER_PATH' ) ) {
    define( 'PEERRAISER_PATH', trailingslashit( __DIR__ ) );
}

if ( ! defined( 'PEERRAISER_TEXT_DOMAIN' ) ) {
    define( 'PEERRAISER_TEXT_DOMAIN', 'peerraiser' );
}

/**
 * Include Libraries
 */
if ( file_exists(  plugin_dir_path( __FILE__ ) . 'library/CMB2/init.php' ) ) {
    // CMB2
    require_once( plugin_dir_path( __FILE__ ) . 'library/CMB2/init.php' );
}

// Kick everything off
add_action( 'plugins_loaded', 'peerraiser_init' );

// Register activation/deactivation functions
register_activation_hook( __FILE__, 'peerraiser_activate' );
register_deactivation_hook( __FILE__, 'peerraiser_deactivate' );

/**
 * Callback for starting the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function peerraiser_init() {
    peerraiser_before_start();

    $config = \PeerRaiser\Core\Setup::get_plugin_config();
    $peerraiser = new \PeerRaiser\Core\Bootstrap( $config );

    try {
        $peerraiser->run();
    } catch ( Exception $e ) {
        wp_die( print_r( $e, true ) );
    }

}

/**
 * Callback for activating the plugin.
 */
function peerraiser_activate() {
    peerraiser_before_start();

    $config = \PeerRaiser\Core\Setup::get_plugin_config();
    $peerraiser = new \PeerRaiser\Core\Bootstrap( $config );

    do_action( 'peerraiser_activate_before' );
    $peerraiser->activate();
    do_action( 'peerraiser_activate_after' );
}

/**
 * Callback for deactivating the plugin.
 */
function peerraiser_deactivate() {
    do_action( 'peerraiser_deactivate_before' );
    // $peerraiser->deactivate();
    do_action( 'peerraiser_deactivate_after' );
}

/**
 * Run before plugins_loaded, activate_peerraiser, and deactivate_peerraiser, to register
 * our autoload paths
 *
 * @since     1.0.0
 * @return    null
 */
function peerraiser_before_start() {
    // Autoloader
    require( plugin_dir_path( __FILE__ ) . 'class-autoloader.php');
    $peerraiser_autoloader = new PeerRaiser\Autoloader();
    spl_autoload_register( array($peerraiser_autoloader, 'register_class_autoloader') );
}
