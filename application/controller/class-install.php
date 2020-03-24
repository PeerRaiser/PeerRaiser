<?php
namespace PeerRaiser\Controller;

use PeerRaiser\Model\Database\Donation_Table;

/**
 * PeerRaiser installation controller.
 */
class Install extends Base {

    // TODO: Move this to the model
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
            'register' => array(
                'post_title'     => __( 'Register', 'peerraiser' ),
                'post_content'   => __( '[peerraiser_registration]', 'peerraiser' )
            ),
            'participant_dashboard' => array(
                'post_title'     => __( 'Participant Dashboard', 'peerraiser' ),
                'post_content'   => __( '[peerraiser_participant_dashboard]', 'peerraiser' )
            ),
            'donate' => array(
                'post_title'     => __( 'Donate', 'peerraiser' ),
                'post_content'   => __( '[peerraiser_donation_form]', 'peerraiser' )
            ),
        );
        parent::__construct();
    }

     public function register_actions() {
        add_action( 'admin_init',                     array( $this, 'trigger_requirements_check' ) );
        add_action( 'admin_init',                     array( $this, 'trigger_update_capabilities' ) );
        add_action( 'peerraiser_update_capabilities', array( $this, 'update_capabilities' ) );
        add_action( 'peerraiser_check_requirements',  array( $this, 'check_requirements' ) );
        add_action( 'admin_notices',                  array( $this, 'render_requirements_notices' ) );
        add_action( 'admin_notices',                  array( $this, 'check_for_updates' ) );
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
     */
    public function trigger_requirements_check() {
        do_action( 'peerraiser_check_requirements' );
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
        $required_php_version           = '5.3.0';
        $required_wp_version            = '4.4.0';
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
            deactivate_plugins( $this->config->get( 'plugin_base_name' ) );
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
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

        if ( version_compare( $current_version, $this->config->get( 'version' ), '!=' ) ) {
            $this->install();
        }
    }

    /**
     * Create custom tables and set the required options.
     *
     * This function is called if this plugin version is ever different than the installed plugin version
     *
     * @return void
     */
    public function install() {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

        // If the installed version and this version of the same, do nothing
        if ( version_compare( $current_version, $this->config->get( 'version' ), '==' ) ) {
            return;
        }

        // cancel the installation process, if the requirements check returns errors
        $notices = (array) $this->check_requirements();
        if ( count( $notices ) ) {
            return;
        }

        // Set the default options
        $this->set_default_options();

        // Create default pages if needed
        $this->maybe_create_default_pages();

        // Upload default thumbnail images if needed
        $this->maybe_upload_default_images();

        // Created databases if needed
        $this->maybe_create_databases();

        // Populate default roles
        $this->populate_roles();

        // Perform database updates
        $this->maybe_update_donation_table();
        $this->maybe_update_donor_table();

        // Update the email templates
	    $this->maybe_update_email_templates();

        // keep the plugin version up to date
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $plugin_options['peerraiser_version'] = $this->config->get( 'version' );
        update_option( 'peerraiser_options', $plugin_options );

        // Add install/update notice to activity feed
        $model = new \PeerRaiser\Model\Activity_Feed();
        $model->add_install_notice_to_feed( $this->config->get( 'version' ) );

        // clear opcode cache
        \PeerRaiser\Helper\Cache::reset_opcode_cache();
    }

    /**
     * Trigger requirements check.
     */
    public function trigger_update_capabilities() {
        do_action( 'peerraiser_update_capabilities' );
    }


    /**
     * Update user roles capabilities.
     */
    public function update_capabilities( $roles ) {
        // update capabilities
        $peerraiser_capabilities = new \PeerRaiser\Core\Capability();
        $peerraiser_capabilities->update_roles( (array) $roles );
    }

    /**
     * Merge default options with whatever options are currently set by the user
     *
     * @since    1.0.0
     */
    private function set_default_options() {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $default_options = array();

        $default_options['currency']                          = $this->config->get( 'currency.default' );
        $default_options['currency_position']                 = $this->config->get( 'currency.position');
        $default_options['thousands_separator']               = $this->config->get( 'currency.thousands_separator' );
        $default_options['decimal_separator']                 = $this->config->get( 'currency.decimal_separator' );
        $default_options['number_decimals']                   = $this->config->get( 'currency.number_decimals' );
        $default_options['donation_minimum']                  = $this->config->get( 'donation_minimum' );
        $default_options['fundraiser_slug']                   = 'give';
        $default_options['campaign_slug']                     = 'campaign';
        $default_options['team_slug']                         = 'team';
        $default_options['disable_css_styles']                = false;
        $default_options['test_mode']                         = true;
        $default_options['show_welcome_message']              = true;
        $default_options['donation_receipt_enabled']          = true;
        $default_options['new_donation_notification_enabled'] = true;
	    $default_options['welcome_email_enabled']             = true;
	    $default_options['team_registration_email_enabled']   = true;
	    $default_options['from_name']                         = get_bloginfo( 'name' );
        $default_options['from_email']                        = get_bloginfo( 'admin_email' );
        $default_options['tax_id']                            = '';
        $default_options['new_donation_notification_to']      = get_bloginfo( 'admin_email' );
        $default_options['uninstall_deletes_data']            = false;
        $default_options['donation_receipt_subject']          = $this->config->get( 'donation_receipt_subject' );
        $default_options['donation_receipt_body']             = $this->config->get( 'donation_receipt_body' );
        $default_options['new_donation_notification_subject'] = $this->config->get( 'new_donation_notification_subject' );
	    $default_options['new_donation_notification_body']    = $this->config->get( 'new_donation_notification_body' );
		$default_options['welcome_email_subject']             = $this->config->get( 'welcome_email_subject' );
		$default_options['welcome_email_body']                = $this->config->get( 'welcome_email_body' );

        update_option( 'peerraiser_options', wp_parse_args( $plugin_options, $default_options ) );
    }

    /**
     * Create default pages if current version is not 1.0.0 or better
     *
     * PeerRaiser needs a few default pages setup to work out of the box. The user can change these later.
     *
     * @since     1.0.0
     * @return    void
     */
    private function maybe_create_default_pages() {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

        // If current version is 1.0.0+, we know pages were already created
        if ( version_compare( $current_version, '1.0.0', '>=' ) )
            return;

        // Create default pages
        $thank_you_page        = $this->create_page( 'thank_you' );
        $login_page            = $this->create_page( 'login' );
        $signup_page           = $this->create_page( 'signup' );
        $registration_page     = $this->create_page( 'register' );
        $participant_dashboard = $this->create_page( 'participant_dashboard' );
        $donation_page         = $this->create_page( 'donate' );

        $default_options = array(
            'thank_you_page'        => $thank_you_page,
            'login_page'            => $login_page,
            'signup_page'           => $signup_page,
            'registration_page'     => $registration_page,
            'participant_dashboard' => $participant_dashboard,
            'donation_page'         => $donation_page,
        );

        update_option( 'peerraiser_options', wp_parse_args( $plugin_options, $default_options ) );
    }

    /**
     * Create default images if current version is not 1.0.0 or better
     *
     * @since     1.0.0
     * @return    void
     */
    private function maybe_upload_default_images() {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

        // If current version is 1.0.0+, we know images were already created
        if ( version_compare( $current_version, '1.0.0', '>=' ) )
            return;

        $campaign_image_id = \PeerRaiser\Helper\View::add_file_to_media_library( 'default-campaign-thumbnail.png' );
        $team_images_id    = \PeerRaiser\Helper\View::add_file_to_media_library( 'default-team-thumbnail.png' );
        $user_images_id    = \PeerRaiser\Helper\View::add_file_to_media_library( 'default-user-thumbnail.png' );

        $default_options = array(
            'campaign_thumbnail_image' => $campaign_image_id,
            'user_thumbnail_image'     => $user_images_id,
            'team_thumbnail_image'     => $team_images_id,
        );

        update_option( 'peerraiser_options', wp_parse_args( $plugin_options, $default_options ) );
    }

    /**
     * Create database tables, if they don't exist
     *
     * @since     1.0.0
     * @return    void
     */
    private function maybe_create_databases() {
        // Donation
        $donation_database = new \PeerRaiser\Model\Database\Donation_Table();
        if ( ! $donation_database->table_exists() ) {
            $donation_database->create_table();
        }

        // Donation Meta
        $donation_meta_database = new \PeerRaiser\Model\Database\Donation_Meta_Table();
        if ( ! $donation_meta_database->table_exists() ) {
            $donation_meta_database->create_table();
        }

        // Donor
        $donor_database = new \PeerRaiser\Model\Database\Donor_Table();
        if ( ! $donor_database->table_exists() ) {
            $donor_database->create_table();
        }

        // Donor Meta
        $donor_meta_database = new \PeerRaiser\Model\Database\Donor_Meta_Table();
        if ( ! $donor_meta_database->table_exists() ) {
            $donor_meta_database->create_table();
        }
    }

    /**
     * Populate roles
     *
     * @since     1.0.0
     * @return    void
     */
    private function populate_roles() {
        $peerraiser_capabilities = new \PeerRaiser\Core\Capability();
        $peerraiser_capabilities->populate_roles();
    }

    /**
     * Create pages
     *
     * @since     1.0.0
     * @param     string    $page    The page slug to create
     * @return    int                ID of the page that was created
     */
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

    /**
     * Maybe update the donation table
     *
     * In version 1.1.0 PeerRaiser added a participant_id column. If the current version is less than 1.1.0, this
     * function will add that column.
     *
     * @since 1.1.0
     */
    private function maybe_update_donation_table() {
        global $wpdb;

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

        if ( $current_version === '0' ) {
            return;
        }

        // In version 1.1.0, we added the participant_id column
        if ( version_compare( $current_version, '1.1.0', '<' ) ) {
	        $table   = $wpdb->prefix . 'pr_donations';
	        $columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table . ';' );

	        $is_participant_id_present = false;

	        foreach ( $columns as $column ) {
		        if ( $column->Field === 'participant_id' ) {
			        $is_participant_id_present = true;
		        }
	        }

	        // If need to add participant_id field
	        if ( ! $is_participant_id_present ) {
		        $wpdb->query( 'ALTER TABLE ' . $table . ' ADD `participant_id` bigint(20) NOT NULL DEFAULT 0 AFTER `team_id`;' );
	        }
        }

    }

    private function maybe_update_donor_table() {
	    global $wpdb;

	    $plugin_options  = get_option( 'peerraiser_options', array() );
	    $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

	    if ( $current_version === '0' ) {
		    return;
	    }

	    // In version 1.2.0 we added test_donation_value and test_donation_count columns to track test donations
	    if ( version_compare( $current_version, '1.2.0', '<' ) ) {
		    $table   = $wpdb->prefix . 'pr_donors';
		    $columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table . ';' );

		    $is_test_donation_value_present = false;
		    $is_test_donation_count_present = false;

		    foreach ( $columns as $column ) {
			    if ( $column->Field === 'test_donation_value' ) {
				    $is_test_donation_value_present = true;
			    } elseif ( $column->Field === 'test_donation_count' ) {
				    $is_test_donation_value_present = true;
			    }
		    }

		    // If need to add test_donation_value field
		    if ( ! $is_test_donation_value_present ) {
			    $wpdb->query( 'ALTER TABLE ' . $table . ' ADD `test_donation_value` decimal(13,4) NOT NULL DEFAULT 0.00 AFTER `donation_count`;' );
		    }

		    // If need to add test_donation_count field
		    if ( ! $is_test_donation_count_present ) {
			    $wpdb->query( 'ALTER TABLE ' . $table . ' ADD `test_donation_count` bigint(20) NOT NULL DEFAULT 0 AFTER `test_donation_value`;' );
		    }
	    }
    }

	/**
	 * Maybe update the email templates
	 *
	 * In version 1.2.0 PeerRaiser changed the way email templates work. If the current version is less than 1.2.0, this
	 * function will update the templates to the new version.
	 *
	 */
    private function maybe_update_email_templates() {
	    $plugin_options  = get_option( 'peerraiser_options', array() );
	    $current_version = ( isset( $plugin_options['peerraiser_version'] ) ) ? $plugin_options['peerraiser_version'] : '0';

	    // If current version is not less than 1.2.0, do nothing
	    if ( $current_version === '0' || version_compare( $current_version, '1.2.0', '>=' ) ) {
		    return;
	    }

	    $plugin_options = get_option( 'peerraiser_options', array() );

	    $default_options['donation_receipt_subject']          = $this->config->get( 'donation_receipt_subject' );
	    $default_options['donation_receipt_body']             = $this->config->get( 'donation_receipt_body' );
	    $default_options['new_donation_notification_subject'] = $this->config->get( 'new_donation_notification_subject' );
	    $default_options['new_donation_notification_body']    = $this->config->get( 'new_donation_notification_body' );
	    $default_options['welcome_email_subject']             = $this->config->get( 'welcome_email_subject' );
	    $default_options['welcome_email_body']                = $this->config->get( 'welcome_email_body' );

	    update_option( 'peerraiser_options', wp_parse_args( $plugin_options, $default_options ) );
    }

}
