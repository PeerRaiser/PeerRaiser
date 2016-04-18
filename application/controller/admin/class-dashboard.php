<?php

namespace PeerRaiser\Controller\Admin;

class Dashboard  extends Base {

    /**
     * @see \PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_peerraiser_dismiss_message' => array(
                array( 'process_dismiss_message_request', 100 ),
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
            ),
        );
    }


    /**
     * @see \PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific CSS
        wp_register_style(
            'peerraiser-admin-dashboard',
            $this->config->get( 'css_url' ) . 'peerraiser-admin-dashboard.css',
            array( 'peerraiser-font-awesome' ),
            $this->config->get( 'version' )
        );
        wp_enqueue_style( 'peerraiser-admin-dashboard' );

        // load page-specific JS
        wp_register_script(
            'peerraiser-select2',
            $this->config->get( 'js_url' ) . 'vendor/select2.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-backend-dashboard',
            $this->config->get( 'js_url' ) . 'peerraiser-backend-dashboard.js',
            array( 'jquery', 'peerraiser-select2' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'peerraiser-select2' );
        wp_enqueue_script( 'peerraiser-backend-dashboard' );

        // translations
        $i18n = array(
            'total'   => __( 'Total', 'peerraiser' ),
            'sales'   => __( 'sales', 'peerraiser' ),
            'signups' => __( 'signups', 'peerraiser' ),
            'delete'  => __( 'Delete', 'peerraiser' ),
        );

        // pass localized strings and variables to script
        // $time_passes_model  = new \PeerRaiser\Model\TimePass();

        $plugin_options = get_option( 'peerraiser_options', array() );

        wp_localize_script(
            'peerraiser-backend-dashboard',
            'pr_dashboard_variables',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri()
            )
        );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $view_args = array(
            'standard_currency'    => $plugin_options['currency'],
            'show_welcome_message' => filter_var($plugin_options['show_welcome_message'], FILTER_VALIDATE_BOOLEAN),
            'display_name'         => $this->get_current_users_name(),
            'plugin_version'       => $plugin_options['peerraiser_version'],
            'admin_url'            => get_admin_url(),
            'font_awesome_class'   => array(
                'step_1'           => 'fa-square-o',
                'step_2'           => 'fa-square-o',
                'step_3'           => $this->get_campaign_status()
            )
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/dashboard' );
    }


    private function get_current_users_name(){
        $current_user = wp_get_current_user();
        return ( isset($current_user->user_firstname) && !empty($current_user->user_firstname) ) ? $current_user->user_firstname : $current_user->display_name;
    }


    private function get_campaign_status() {
        $campaigns_count = wp_count_posts( 'pr_campaign' );
        return ( $campaigns_count->publish > 0 ) ? 'fa-check-square-o' : 'fa-square-o';
    }


    public function process_dismiss_message_request( \PeerRaiser\Core\Event $event ) {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
            )
        );

        // Attempt to verify the nonce, and exit early if it fails
        if ( !wp_verify_nonce( $_POST['nonce'], 'dismiss_'.$_POST['message_type'] ) ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'Verify nonce failed.', 'peerraiser' ),
                )
            );
            return;
        }

        // Set "show welcome message" option to false
        $plugin_options = get_option( 'peerraiser_options', array() );
        $plugin_options['show_welcome_message'] = false;
        update_option( 'peerraiser_options', $plugin_options );

        $event->set_result(
            array(
                'success' => true,
                'message' => __( 'Message has been dismissed', 'peerraiser' ),
            )
        );
    }

}
