<?php
/*
 * (c) PeerRaiser <http://peerraiser.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PeerRaiser;

/**
 * Registers an autoloader for loading classes
 */
class Autoloader {

    /**
     * Initialize the collections used to maintain the actions and filters.
     */
    public function __construct() {
    }

    /**
     * Class Autoloader
     *
     * Function called by spl_autoload_register that registers our classes
     *
     * @since     1.0.0
     * @param     object    $class_name    The class being loaded
     */
    public function register_class_autoloader( $class_name ) {

        // Convert it to lowercase and remove forward slash in front
        $class_name = strtolower(ltrim(str_replace('_', '-', $class_name), '\\'));

        // Check if this is a class with our plugin name at the beginning
        if ( stripos($class_name, 'peerraiser\\') === 0 ) {
            $class_name = substr($class_name, strlen('peerraiser\\'));
        } else {
            return false;
        }

        $file_name  = '';
        $namespace = '';
        if ($last_namespace_position = strripos($class_name, '\\')) {
            // If there's a namespace
            $namespace = substr($class_name, 0, $last_namespace_position);
            $class_name = substr($class_name, $last_namespace_position + 1);
            $file_name  = 'application' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . 'class-'.$class_name.'.php';
        } else {
            // If there's no namespace
            $file_name  = 'application' . DIRECTORY_SEPARATOR . 'class-'.$class_name.'.php';
        }

        // Check if file is readable before trying to load it
        $plugin_directory_path = plugin_dir_path(__FILE__);
        if (is_readable($plugin_directory_path . $file_name)) {
            try {
                require( $plugin_directory_path . $file_name);
            } catch (Exception $e) {
                print_r($e);
            }
        }

    }

}