<?php

namespace PeerRaiser\Model\Frontend\Widget;

use \PeerRaiser\Core\Setup;

class PeerRaiser_Widget extends \WP_Widget {

    /**
     * Contains all settings for the plugin.
     *
     * @var    \PeerRaiser\Model\Config
     */
    protected $config;

    /**
     * Variables for substitution in templates.
     *
     * @var    array
     */
    protected $variables = array();

    public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
        $this->config = Setup::get_plugin_config();
        parent::__construct( $id_base, $name, $widget_options, $control_options );
    }

    /**
     * Assign variable for substitution in templates.
     *
     * @param    string    $variable    name variable to assign
     * @param    mixed     $value       value variable for assign
     *
     * @return    void
     */
    public function assign( $variable, $value ) {
        $this->variables[ $variable ] = $value;
    }

    /**
     * Get HTML from file.
     *
     * @param    string    $file        file to get HTML string
     * @param    string    $view_dir    view directory
     *
     * @return    string    $html    html output as string
     */
    public function get_text_view( $file, $view_dir = null ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }

        $view_dir  = isset( $view_dir ) ? $view_dir : $this->config->get( 'view_dir' );
        $view_file = $view_dir . $file . '.php';
        if ( ! file_exists( $view_file ) ) {
            $msg = sprintf(
                __( '%s : <code>%s</code> not found', 'peerraiser' ),
                __METHOD__,
                $file
            );

            return '';
        }

        ob_start();
        include( $view_file );
        $thread = ob_get_contents();
        ob_end_clean();
        $html = $thread;

        $this->init_assignments();

        return $html;
    }

    protected function init_assignments() {
        $this->variables = array();
        // assign the config to the views
        $this->assign( 'config', $this->config );
    }
}