<?php

namespace PeerRaiser\Controller\Frontend;

class Post extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_stylesheets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );
    }

    public function add_frontend_stylesheets() {
        wp_register_style(
            'peerraiser-frontend',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'css_url' ) . 'peerraiser-frontend.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' )
        );

        wp_enqueue_style( 'peerraiser-frontend' );
    }

    public function add_frontend_scripts() {
        wp_register_script(
            'peerraiser-frontend',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'js_url' ) . 'peerraiser-frontend.js',
            array( 'jquery' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' ),
            true
        );
        $post = get_post();
        wp_localize_script(
            'peerraiser-frontend',
            'peerraiser_variables',
            array(
                'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
                'post_id'               => ! empty( $post ) ? $post->ID : false,
            )
        );

        wp_enqueue_script( 'peerraiser-frontend' );
    }

}