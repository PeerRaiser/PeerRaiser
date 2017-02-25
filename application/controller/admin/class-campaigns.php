<?php

namespace PeerRaiser\Controller\Admin;

class Campaigns extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                          array( $this, 'register_meta_boxes' ) );
        add_action( 'admin_print_styles-post-new.php',          array( $this, 'load_assets' ) );
        add_action( 'admin_print_styles-post.php',              array( $this, 'load_assets' ) );
        // add_action( 'added_post_meta',                          array( $this, 'add_connections' ) );
        // add_action( 'update_post_meta',                         array( $this, 'update_connections' ) );
        // add_action( 'delete_post_meta',                         array( $this, 'delete_connections' ) );
        add_action( 'cmb2_save_post_fields',                    array( $this, 'update_field_data' ) );
        add_action( 'manage_pr_campaign_posts_custom_column',   array( $this, 'manage_columns' ) );
        add_action( 'manage_edit-pr_campaign_sortable_columns', array( $this, 'sort_columns' ) );
        add_action( 'pre_get_posts',                            array( $this, 'add_sort_type' ) );
        add_action( 'admin_head',                               array( $this, 'remove_date_filter' ) );
        add_action( 'add_meta_boxes',                           array( $this, 'add_meta_boxes' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $default_views = array( 'list', 'add', 'edit' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_campaign_admin_view', 'list', $view );

        // Assign data to the view
        $view_args = array(
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'admin_url'            => get_admin_url(),
            'list_table'           => new \PeerRaiser\Model\Admin\Campaign_List_Table(),
        );
        $this->assign( 'peerraiser', $view_args );

        // Render the view
        $this->render( 'backend/campaign-' . $view );
    }

    public function register_meta_boxes() {

        $campaigns_model = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_field_groups = $campaigns_model->get_fields();

        foreach ($campaign_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'        => $field_group['title'],
                'object_types' => array( 'post' ),
                'hookup'       => false,
                'save_fields'  => false,
            ) );
            foreach ($field_group['fields'] as $key => $value) {
                $cmb->add_field($value);
            }
        }

    }

    public function load_assets() {
        parent::load_assets();

        // If this isn't the Campaigns post type, exit early
        global $post_type;
        if ( 'pr_campaign' != $post_type )
            return;

        // Register and enqueue styles
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
            array('peerraiser-font-awesome'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_register_style(
            'peerraiser-admin-campaigns',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-campaigns.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-campaigns' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-campaigns',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-campaigns.js',
            array( 'jquery', 'peerraiser-admin' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-campaigns' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-campaigns',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
            )
        );

    }

    /**
     * After post meta is added, add the connections
     *
     * @since    1.0.0
     * @param    \PeerRaiser\Core\Event    $event
     * @return   null
     */
    public function add_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_campaign_participants' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_campaign_participants':
                foreach ($_meta_value as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->connect( $object_id, $value, array(
                        'date' => current_time('mysql')
                    ) );
                }
                break;

            default:
                break;
        }

    }

    /**
     * Before the post meta is updated, update the connections
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     * @return    null
     */
    public function update_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_campaign_participants' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_campaign_participants':
                $removed = array_diff($old_value, $_meta_value);
                $added = array_diff($_meta_value, $old_value);
                // Remove the value from connection
                foreach ($removed as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->disconnect( $object_id, $value );
                }
                // Add the new connection
                foreach ($added as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->connect( $object_id, $value, array(
                        'date' => current_time('mysql')
                    ) );
                }
                break;

            default:
                break;
        }

    }

    /**
     * Before post meta is deleted, delete the connections
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     * @return    null
     */
    public function delete_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_campaign_participants' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_campaign_participants':
                // Remove the value from connection
                foreach ($old_value as $key => $value) {
                    p2p_type( 'campaign_to_participant' )->disconnect( $object_id, $value );
                }
                break;

            default:
                break;
        }

    }

    public function update_field_data( $object_id, $cmb_id, $updated, $cmb ) {
        $post_type = get_post_type($object_id);

        if ( $post_type !== 'pr_campaign' )
            return;

        $start_date = get_post_meta( $object_id, '_peerraiser_campaign_start_date', true );
        $post_status = get_post_status( $object_id );
        $allowed_status = array( 'publish', 'future', 'private');

        if ( empty( $start_date ) && in_array($post_status, $allowed_status) ) {
            $date = current_time( 'timestamp' );
            // $_POST['_peerraiser_campaign_start_date'] = $date;
            $results = update_post_meta( (int) $object_id, '_peerraiser_campaign_start_date', (string) $date);
        }
    }

    public function manage_columns( $column_name, $post_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        switch ( $column_name ) {
            case 'amount_raised':
                echo $currency_symbol . \PeerRaiser\Helper\Stats::get_total_donations_by_campaign( $post_id );
                break;

            case 'goal_amount':
                $goal_amount = get_post_meta( $post_id, '_peerraiser_campaign_goal', true);
                echo ( !empty($goal_amount) && $goal_amount != '0.00' ) ? $currency_symbol . $goal_amount : '&mdash;';
                break;

            case 'fundraisers':
                echo $this->get_total_fundraisers( $post_id );
                break;

            case 'teams':
                echo $this->get_total_teams( $post_id );
                break;

            case 'donations':
                echo $this->get_total_donations( $post_id );
                break;

            case 'start_date':
                $start_date = get_post_meta( $post_id, '_peerraiser_campaign_start_date', true );
                echo ( !empty($start_date) ) ? date_i18n( get_option( 'date_format' ), $start_date ) : '&ndash;';
                break;

            case 'end_date':
                $end_date = get_post_meta( $post_id, '_peerraiser_campaign_end_date', true );
                echo ( !empty($end_date) ) ? date_i18n( get_option( 'date_format' ), $end_date ) : '&infin;';
                break;
        }
    }

    public function sort_columns( $sortable_columns ){

        $sortable_columns['start_date'] = 'campaign_start_date';
        $sortable_columns['end_date'] = 'campaign_end_date';

        $event->set_result( $sortable_columns );
    }

    public function add_sort_type( $query ){

        if ( ! is_admin() )
                return;

        $orderby = $query->get( 'orderby');

        switch ( $orderby ) {

            case 'amount_raised':

            case 'campaign_start_date':
                $query->set('meta_query', array(
                    'relation' => 'OR',
                    array(
                        'key'=>'_peerraiser_campaign_start_date',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key'=>'_peerraiser_campaign_start_date',
                        'compare' => 'NOT EXISTS'
                    )
                ));
                $query->set('orderby','meta_value_num');
                break;

            case 'campaign_end_date':
                $query->set('meta_query', array(
                    'relation' => 'OR',
                    array(
                        'key'=>'_peerraiser_campaign_end_date',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key'=>'_peerraiser_campaign_end_date',
                        'compare' => 'NOT EXISTS'
                    )
                ));
                $query->set('orderby','meta_value_num');
                break;
        }

    }

    public function remove_date_filter(){
        $screen = get_current_screen();

        if ( $screen->post_type === 'pr_campaign' ) {
            add_filter('months_dropdown_results', '__return_empty_array');
        }

    }

    public function get_total_fundraisers( $campaign_id ) {
        $args = array(
            'post_type' => 'fundraiser',
            'connected_type' => 'campaign_to_fundraiser',
            'connected_items' => $campaign_id,
            'posts_per_page' => -1
        );
        $fundraisers = new \WP_Query( $args );
        return $fundraisers->found_posts;
    }

    public function get_total_teams( $campaign_id ) {
        $args = array(
            'post_type' => 'fundraiser',
            'connected_type' => 'campaigns_to_teams',
            'connected_items' => $campaign_id,
            'posts_per_page' => -1
        );
        $teams = new \WP_Query( $args );
        return $teams->found_posts;
    }

    public function get_total_donations( $campaign_id ) {
        $args = array(
            'post_type' => 'fundraiser',
            'connected_type' => 'donation_to_campaign',
            'connected_items' => $campaign_id,
            'posts_per_page' => -1
        );
        $donations = new \WP_Query( $args );
        return $donations->found_posts;
    }

    public function add_meta_boxes() {
        if ( $this->is_edit_page( 'new' ) )
            return;

        add_meta_box(
            'campaign_donations',
            __('Donations', 'peerraiser'),
            array( $this, 'display_donations_list' ),
            'pr_campaign'
        );

        add_meta_box(
            'campaign_fundraisers',
            __('Fundraisers', 'peerraiser'),
            array( $this, 'display_fundraisers_list' ),
            'pr_campaign'
        );

        add_meta_box(
            'campaign_teams',
            __('Teams', 'peerraiser'),
            array( $this, 'display_teams_list' ),
            'pr_campaign'
        );

        add_meta_box(
            "campaign_stats",
            __( 'Campaign Stats', 'peerraiser'),
            array( $this, 'display_campaign_stats' ),
            'pr_campaign',
            'side'
        );

    }

    public function display_fundraisers_list() {
        global $post;
        $paged = isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1;

        $campaigns    = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_fundraisers = $campaigns->get_fundraisers( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_fundraisers,
            'paged'        => isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1,
            'paged_name'   => 'fundraisers_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_fundraisers' => $campaign_fundraisers->found_posts,
            'pagination'            => $pagination,
            'currency_symbol'       => $currency_symbol,
            'fundraisers'           => $campaign_fundraisers->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-fundraisers' );
    }

    public function display_donations_list() {
        global $post;
        $paged = isset($_GET['donations_page']) ? $_GET['donations_page'] : 1;

        $campaigns          = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_donations = $campaigns->get_donations( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_donations,
            'paged'        => isset($_GET['donations_page']) ? $_GET['donations_page'] : 1,
            'paged_name'   => 'donations_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_donations' => $campaign_donations->found_posts,
            'pagination'          => $pagination,
            'currency_symbol'     => $currency_symbol,
            'donations'           => $campaign_donations->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-donations' );
    }

    public function display_teams_list() {
        global $post;
        $paged = isset($_GET['teams_page']) ? $_GET['teams_page'] : 1;

        $campaigns      = new \PeerRaiser\Model\Admin\Campaigns();
        $campaign_teams = $campaigns->get_teams( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_teams,
            'paged'        => isset($_GET['teams_page']) ? $_GET['teams_page'] : 1,
            'paged_name'   => 'teams_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_teams' => $campaign_teams->found_posts,
            'pagination'      => $pagination,
            'currency_symbol' => $currency_symbol,
            'teams'           => $campaign_teams->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-teams' );
    }

    public function display_campaign_stats( $post ) {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $end_date = get_post_meta( $post->ID, '_peerraiser_campaign_end_date', true );
        $goal = get_post_meta( $post->ID, '_peerraiser_campaign_goal', true );
        $days_left = 0;

        if ( !empty( $end_date ) ) {
            $today = time();
            $difference = $end_date - $today;
            $days_left = floor($difference/60/60/24);
        }

        $total_donations = \PeerRaiser\Helper\Stats::get_total_donations_by_campaign( $post->ID );

        $view_args = array(
            'currency_symbol' => $currency_symbol,
            'has_goal' => ( $goal !== '0.00' ),
            'has_end_date' => !empty( $end_date ),
            'total_donations' => number_format_i18n( $total_donations, 2),
            'goal_percent' => ( !empty( $goal ) && $goal !== '0.00' ) ? number_format( ( $total_donations / $goal ) * 100, 2) : 0,
            'days_left' => ( $days_left < 0 ) ? __( 'Campaign Ended', 'peerraiser' ) : $days_left,
            'days_left_class' => ( $days_left < 0 ) ? 'negative' : 'positive',
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-stats' );
    }

}