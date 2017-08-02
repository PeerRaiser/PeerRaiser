<?php

namespace PeerRaiser\Controller\Frontend;

class Account extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'admin_post_nopriv_peerraiser_login',  array( $this, 'handle_login' ) );
        add_action( 'admin_post_peerraiser_login',         array( $this, 'handle_login' ) );
        add_action( 'admin_post_nopriv_peerraiser_signup', array( $this, 'handle_signup' ) );
        add_action( 'admin_post_peerraiser_signup',        array( $this, 'handle_signup' ) );
    }

    public function handle_login() {
        // Get the default dashboard and login page urls
        $plugin_options        = get_option( 'peerraiser_options', array() );
        $participant_dashboard = get_permalink( $plugin_options[ 'participant_dashboard' ] );
        $login_page            = get_permalink( $plugin_options[ 'login_page' ] );

        // Check for empty fields and redirect with error if they are
        if ( empty( $_POST['username'] ) || empty( $_POST['password'] ) ){
            $args = array(
                'errors' => 'empty_fields'
            );

            if ( isset( $_POST['next_url'] ) ) {
                $args['next_url'] = urldecode( $_POST['next_url'] );
            }

            wp_safe_redirect( add_query_arg( $args, $login_page ) );
            exit;
        }

        // If the user is already logged in, log them out first
        if ( is_user_logged_in() ) {
            wp_logout();
        }

        // If the username passed is an email address, try to look up the username
        $username = ( is_email( $_POST['username'] ) ) ? $this->get_username_by_email( $_POST['username'] ) : $_POST['username'];

        $args = array(
            'remember' => ( isset( $_POST['remember'] ) ),
            'user_login' => $username,
            'user_password' => $_POST['password'],
            'id_password' => 'pass',
        );

        $user = wp_signon( $args );

        if ( is_wp_error( $user ) ) {
            $args = array(
                'errors' => 'login'
            );

            if ( isset( $_POST['next_url'] ) ) {
                $args['next_url'] = $_POST['next_url'];
            }

            wp_safe_redirect( add_query_arg( $args, $login_page ) );
            exit;
        } else {
            $next_url = ( isset( $_POST['next_url'] ) ) ? $_POST['next_url'] : $participant_dashboard;
            wp_safe_redirect( $next_url );
            exit;
        }

    }


    public function handle_signup() {
        // Get the default dashboard and login page urls
        $plugin_options         = get_option( 'peerraiser_options', array() );
        $participant_dashboard  = get_permalink( $plugin_options[ 'participant_dashboard' ] );
        $signup_page            = get_permalink( $plugin_options[ 'signup_page' ] );

        $required = array( 'username', 'password', 'firstname', 'lastname', 'email' );

        // Loop over required field names and make sure they're not empty
        foreach($required as $field) {
            if ( empty($_POST[$field]) ) {
                $args = array(
                    'errors' => 'empty_fields'
                );

                if ( isset( $_POST['next_url'] ) ) {
                    $args['next_url'] = $_POST['next_url'];
                }

                wp_safe_redirect( add_query_arg( $args, $signup_page ) );
                exit;
            }
        }

        // Check if username already exists
        if ( username_exists( $_POST['username'] ) ){
            $args = array(
                'errors' => 'username_exists'
            );

            if ( isset( $_POST['next_url'] ) ) {
                $args['next_url'] = $_POST['next_url'];
            }

            wp_safe_redirect( add_query_arg( $args, $signup_page ) );
            exit;
        }

        // If the user is already logged in, log them out first
        if ( is_user_logged_in() ) {
            wp_logout();
        }

        // Create the user
        $user_id = wp_create_user( $_POST['username'], $_POST['password'], $_POST['email'] );

        wp_update_user(
            array(
                'ID'                   => $user_id,
                'display_name'         => $_POST['firstname'] . ' ' . $_POST['lastname'],
                'first_name'           => $_POST['firstname'],
                'last_name'            => $_POST['lastname'],
                'role'                 => 'subscriber',
                'show_admin_bar_front' => false
            )
        );

        // Log the user in
        wp_set_auth_cookie( $user_id, false, is_ssl() );

        $next_url = ( isset( $_POST['next_url'] ) ) ? $_POST['next_url'] : $participant_dashboard;
        wp_safe_redirect( $next_url );
        exit;

    }

    /**
     * Attempts to get the username based on the email address on the account.
     * If a username cannot by found with that username, the email is returned.
     *
     * @since     1.0.0
     * @param     string    $email    The email address to search with
     * @return    string              The username
     */
    private function get_username_by_email( $email ){
        $user = get_user_by( 'email', $email );

        return ( ! empty( $user ) ) ? $user->user_login : $email;
    }

}