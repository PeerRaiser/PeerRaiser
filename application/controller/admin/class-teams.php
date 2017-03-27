<?php

namespace PeerRaiser\Controller\Admin;

class Teams extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                    array( $this, 'register_meta_boxes' ) );
        add_action( 'admin_print_styles-post-new.php',    array( $this, 'load_assets' ) );
        add_action( 'admin_print_styles-post.php',        array( $this, 'load_assets' ) );
        add_action( 'meta_boxes',                         array( $this, 'add_meta_boxes' ) );
        add_action( 'manage_pr_team_posts_custom_column', array( $this, 'manage_columns' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $view_args = array(
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'admin_url'            => get_admin_url(),
            'list_table'           => new \PeerRaiser\Model\Admin\Team_List_Table(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/team-list' );
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

        // If this isn't the Fundraiser post type, exit early
        global $post_type;
        if ( 'pr_team' != $post_type )
            return;

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

    public function add_meta_boxes() {
        if ( $this->is_edit_page( 'new' ) )
            return;

        add_meta_box(
            'teams_fundraisers',
            __('Fundraisers', 'peerraiser'),
            array( $this, 'display_fundraisers_list' ),
            'pr_team'
        );
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

    public function manage_columns( $column_name, $post_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        switch ( $column_name ) {

            case 'leader':
                $leader_id = get_post_meta( $post_id, '_team_leader', true );
                $user_info = get_userdata( $leader_id );
                echo '<a href="user-edit.php?user_id='.$leader_id.'">' . $user_info->user_login  . '</a>';
                break;

            case 'campaign':
                $campaign_id = get_post_meta( $post_id, '_team_campaign', true );
                echo '<a href="post.php?action=edit&post='.$campaign_id.'">' . get_the_title( $campaign_id ) . '</a>';
                break;

            case 'amount_raised':
                echo $currency_symbol . number_format_i18n( \PeerRaiser\Helper\Stats::get_total_donations_by_team( $post_id ), 2);
                break;

            case 'goal_amount':
                $goal_amount = get_post_meta( $post_id, '_goal_amount', true);
                echo ( !empty($goal_amount) && $goal_amount != '0.00' ) ? $currency_symbol . $goal_amount : '&mdash;';
                break;

            case 'fundraisers':
                echo $this->get_total_fundraisers( $post_id );
                break;

        }

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
