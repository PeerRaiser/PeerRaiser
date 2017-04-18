<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Model\Team as Team_Model;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;

class Teams extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                    array( $this, 'register_meta_boxes' ) );
		add_action( 'peerraiser_page_peerraiser-teams',   array( $this, 'load_assets' ) );
        add_action( 'peerraiser_add_team',	              array( $this, 'handle_add_team' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $default_views = array( 'list', 'add', 'summary' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_donation_admin_view', 'list', $view );

        $view_args = array(
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'admin_url'            => get_admin_url(),
            'list_table'           => new \PeerRaiser\Model\Admin\Team_List_Table(),
            'team_admin'       => new \PeerRaiser\Model\Admin\Teams()
        );

	    if ( $view === 'summary' ) {
		    $view_args['team'] = new \PeerRaiser\Model\Team( $_REQUEST['team'] );
	    }

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/team-' . $view );
    }

    public function register_meta_boxes() {

        $teams_model = new \PeerRaiser\Model\Admin\Teams();
        $team_field_groups = $teams_model->get_fields();

        foreach ($team_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'         => $field_group['title'],
                'object_types'  => array( 'pr_team' ),
                'context'       => $field_group['context'],
                'priority'      => $field_group['priority'],
            ) );
            foreach ($field_group['fields'] as $key => $value) {
                if ( $key === 'team_campaign' && $this->is_edit_page( 'edit' ) ){
                    $value['type'] = 'text';
                    $value['attributes'] = array(
                        'readonly' => 'readonly',
                    );
                }
                $cmb->add_field($value);
            }
        }

    }

    public function load_assets() {
        parent::load_assets();

        // Register and enqueue styles
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
            array('peerraiser-font-awesome', 'peerraiser-select2'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_register_style(
            'peerraiser-admin-teams',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-teams.css',
            array('peerraiser-font-awesome', 'peerraiser-admin', 'peerraiser-select2'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-teams' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-teams',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-teams.js',
            array( 'jquery', 'peerraiser-admin', 'peerraiser-select2' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-teams' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-teams',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri()
            )
        );

    }

    /**
     * After post meta is added, add the connections
     *
     * @since    1.0.0
     * @return   null
     */
    public function add_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_team_campaign', '_team_leader' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_team_campaign':
                p2p_type( 'campaigns_to_teams' )->connect( $_meta_value, $object_id, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_team_leader':
                p2p_type( 'teams_to_captains' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            default:
                break;
        }

    }

    /**
     * Before the post meta is updated, update the connections
     *
     * @since     1.0.0
     * @return    null
     */
    public function update_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_fundraiser_campaign', '_team_leader' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_team_campaign':
                // Remove the value from connection
                p2p_type( 'campaigns_to_teams' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'campaigns_to_teams' )->connect( $_meta_value, $object_id, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_team_leader':
                // Remove the value from connection
                p2p_type( 'teams_to_captains' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'teams_to_captains' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            default:
                break;
        }

    }

    /**
     * Before post meta is deleted, delete the connections
     *
     * @since     1.0.0
     * @return    null
     */
    public function delete_connections( $meta_id, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_team_campaign', '_team_leader', );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_team_campaign':
                // Remove the value from connection
                p2p_type( 'campaigns_to_teams' )->disconnect( $old_value, $object_id );
                break;

            case '_team_leader':
                // Remove the value from connection
                p2p_type( 'teams_to_captains' )->disconnect( $old_value, $object_id );
                break;

            default:
                break;
        }

    }

    public function display_fundraisers_list() {
        global $post;
        $paged = isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1;

        $teams_model = new \PeerRaiser\Model\Admin\Teams();
        $team_fundraisers = $teams_model->get_fundraisers( $post->ID, $paged );

        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $team_fundraisers,
            'paged' => isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1,
            'paged_name' => 'fundraisers_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_fundraisers' => $team_fundraisers->found_posts,
            'pagination'            => $pagination,
            'currency_symbol'       => $currency_symbol,
            'fundraisers'           => $team_fundraisers->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/team-fundraisers' );
    }

    public function handle_add_team() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_team_nonce' ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_team();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $team = new Team_Model();

        // Required Fields
        $team->team_name   = $_REQUEST['_peerraiser_team_title'];
        $team->team_leader = $_REQUEST['_peerraiser_team_leader'];
        $team->campaign_id = $_REQUEST['_peerraiser_team_campaign'];
        $team->team_goal   = $_REQUEST['_peerraiser_goal_amount'];

        // Optional Fields
        if ( isset( $_REQUEST['_peerraiser_team_thumbnail'] ) ) {
            $thumbnail_image_id = \PeerRaiser\Helper\Field::get_image_id_by_url( $_REQUEST['_peerraiser_team_thumbnail'] );
            $team->thumbnail_image = $thumbnail_image_id;
        }

        // Save to the database
        $team->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page' => 'peerraiser-teams',
            'view' => 'summary',
            'campaign_id' => $team->ID
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    /**
     * Checks if the fields are valid
     *
     * @todo Check formatting of goal amounts
     * @since     1.0.0
     * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
     */
    private function is_valid_team() {
        $required_fields = array( '_peerraiser_team_title', '_peerraiser_team_leader', '_peerraiser_team_campaign', '_peerraiser_goal_amount' );

        $data = array(
            'is_valid'     => true,
            'field_errors' => array(),
        );

        // Make sure team name isn't already taken
        $team_exists = term_exists( $_REQUEST['_peerraiser_team_title'], 'peerraiser_team' );

        if ( $team_exists !== 0 && $team_exists !== null ) {
            $data['field_errors'][ '_peerraiser_team_title' ] = __( 'This team name already exists', 'peerraiser' );
        }

        // Check required fields
        foreach ( $required_fields as $field ) {
            if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
                $data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
            }
        }

        if ( ! empty( $data['field_errors'] ) ) {
            $message = __( 'There was an issue creating this team. Please fix the errors below.', 'peerraiser' );
            Admin_Notices_Model::add_notice( $message, 'notice-error', true );

            wp_localize_script(
                'jquery',
                'peerraiser_field_errors',
                $data['field_errors']
            );

            $data['is_valid'] = false;
        }

        return $data;
    }

    private function get_total_fundraisers( $team_id ) {
        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => -1,
            'post_status'     => 'publish',
            'connected_type'  => 'fundraiser_to_team',
            'connected_items' => $team_id
        );
        $fundraisers = new \WP_Query( $args );
        return $fundraisers->found_posts;
    }

 }
