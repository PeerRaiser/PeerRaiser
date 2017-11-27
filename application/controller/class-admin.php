<?php

namespace PeerRaiser\Controller;

use \PeerRaiser\Helper\View;
use \PeerRaiser\Core\Setup;
use \PeerRaiser\Model\Admin\Admin_Notices;
use \PeerRaiser\Helper\Field;
use \PeerRaiser\Helper\Text;
use \PeerRaiser\Controller\Admin as AdminController;
use \PeerRaiser\Controller\Admin\Dashboard as DashboardController;
use \PeerRaiser\Controller\Admin\Campaigns as CampaignsController;
use \PeerRaiser\Controller\Admin\Teams as TeamsController;
use \PeerRaiser\Controller\Admin\Donations as DonationsController;
use \PeerRaiser\Controller\Admin\Donors as DonorsController;
use \PeerRaiser\Controller\Admin\Participants as ParticipantsController;
use \PeerRaiser\Controller\Admin\Settings as SettingsController;

/**
 * PeerRaiser admin controller.
 */
class Admin extends Base {

    public function register_actions() {
        add_action( 'cmb2_init',                        array( $this, 'handle_peerraiser_actions' ), 99 );
        add_action( 'admin_menu',                       array( $this, 'add_to_admin_panel' ) );
	    add_action( 'current_screen',                   array( $this, 'maybe_display_test_mode_reminder' ) );
	    add_action( 'admin_head',                       array( $this, 'on_campaigns_view' ) );
        add_action( 'admin_print_footer_scripts',       array( $this, 'modify_footer' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'add_plugin_admin_assets' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'add_admin_pointers_script' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'register_admin_scripts' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'register_admin_styles' ) );
        add_action( 'wp_ajax_peerraiser_get_posts',     array( $this, 'ajax_get_posts' ) );
        add_action( 'wp_ajax_peerraiser_get_donors',    array( $this, 'ajax_get_donors' ) );
        add_action( 'wp_ajax_peerraiser_get_campaigns', array( $this, 'ajax_get_campaigns' ) );
        add_action( 'wp_ajax_peerraiser_get_teams',     array( $this, 'ajax_get_teams' ) );
        add_action( 'wp_ajax_peerraiser_get_users',     array( $this, 'ajax_get_users' ) );
        add_action( 'wp_ajax_peerraiser_get_slug',      array( $this, 'ajax_get_slug' ) );

        add_filter( 'enter_title_here',           array( $this, 'customize_title' ), 1 );
        add_filter( 'manage_users_columns',       array( $this, 'add_peerraiser_group_column' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'manage_peerraiser_group_column'), 10, 3 );
    }

    public function handle_peerraiser_actions() {
        if ( isset( $_REQUEST['peerraiser_action'] ) ) {
            do_action( 'peerraiser_' . $_REQUEST['peerraiser_action'], $_REQUEST );
        }
    }

    /**
     * Show plugin in administrator panel.
     *
     * @return void
     */
    public function add_to_admin_panel() {
        add_menu_page(
            __( 'PeerRaiser', 'peerraiser' ),
            'PeerRaiser',
            'moderate_comments', // allow Super Admin, Admin, and Editor to view the settings page
            'peerraiser-dashboard',
            array( $this, 'run' ),
            'dashicons-peerraiser-logo',
            81
        );

        $model = new \PeerRaiser\Model\Admin\Admin();
        $menu_items = $model->get_menu_items();

        foreach ( $menu_items as $name => $page ) {
            $slug = $page['url'];

            if ( strpos($slug, 'post_type') === false ) {
                $page_id = add_submenu_page(
                    'peerraiser-dashboard',
                    $page['title'] . ' | ' . __( 'PeerRaiser', 'peerraiser' ),
                    $page['title'],
                    $page['cap'],
                    $slug,
                    isset( $page['run'] ) ? $page['run'] : array( $this, 'run_' . $name )
                );
            } else {
                $page_id = add_submenu_page(
                    'peerraiser-dashboard',
                    $page['title'] . ' | ' . __( 'PeerRaiser', 'peerraiser' ),
                    $page['title'],
                    $page['cap'],
                    $slug,
                    null
                );
            }

            do_action( 'load-' . $page_id, 'peerraiser_load_' . $page_id );

            $help_action = isset( $page['help'] ) ? $page['help'] : array( $this, 'help_' . $name );
            do_action( 'peerraiser_load_' . $page_id, $help_action );
        }
    }

    public function maybe_display_test_mode_reminder() {
	    $current_screen = get_current_screen();

	    // Skip if the current screen isn't for this plugin, or it isn't in test mode
	    if ( is_null( $current_screen ) ||
	         ( strpos( $current_screen->base, 'peerraiser' ) === false && $current_screen->post_type !== 'fundraiser' ) ||
	         ! peerraiser_is_test_mode()
	    ) {
		    return;
	    }

	    // Don't show on the settings page
	    if ( strpos( $current_screen->base, 'peerraiser-settings' ) !== false ) {
	    	return;
	    }

	    $admin_notice_model = new Admin_Notices();

	    $notice = $admin_notice_model->get_notice_message('test_mode_active_reminder');
	    $admin_notice_model->add_notice( $notice['message'], $notice['class'], $notice['dismissible'] );
    }

    /**
     *
     * @param string $name
     * @param mixed  $args
     *
     * @return mixed
     */
    public function __call( $name, $args ) {
        if ( substr( $name, 0, 4 ) == 'run_' ) {
            return $this->run( strtolower( substr( $name, 4 ) ) );
        } elseif ( substr( $name, 0, 5 ) == 'help_' ) {
            return $this->help( strtolower( substr( $name, 5 ) ) );
        }
    }

    /**
     * @see \PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load PeerRaiser-specific CSS
        wp_register_style(
            'peerraiser-admin',
            Setup::get_plugin_config()->get( 'css_url' ) . 'peerraiser-admin.css',
            array(),
            Setup::get_plugin_config()->get( 'version' )
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext'
        );
        wp_register_style(
            'peerraiser-select2',
            Setup::get_plugin_config()->get( 'css_url' ) . 'vendor/select2.min.css',
            array(),
            Setup::get_plugin_config()->get( 'version' )
        );
        wp_enqueue_style( 'open-sans' );
        wp_enqueue_style( 'peerraiser-select2' );
        wp_enqueue_style( 'peerraiser-admin' );

        // load PeerRaiser-specific JS
        wp_enqueue_script( 'peerraiser-admin' );

    }

    /**
     * Constructor for class PeerRaiserController, processes the pages in the plugin backend.
     *
     * @param string $page
     *
     * @return void
     */
    public function run( $page = '' ) {
        $this->load_assets();

        // return default page, if no specific page is requested
        if ( empty( $page ) ) {
            $page = 'dashboard';
        }

        switch ( $page ) {
            default:
            case 'dashboard' :
                $dashboard_controller = new DashboardController( Setup::get_plugin_config() );
                $dashboard_controller->render_page();
                break;
            case 'campaigns' :
                $campaigns_controller = new CampaignsController( Setup::get_plugin_config() );
                $campaigns_controller->render_page();
                break;
            case 'teams' :
                $teams_controller = new TeamsController( Setup::get_plugin_config() );
                $teams_controller->render_page();
                break;
            case 'donations' :
                $donations_controller = new DonationsController( Setup::get_plugin_config() );
                $donations_controller->render_page();
                break;
            case 'donors' :
                $donors_controller = new DonorsController( Setup::get_plugin_config() );
                $donors_controller->render_page();
                break;
            case 'participants' :
                $participants_controller = new ParticipantsController( Setup::get_plugin_config() );
                $participants_controller->render_page();
                break;
            case 'settings' :
                $settings_controller = new SettingsController( Setup::get_plugin_config() );
                $settings_controller->render_page();
                break;
        }
    }

    /**
     * Add WordPress pointers to pages.
     *
     * @param
     * @return string
     */
    public function modify_footer() {
        $pointers = AdminController::get_pointers_to_be_shown();

        // don't render the partial, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        // assign pointers
        $view_args = array(
            'pointers' => $pointers,
        );

        $this->assign( 'peerraiser', $view_args );
        $result = $this->get_text_view( 'backend/partials/pointer-scripts' );
        return $result;
    }

    /**
     * Load PeerRaiser stylesheet with PeerRaiser vector logo on all pages where the admin menu is visible.
     *
     * @return void
     */
    public function add_plugin_admin_assets() {
        wp_register_style(
            'peerraiser-admin',
            Setup::get_plugin_config()->css_url . 'peerraiser-admin.css',
            array(),
            Setup::get_plugin_config()->version
        );
        wp_enqueue_style( 'peerraiser-admin' );
    }

    /**
     * Hint at the newly installed plugin using WordPress pointers.
     *
     * @return void
     */
    public function add_admin_pointers_script() {
        $pointers = AdminController::get_pointers_to_be_shown();

        // don't enqueue the assets, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }

    /**
     * Return all pointer constants from current class.
     *
     * @return array $pointers
     */
    public static function get_all_pointers() {
        $reflection         = new \ReflectionClass( __CLASS__ );
        $class_constants    = $reflection->getConstants();
        $pointers           = array();

        if ( $class_constants ) {
            foreach ( array_keys( $class_constants ) as $key_value ) {
                if ( strpos( $key_value, 'POINTER' ) !== false ) {
                    $pointers[] = $class_constants[ $key_value ];
                }
            }
        }

        return $pointers;
    }

    /**
     * Registers the main admin script so it can be enqueued on the other PeerRaiser pages
     *
     * @since     1.0.0
     * @return    null
     */
    public function register_admin_scripts() {
        wp_register_script(
            'validate',
            Setup::get_plugin_config()->get('js_url') . 'vendor/validate/jquery.validate.min.js',
            array( 'jquery' ),
            '1.16.0',
            true
        );
        wp_register_script(
            'validate-additional-methods',
            Setup::get_plugin_config()->get('js_url') . 'vendor/validate/additional-methods.min.js',
            array( 'jquery', 'validate' ),
            '1.16.0',
            true
        );
        wp_register_script(
            'peerraiser-admin',
            Setup::get_plugin_config()->get( 'js_url' ) . 'peerraiser-admin.js',
            array( 'jquery', 'peerraiser-select2', 'validate', 'validate-additional-methods' ),
            Setup::get_plugin_config()->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-select2',
            Setup::get_plugin_config()->get('js_url') . 'vendor/select2.min.js',
            array( 'jquery' ),
            '4.0.2',
            true
        );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin',
            'peerraiser_admin_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
                'i10n' => array(
                    'edit'           => __('Edit', 'peerraiser'),
                    'ok'             => __('OK', 'peerraiser'),
                    'cancel'         => __('Cancel'),
                    'confirm_delete' => __('Are you sure you want to delete this? This cannot be undone.', 'peerraiser' )
                )
            )
        );
    }

    public function register_admin_styles() {
        wp_register_style(
            'peerraiser-select2',
            Setup::get_plugin_config()->get('css_url') . 'vendor/select2.min.css',
            array(),
            '4.0.2'
        );
        wp_register_style(
            'peerraiser-font-awesome',
            'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css',
            array(),
            '4.5.0'
        );
    }

    public function on_campaigns_view() {
        $current_screen = get_current_screen();
        $campaigns_count = wp_count_posts( 'pr_campaign' );
        if ( $current_screen->id == 'edit-pr_campaign' && $campaigns_count->publish == 0) {
            $admin_notices = Admin_Notices::get_instance();
            $message = __( 'Create your first campaign to get started. <a href="/admin.php?page=peerraiser-campaigns&view=add">Create Campaign</a>' , 'peerraiser' );
            $admin_notices::add_notice( $message );
        }
    }

    /**
     * Customize the "Enter title here" placeholder in the Title field based on post type
     *
     * @since 1.0.0
     * @param $title
     *
     * @return string
     */
    public function customize_title( $title ) {
        $current_screen = get_current_screen();

        switch ($current_screen->post_type) {
            case 'fundraiser':
                if ( $title === 'fundraiser' ) {
                    $title = 'fundraiser';
                } else {
                    $title = __( 'Enter fundraiser name here', 'peerraiser' );
                }
                break;

            default:
                break;
        }

        return $title;
    }

    /**
     * Add a 'PeerRaiser Group' column to the Users list table
     *
     * @param $column
     *
     * @return mixed
     */
    public function add_peerraiser_group_column( $column ) {
        $column['peerraiser_group'] = __( 'PeerRaiser Group', 'peerraiser' );

        return $column;
    }

    /**
     * Manage the display output of 'PeerRaiser Group' columns in the Users list table.
     *
     * @param string $output      Custom column output. Default empty.
     * @param string $column_name Column name.
     * @param int    $user_id     ID of the currently-listed user.
     *
     * @return string
     */
    public function manage_peerraiser_group_column( $output, $column_name, $user_id ) {
        switch ($column_name) {
            case 'peerraiser_group' :
                $peerraiser_groups = wp_get_object_terms( $user_id, array( 'peerraiser_group' ), array( 'fields' => 'names' ) );
                return implode( ', ', $peerraiser_groups );
                break;
            default:
        }
        return $output;
    }

    /**
     * Retrieve posts and creates <option> for select lists
     *
     * @since     1.0.0
     *
     * @return    array                              Data formatted for select2
     */
    public function ajax_get_posts() {
        $choices = Field::get_post_choices( $_POST );

        echo Text::peerraiser_json_encode( $choices );

        wp_die();
    }

    /**
     * Retrieves donors and creates <option> for select lists
     *
     * @since     1.0.0
     *
     * @return    array Data formatted for select2
     */
    public function ajax_get_donors() {
        $choices = Field::get_donor_choices( $_POST );

        echo Text::peerraiser_json_encode( $choices );

        wp_die();
    }

    /**
     * Retrieves campaigns and creates <option> for select lists
     *
     * @since     1.0.0
     *
     * @return    array Data formatted for select2
     */
    public function ajax_get_campaigns() {
        $choices = Field::get_campaign_choices( $_POST );

        echo Text::peerraiser_json_encode( $choices );

        wp_die();
    }

    /**
     * Retrieves campaigns and creates <option> for select lists
     *
     * @since     1.0.0
     *
     * @return    array Data formatted for select2
     */
    public function ajax_get_teams() {
        $choices = Field::get_team_choices( $_POST );

        echo Text::peerraiser_json_encode( $choices );

        wp_die();
    }

    public function ajax_get_users() {
        $count_args  = array(
            'number'    => 999999
        );
        if ( isset($_POST['q'] ) ){
            $count_args['search'] = '*'.sanitize_text_field($_POST['q']).'*';
            $count_args['search_columns'] = array( 'display_name', 'user_email' );
        }

        $user_count_query = new \WP_User_Query($count_args);
        $user_count = $user_count_query->get_results();

        // count the number of users found in the query
        $total_users = $user_count ? count($user_count) : 1;

        // grab the current page number and set to 1 if no page number is set
        $page = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;

        // how many users to show per page
        $users_per_page = 10;

        // calculate the total number of pages.
        $total_pages = 1;
        $offset = $users_per_page * ($page - 1);
        $total_pages = ceil($total_users / $users_per_page);

        // main user query
        $args  = array(
            // order results by display_name
            'orderby'   => 'display_name',
            'fields'    => 'all_with_meta',
            'number'    => $users_per_page,
            'offset'    => $offset
        );

        if ( isset( $_POST['peerraiser_group'] ) && $_POST['peerraiser_group'] === 'participants' ) {
            $participant_term = get_term_by( 'slug', 'participant', 'peerraiser_group' );
            $participant_ids  = get_objects_in_term( $participant_term->term_id, 'peerraiser_group' );

            if ( ! empty( $participant_ids ) ) {
                $args['include'] = $participant_ids;
            }
        }

        if ( isset($_POST['q'] ) ){
            $args['search'] = '*'.sanitize_text_field($_POST['q']).'*';
            $args['search_columns'] = array( 'display_name', 'user_email' );
        }

        $user_query = new \WP_User_Query( $args );

        // empty array to fill with data
        $data = array();

        // User Loop
        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) {
                $line = array(
                    'id'   => $user->ID,
                    'text' => $user->display_name
                );
                array_push($data, $line);
            }
        }

        echo Text::peerraiser_json_encode( array(
            'items' => $data ,
            'total_count' => $total_users
        ) );

        wp_die();
    }

    public function ajax_get_slug() {
        // Remove special characters
        $sanitized = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities( wp_strip_all_tags( $_POST['new_slug'] ) ) );

        // Replace whitespaces with dashes.
        $sanitized = sanitize_title_with_dashes( $sanitized, null, 'save' );

        switch ( $_POST['object_type'] ) {
            case 'campaign' :
                $term = get_term_by( 'slug', $_POST['new_slug'], 'peerraiser_campaign' );
                if ( $term && $term->term_id !== (int) $_POST['object_id'] ) {
                    $_POST['new_slug'] = $this->increment_slug( $sanitized );
                    $this->ajax_get_slug();
                } else {
                    $new_slug = $sanitized;
                }
                break;
            case 'team' :
                $term = get_term_by( 'slug', $_POST['new_slug'], 'peerraiser_team' );
                if ( $term && $term->term_id !== (int) $_POST['object_id'] ) {
                    $_POST['new_slug'] = $this->increment_slug( $sanitized );
                    $this->ajax_get_slug();
                } else {
                    $new_slug = $sanitized;
                }
                break;
            default :
                break;
        }

        if ( mb_strlen( $new_slug ) > 34 ) {
            $new_slug_abridged = mb_substr( $new_slug, 0, 16 ) . '&hellip;' . mb_substr( $new_slug, -16 );
        } else {
            $new_slug_abridged = $new_slug;
        }

        echo Text::peerraiser_json_encode( array(
            'new_slug' => $new_slug,
            'slug_abridged' => $new_slug_abridged,
        ) );

        wp_die();
    }

    function increment_slug( $slug ) {
        preg_match("/(.*?)-(\d+)$/", $slug, $matches );

        if ( isset( $matches[2] ) ) {
            $new_slug = $matches[1] . '-' . ( intval($matches[2]) + 1 );
        } else {
            $new_slug = $slug . '-2';
        }

        return $new_slug;
    }
}
