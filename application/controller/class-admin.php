<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser admin controller.
 */
class Admin extends Base {

    public function register_actions() {
        add_action( 'admin_menu',                   array( $this, 'add_to_admin_panel' ) );
        add_action( 'admin_head',                   array( $this, 'on_campaigns_view' ) );
        add_action( 'admin_print_footer_scripts',   array( $this, 'modify_footer' ) );
        add_action( 'admin_enqueue_scripts',        array( $this, 'add_plugin_admin_assets' ) );
        add_action( 'admin_enqueue_scripts',        array( $this, 'add_admin_pointers_script' ) );
        add_action( 'admin_enqueue_scripts',        array( $this, 'register_admin_scripts' ) );
        add_action( 'admin_enqueue_scripts',        array( $this, 'register_admin_styles' ) );
        add_action( 'wp_ajax_peerraiser_get_posts', array( $this, 'ajax_get_posts' ) );
        add_action( 'wp_ajax_peerraiser_get_posts', array( $this, 'peerraiser_on_ajax_send_json' ) );
        add_action( 'wp_ajax_peerraiser_get_users', array( $this, 'ajax_get_users' ) );
        add_action( 'wp_ajax_peerraiser_get_users', array( $this, 'peerraiser_on_ajax_send_json' ) );

        add_filter( 'enter_title_here', array( $this, 'customize_title' ), 1 );
    }

    /**
     * Show plugin in administrator panel.
     *
     * @return void
     */
    public function add_to_admin_panel() {
        $plugin_page = \PeerRaiser\Helper\View::$pluginPage;
        add_menu_page(
            __( 'PeerRaiser', 'peerraiser' ),
            'PeerRaiser',
            'moderate_comments', // allow Super Admin, Admin, and Editor to view the settings page
            $plugin_page,
            array( $this, 'run' ),
            'dashicons-peerraiser-logo',
            81
        );

        $model = new \PeerRaiser\Model\Admin();
        $menu_items = $model->get_menu_items();

        foreach ( $menu_items as $name => $page ) {
            $slug = $page['url'];

            if ( strpos($slug, 'post_type') === false ) {
                $page_id = add_submenu_page(
                    $plugin_page,
                    $page['title'] . ' | ' . __( 'PeerRaiser', 'peerraiser' ),
                    $page['title'],
                    $page['cap'],
                    $slug,
                    isset( $page['run'] ) ? $page['run'] : array( $this, 'run_' . $name )
                );
            } else {
                $page_id = add_submenu_page(
                    $plugin_page,
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


    /**
     *
     * @param string $name
     * @param mixed  $args
     *
     * @return void
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
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'css_url' ) . 'peerraiser-admin.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' )
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext'
        );
        wp_register_style(
            'peerraiser-select2',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'css_url' ) . 'vendor/select2.min.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' )
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
                $dashboard_controller = new \PeerRaiser\Controller\Admin\Dashboard( \PeerRaiser\Core\Setup::get_plugin_config() );
                $dashboard_controller->render_page();
                break;
            case 'campaigns' :
                $campaigns_controller = new \PeerRaiser\Controller\Admin\Campaigns( \PeerRaiser\Core\Setup::get_plugin_config() );
                $campaigns_controller->render_page();
                break;
            case 'teams' :
                $teams_controller = new \PeerRaiser\Controller\Admin\Teams( \PeerRaiser\Core\Setup::get_plugin_config() );
                $teams_controller->render_page();
                break;
            case 'donations' :
                $donations_controller = new \PeerRaiser\Controller\Admin\Donations( \PeerRaiser\Core\Setup::get_plugin_config() );
                $donations_controller->render_page();
                break;
            case 'donors' :
                $donors_controller = new \PeerRaiser\Controller\Admin\Donors( \PeerRaiser\Core\Setup::get_plugin_config() );
                $donors_controller->render_page();
                break;
            case 'settings' :
                $settings_controller = new \PeerRaiser\Controller\Admin\Settings( \PeerRaiser\Core\Setup::get_plugin_config() );
                $settings_controller->render_page();
                break;
        }
    }

    /**
     * Render contextual help, depending on the current page.
     *
     * @param string $tab
     *
     * @return void
     */
    public function help( $tab = '' ) {
        switch ( $tab ) {
            case 'wp_edit_post':
            case 'wp_add_post':
                $this->render_add_edit_post_page_help();
                break;

            case 'dashboard':
                $this->render_dashboard_tab_help();
                break;

            // case 'appearance':
            //     $this->render_appearance_tab_help();
            //     break;

            default:
                break;
        }
    }

    /**
     * Add WordPress pointers to pages.
     *
     * @param
     * @return void
     */
    public function modify_footer() {
        $pointers = \PeerRaiser\Controller\Admin::get_pointers_to_be_shown();

        // don't render the partial, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        // assign pointers
        $view_args = array(
            'pointers' => $pointers,
        );

        $this->assign( 'peerraiser', $view_args );
        $result = $event->get_result();
        $result .= $this->get_text_view( 'backend/partials/pointer-scripts' );
        $event->set_result( $result );
    }


    /**
     * Load PeerRaiser stylesheet with PeerRaiser vector logo on all pages where the admin menu is visible.
     *
     * @return void
     */
    public function add_plugin_admin_assets() {
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->css_url . 'peerraiser-admin.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->version
        );
        wp_enqueue_style( 'peerraiser-admin' );
    }


    /**
     * Hint at the newly installed plugin using WordPress pointers.
     *
     * @return void
     */
    public function add_admin_pointers_script() {
        $pointers = \PeerRaiser\Controller\Admin::get_pointers_to_be_shown();

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
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'js_url' ) . 'peerraiser-admin.js',
            array( 'jquery', 'peerraiser-select2' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-select2',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'vendor/select2.min.js',
            array( 'jquery' ),
            '4.0.2',
            true
        );
    }


    public function register_admin_styles() {
        wp_register_style(
            'peerraiser-select2',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'vendor/select2.min.css',
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
            $admin_notices = \PeerRaiser\Controller\Admin\Admin_Notices::get_instance();
            $message = __( 'Create your first campaign to get started. <a href="post-new.php?post_type=pr_campaign">Create Campaign</a>' , 'peerraiser' );
            $admin_notices::add_notice( $message );
        }
    }


    /**
     * Customize the "Enter title here" placeholder in the Title field based on post type
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     */
    public function customize_title( $title ) {
        $current_screen = get_current_screen();

        switch ($current_screen->post_type) {
            case 'fundraiser':
                if ( $title === 'fundraiser' ) {
                    $title = 'fundraiser';
                } else {
                    $title = __( 'Enter the fundraiser name here', 'peerraiser' );
                }
                break;

            default:
                break;
        }

        return $title;
    }


    /**
     * Retreive posts and creates <option>for select lists
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     *
     * @return    array                              Data formatted for select2
     */
    public function ajax_get_posts() {
        $data =  array(
            'success' => false,
            'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
        );

        $choices = \PeerRaiser\Helper\Field::get_choices( $_POST );

        echo \PeerRaiser\Helper\String::peerraiser_json_encode( $choices );

        wp_die();
    }


    public function ajax_get_users() {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
            )
        );

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

        $event->set_result(
            array(
                'items' => $data ,
                'total_count' => $total_users
            )
        );
    }

}
