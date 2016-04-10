<?php

namespace PeerRaiser\Controller\Admin;

/**
 * LaterPay menu controller.
  */
class Base extends \PeerRaiser\Controller\Base {

    /**
     * Render the navigation for the plugin backend.
     *
     * @param    string    $file
     * @param    string    $view_dir    view directory
     *
     * @return    string    $html
     */
    public function get_menu( $file = null, $view_dir = null ) {
        if ( empty( $file ) ) {
            $file = 'backend/partials/navigation';
        }

        $current_page   = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : \PeerRaiser\Helper\View::$pluginPage;
        $menu           = \PeerRaiser\Helper\View::get_admin_menu();
        $plugin_page    = \PeerRaiser\Helper\View::$pluginPage;

        $view_args      = array(
            'menu'         => $menu,
            'current_page' => $current_page,
            'plugin_page'  => $plugin_page,
        );

        $this->assign( 'peerraiser', $view_args );
        return $this->get_text_view( $file, $view_dir );
    }

}
