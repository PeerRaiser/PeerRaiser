<?php

/**
 * Plugin Name:       PeerRaiser
 * Plugin URI:        http://PeerRaiser.com
 * Description:       Peer-to-peer fundraising for WordPress
 * Version:           1.0.0
 * Author:            Nate Allen
 * Author URI:        http://peerraiser.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       peerraiser
 * Domain Path:       /languages
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

if ( ! defined( 'PEERRAISER_TEXT_DOMAIN' ) ) {
    define( 'PEERRAISER_TEXT_DOMAIN', 'peerraiser' );
}

/**
 * Include Libraries
 */
if ( file_exists(  plugin_dir_path( __FILE__ ) . 'library/CMB2/init.php' ) ) {
    // CMB2
    require_once( plugin_dir_path( __FILE__ ) . 'library/CMB2/init.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'library/CMB2-multiselect/cmb-field-multiselect.php' );
}
if ( file_exists(  plugin_dir_path( __FILE__ ) . 'library/class-peerraiser-p2p.php' ) ) {
    // Posts 2 Posts
    require_once( plugin_dir_path( __FILE__ ) . 'library/class-peerraiser-p2p.php');
    $peerraiser_posts_to_posts = new PeerRaiser_P2P();
}
if ( file_exists(  plugin_dir_path( __FILE__ ) . 'library/Pimple/Container.php' ) ) {
    // Pimple
    require_once( plugin_dir_path( __FILE__ ) . 'library/Pimple/Container.php');
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
        $context = array(
            'message' => $e->getMessage(),
            'trace'   => $e->getTrace(),
        );
        $logger = \PeerRaiser\Core\Logger::get_logger();
        $logger->critical( __( 'Unexpected error during plugin init', 'peerraiser' ), $context );
    }

}

/**
 * Callback for activating the plugin.
 */
function peerraiser_activate() {
    // Autoloader and logger stuff
    peerraiser_before_start();

    $config = \PeerRaiser\Core\Setup::get_plugin_config();
    $peerraiser = new \PeerRaiser\Core\Bootstrap( $config );

    $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();

    $dispatcher->dispatch( 'peerraiser_activate_before' );
    $peerraiser->activate();
    $dispatcher->dispatch( 'peerraiser_activate_after' );
}

/**
 * Run before plugins_loaded, activate_peerraiser, and deactivate_peerraiser, to register
 * our autoload paths and boot-up the logger
 *
 * @since     1.0.0
 * @return    null
 */
function peerraiser_before_start() {
    // Autoloader
    require( plugin_dir_path( __FILE__ ) . 'class-autoloader.php');
    $peerraiser_autoloader = new PeerRaiser\Autoloader();
    spl_autoload_register( array($peerraiser_autoloader, 'register_class_autoloader') );

    // Boot up logger
    $logger = \PeerRaiser\Core\Logger::get_logger();
}