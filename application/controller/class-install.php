<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser installation controller.
 */
class Install extends Base {
    /**
     * @see PeerRaiser\Core\Event\Subscriber_Interface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_post_metadata' => array(
                array( 'peerraiser_on_plugin_is_working', 200 )
            ),
            'peerraiser_admin_init' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'trigger_requirements_check' ),
                array( 'trigger_update_capabilities' ),
            ),
            'peerraiser_update_capabilities' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'update_capabilities' ),
            ),
            'peerraiser_check_requirements' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugins_page_view', 200 ),
                array( 'check_requirements' ),
            ),
            'peerraiser_admin_notices' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_plugins_page_view', 200 ),
                array( 'render_requirements_notices' ),
                array( 'check_for_updates' ),
            ),
        );
    }

    /**
     * Render admin notices, if requirements are not fulfilled.
     *
     * @wp-hook admin_notices
     *
     * @return    void
     */
    public function render_requirements_notices() {
        $notices = $this->check_requirements();
        if ( count( $notices ) > 0 ) {
            $out = join( "\n", $notices );
            echo '<div class="error">' . $out . '</div>';
        }
    }


    /**
     * Trigger requirements check
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function trigger_requirements_check( \PeerRaiser\Core\Event $event ) {
        $new_event = new \PeerRaiser\Core\Event( $event->get_arguments() );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_check_requirements', $new_event );
    }


    /**
     * Check plugin requirements. Deactivate plugin and return notices if requirements are not fulfilled.
     *
     * @global    string    $wp_version
     *
     * @return    array    $notices
     */
    public function check_requirements() {
        global $wp_version;

        $installed_php_version          = phpversion();
        $installed_wp_version           = $wp_version;
        $required_php_version           = '5.2.4';
        $required_wp_version            = '3.5.2';
        $installed_php_is_compatible    = version_compare( $installed_php_version, $required_php_version, '>=' );
        $installed_wp_is_compatible     = version_compare( $installed_wp_version, $required_wp_version, '>=' );

        $notices = array();
        $template = __( '<p>PeerRaiser: Your server <strong>does not</strong> meet the minimum requirement of %s version %s or higher. You are running %s version %s.</p>', 'peerraiser' );

        // check PHP compatibility
        if ( ! $installed_php_is_compatible ) {
            $notices[] = sprintf( $template, 'PHP', $required_php_version, 'PHP', $installed_php_version );
        }

        // check WordPress compatibility
        if ( ! $installed_wp_is_compatible ) {
            $notices[] = sprintf( $template, 'Wordpress', $required_wp_version, 'Wordpress', $installed_wp_version );
        }

        // deactivate plugin, if requirements are not fulfilled
        if ( count( $notices ) > 0 ) {
            // suppress 'Plugin activated' notice
            unset( $_GET['activate'] );
            deactivate_plugins( $this->config->plugin_base_name );
            $notices[] = __( 'The PeerRaiser plugin could not be installed. Please fix the reported issues and try again.', 'peerraiser' );
        }

        return $notices;
    }


    /**
     * Compare plugin version with latest version and perform an update, if required.
     *
     * @wp-hook plugins_loaded
     *
     * @return     void
     */
    public function check_for_updates() {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $current_version = $plugin_options['peerraiser_version'];
        if ( version_compare( $current_version, $this->config->version, '!=' ) ) {
            $this->install();
        }
    }

    /**
     * Create custom tables and set the required options.
     *
     * @return void
     */
    public function install() {
        global $wpdb;

        // cancel the installation process, if the requirements check returns errors
        $notices = (array) $this->check_requirements();
        if ( count( $notices ) ) {
            $this->logger->warning( __METHOD__, $notices );
            return;
        }

        $plugin_options = get_option( 'peerraiser_options', array() );

        // Default options
        $plugin_options['currency']                          = $this->config->get( 'currency.default' );
        $plugin_options['fundraiser_slug']                   = 'give';
        $plugin_options['campaign_slug']                     = 'campaign';
        $plugin_options['disable_css_styles']                = false;
        $plugin_options['test_mode']                         = true;
        $plugin_options['show_welcome_message']              = true;
        $plugin_options['donation_receipt_enabled']          = true;
        $plugin_options['new_donation_notification_enabled'] = true;
        $plugin_options['welcome_email_enabled']             = true;
        $plugin_options['from_name']                         = get_bloginfo( 'name' );
        $plugin_options['from_email']                        = get_bloginfo( 'admin_email' );
        $plugin_options['new_donation_notification_to']      = get_bloginfo( 'admin_email' );
        $plugin_options['donation_receipt_subject']          = __('Thank you for your donation', 'peerraiser');
        $plugin_options['donation_receipt_body']             = __('Dear [peerraiser_email show=donor_first_name],

            Thank you so much for your generous donation.

            Transaction Summary
            [peerraiser_email show=donation_summary]

            With thanks,
            [peerraiser_email show=site_name]', 'peerraiser');
        $plugin_options['new_donation_notification_subject'] = __('New donation received', 'peerraiser');
        $plugin_options['new_donation_notification_body']    = __('[peerraiser_email show=donor] has just made a donation!

            Summary
            [peerraiser_email show=donation_summary]', 'peerraiser');
        $plugin_options['welcome_email_subject']             = __('Welcome!', 'peerraiser');
        $plugin_options['welcome_email_body']                = __('Welcome to the [peerraiser_email show=campaign_name] campaign!', 'peerraiser');


        // keep the plugin version up to date
        $plugin_options['peerraiser_version'] = $this->config->get( 'version' );

        update_option( 'peerraiser_options', $plugin_options );

        // clear opcode cache
        \PeerRaiser\Helper\Cache::reset_opcode_cache();

        // update capabilities
        $peerraiser_capabilities = new \PeerRaiser\Core\Capability();
        $peerraiser_capabilities->populate_roles();
    }


    /**
     * Trigger requirements check.
     *
     * @param    \PeerRaiser\Core\Event    $event
     */
    public function trigger_update_capabilities( \PeerRaiser\Core\Event $event ) {
        $new_event = new \PeerRaiser\Core\Event();
        $new_event->set_echo( false );
        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_update_capabilities', $new_event );
    }


    /**
     * Update user roles capabilities.
     *
     * @param    \PeerRaiser\Core\Event    $event
     */
    public function update_capabilities( \PeerRaiser\Core\Event $event ) {
        list( $roles ) = $event->get_arguments() + array( array() );
        // update capabilities
        $peerraiser_capabilities = new \PeerRaiser\Core\Capability();
        $peerraiser_capabilities->update_roles( (array) $roles );
    }

}
