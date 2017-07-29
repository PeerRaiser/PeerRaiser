<?php

namespace PeerRaiser\Controller\Frontend;

use \PeerRaiser\Helper\Text;

class Participant_Dashboard extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'template_redirect',                     array( $this, 'dashboard_redirect' ) );
        add_action( 'cmb2_save_user_fields',                 array( $this, 'update_user_email' ) );
        add_action( 'admin_post_peerraiser_change_password', array( $this, 'change_user_password' ) );
        add_action( 'wp_ajax_peerraiser_update_avatar',      array( $this, 'ajax_update_avatar' ) );
        add_action( 'wp_ajax_peerraiser_update_avatar',      array( $this, 'peerraiser_on_ajax_send_json' ) );
    }

    public function dashboard_redirect() {
        global $wp_query;
        $post_id = $wp_query->get_queried_object_id();

        // Get the default dashboard and login page urls
        $plugin_options        = get_option( 'peerraiser_options', array() );
        $participant_dashboard = $plugin_options[ 'participant_dashboard' ];
        $login_page_url        = get_permalink( $plugin_options[ 'login_page' ] );

        // If this is the dashboard and the user isn't logged in, redirect to the login page
        if ( $post_id == $participant_dashboard && ! is_user_logged_in() ) {
            $args = array(
                'next_url' => get_permalink( $participant_dashboard )
            );

            wp_safe_redirect( add_query_arg( $args, $login_page_url ) );
            exit;
        }
    }

    public function ajax_update_avatar() {
        $user_id = get_current_user_id();

        $avatar_id = get_user_meta( $user_id, '_peerraiser_custom_avatar', true );

        /* If the avatar has been set previously, remove it from the media library.
         * This prevents people from spamming the it.
         */
        if ( $avatar_id ==! "" ) {
            wp_delete_attachment( $avatar_id, false );
        }

        $attachment_id = media_handle_sideload( $_FILES['files'], 0 );
        update_user_meta( $user_id, '_peerraiser_custom_avatar', $attachment_id );

	    echo Text::peerraiser_json_encode( array(
		    'avatar_id' => $attachment_id,
		    'success'   => true,
		    'image_url' => wp_get_attachment_image_url( $attachment_id, 'peerraiser_campaign_thumbnail_id' )
	    ) );

	    wp_die();
    }

    /**
     * Updates the user's email address
     *
     * Because user_email isn't technically user meta, it has to be updated using the
     * wp_update_user function.
     *
     * @since     1.0.0
     */
    public function update_user_email( $object_id, $cmb_id, $updated, $cmb ) {
        if ( $cmb_id == 'dashboard_settings' && isset( $_POST['user_email'] ) ) {
            $args = array(
                'ID'         => get_current_user_id(),
                'user_email' => esc_attr( $_POST['user_email'] )
            );
            wp_update_user( $args );
        }

    }

    public function change_user_password() {
        $user                  = wp_get_current_user();
        $plugin_options        = get_option( 'peerraiser_options', array() );
        $participant_dashboard = $plugin_options[ 'participant_dashboard' ];
        $login_page_url        = get_permalink( $plugin_options[ 'login_page' ] );

        // If the nonce is incorrect, just exit
        if ( ! wp_verify_nonce( $_POST['peerraiser_change_password_nonce'], 'change_password_' . $user->ID ) )
            return;

        // If the current password isn't correct, redirect back with error code
        if ( ! wp_check_password( $_POST['current_password'], $user->user_pass, $user->ID) ) {
            $args = array(
                'errors' => 'password_incorrect'
            );
            wp_safe_redirect( add_query_arg( $args, add_query_arg( array( 'page' => 'settings' ), get_permalink( $participant_dashboard ) ) ).'#peerraiser_change_password_form' );
            exit;
        }

        // If the new password doesn't match the confirmation, redirect back with error code
        if ( $_POST['new_password'] !== $_POST['confirm_password']) {
            $args = array(
                'errors' => 'password_mismatch'
            );
            wp_safe_redirect( add_query_arg( $args, add_query_arg( array( 'page' => 'settings' ), get_permalink( $participant_dashboard ) ) ).'#peerraiser_change_password_form' );
            exit;
        }

        wp_set_password( $_POST['new_password'], $user->ID );

        $args = array(
            'next_url' => add_query_arg( array( 'page' => 'settings' ), get_permalink( $participant_dashboard ) )
        );

        wp_safe_redirect( add_query_arg( $args, $login_page_url ) );
        exit;
    }

}