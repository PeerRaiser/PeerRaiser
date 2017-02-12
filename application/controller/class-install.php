<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser installation controller.
 */
class Install extends Base {

    private $default_pages = array();

    public function __construct() {
        $this->default_pages = array(
            'thank_you' => array(
                'post_title'     => __( 'Thank You', 'peerraiser' ),
                'post_content'   => __( 'Thank you for your donation! [peerraiser_receipt]', 'peerraiser' )
            ),
            'login' => array(
                'post_title'     => __( 'Login', 'peerraiser' ),
                'post_content'   => __( '[peerraiser_login]', 'peerraiser' )
            ),
            'signup' => array(
                'post_title'     => __( 'Signup', 'peerraiser' ),
                'post_content'   => __( '[peerraiser_signup]', 'peerraiser' )
            ),
            'participant_dashboard' => array(
                'post_title'     => __( 'Participant Dashboard', 'peerraiser' ),
                'post_content'   => __( '[peerraiser_participant_dashboard]', 'peerraiser' )
            ),
        );
        parent::__construct();
    }

    /**
     * @see PeerRaiser\Core\Event\Subscriber_Interface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_admin_init' => array(
                array( 'trigger_requirements_check' ),
                array( 'trigger_update_capabilities' ),
            ),
            'peerraiser_update_capabilities' => array(
                array( 'update_capabilities' ),
            ),
            'peerraiser_check_requirements' => array(
                array( 'check_requirements' ),
            ),
            'peerraiser_admin_notices' => array(
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
            return;
        }

        $plugin_options = get_option( 'peerraiser_options', array() );
        $default_options = array();

        // Default options
        $default_options['currency']                          = $this->config->get( 'currency.default' );
        $default_options['fundraiser_slug']                   = 'give';
        $default_options['campaign_slug']                     = 'campaign';
        $default_options['disable_css_styles']                = false;
        $default_options['test_mode']                         = true;
        $default_options['show_welcome_message']              = true;
        $default_options['donation_receipt_enabled']          = true;
        $default_options['new_donation_notification_enabled'] = true;
        $default_options['welcome_email_enabled']             = true;
        $default_options['from_name']                         = get_bloginfo( 'name' );
        $default_options['from_email']                        = get_bloginfo( 'admin_email' );
        $default_options['new_donation_notification_to']      = get_bloginfo( 'admin_email' );
        $default_options['uninstall_deletes_data']            = false;
        $default_options['donation_receipt_subject']          = __('Thank you for your donation', 'peerraiser');
        $default_options['donation_receipt_body']             = __('Dear [peerraiser_email show=donor_first_name],

            Thank you so much for your generous donation.

            Transaction Summary
            [peerraiser_email show=donation_summary]

            With thanks,
            [peerraiser_email show=site_name]', 'peerraiser');
        $default_options['new_donation_notification_subject'] = __('New donation received', 'peerraiser');
        $default_options['new_donation_notification_body']    = __('[peerraiser_email show=donor] has just made a donation!

            Summary
            [peerraiser_email show=donation_summary]', 'peerraiser');
        $default_options['welcome_email_subject']             = __('Welcome!', 'peerraiser');
        $default_options['welcome_email_body']                = __('Welcome to the [peerraiser_email show=campaign_name] campaign!', 'peerraiser');

        // Create default pages
        if ( ! isset( $plugin_options['thank_you_page'] ) ) {
            $thank_you_page = $this->create_page( 'thank_you' );
            $default_options[ 'thank_you_page' ] = $thank_you_page;
        }

        if ( ! isset( $plugin_options['login_page'] ) ) {
            $login_page = $this->create_page( 'login' );
            $default_options[ 'login_page' ] = $login_page;
        }

        if ( ! isset( $plugin_options['signup_page'] ) ) {
            $signup_page = $this->create_page( 'signup' );
            $default_options[ 'signup_page' ] = $signup_page;
        }

        if ( ! isset( $plugin_options['participant_dashboard'] ) ) {
            $participant_dashboard = $this->create_page( 'participant_dashboard' );
            $default_options[ 'participant_dashboard' ] = $participant_dashboard;
        }

        // Upload default thumbnail images to the media library
        if ( ! isset( $plugin_options['campaign_thumbnail_image'] ) ) {
            $campaign_image_id = \PeerRaiser\Helper\View::add_file_to_media_library( 'default-campaign-thumbnail.png' );
            $default_options[ 'campaign_thumbnail_image' ] = $campaign_image_id;
        }

        if ( ! isset( $plugin_options['user_thumbnail_image'] ) ) {
            $user_images_id = \PeerRaiser\Helper\View::add_file_to_media_library( 'default-user-thumbnail.png' );
            $default_options[ 'user_thumbnail_image' ] = $user_images_id;
        }

        if ( ! isset( $plugin_options['team_thumbnail_image'] ) ) {
            $team_images_id    = \PeerRaiser\Helper\View::add_file_to_media_library( 'default-team-thumbnail.png' );
            $default_options[ 'team_thumbnail_image' ] = $team_images_id;
        }

        // Add to activity feed
        $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : 0;
        if ( version_compare( $current_version, $this->config->version, '!=' ) ) {
            $model = new \PeerRaiser\Model\Activity_Feed();
            $model->add_install_notice_to_feed( $this->config->version );
        }

        // keep the plugin version up to date
        $plugin_options['peerraiser_version'] = $this->config->get( 'version' );

        update_option( 'peerraiser_options', wp_parse_args( $plugin_options, $default_options ) );

        // Create Databases
        $donation_database = new \PeerRaiser\Model\Donation();
        if ( ! $donation_database->table_exists() ) {
            $donation_database->create_table();
        }

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

    private function create_page( $page ) {
        $page_options = $this->default_pages[$page];

        $page_id = wp_insert_post(
            array(
                'post_title'     => $page_options['post_title'],
                'post_content'   => $page_options['post_content'],
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );

        return $page_id;
    }

}
