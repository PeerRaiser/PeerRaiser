<?php

namespace PeerRaiser\Controller\Admin;

class Fundraisers extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                       array( $this, 'register_meta_boxes' ) );
        add_action( 'admin_print_styles-post-new.php',       array( $this, 'load_assets' ) );
        add_action( 'admin_print_styles-post.php',           array( $this, 'load_assets' ) );
        // add_action( 'added_post_meta',                       array( $this, 'add_connections' ) );
        // add_action( 'update_post_meta',                      array( $this, 'update_connections' ) );
        // add_action( 'delete_post_meta',                      array( $this, 'delete_connections' ) );
        add_action( 'manage_fundraiser_posts_custom_column', array( $this, 'manage_columns' ) );
        add_action( 'meta_boxes',                            array( $this, 'add_meta_boxes' ) );
        add_action( 'post_edit_form_tag',                    array( $this, 'add_peerraiser_class' ) );
    }

    public function register_meta_boxes() {
        $fundraisers_model = new \PeerRaiser\Model\Admin\Fundraisers();
        $fundraiser_field_groups = $fundraisers_model->get_fields();

        foreach ($fundraiser_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'         => $field_group['title'],
                'object_types'  => array( 'fundraiser' ),
                'context'       => $field_group['context'],
                'priority'      => $field_group['priority'],
            ) );
            foreach ($field_group['fields'] as $key => $value) {
                $cmb->add_field($value);
            }
        }

    }

    public function load_assets() {
        parent::load_assets();

        // If this isn't the Fundraiser post type, exit early
        global $post_type;
        if ( 'fundraiser' != $post_type )
            return;

        // Register and enqueue styles
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
            array('peerraiser-font-awesome', 'peerraiser-select2'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_register_style(
            'peerraiser-admin-fundraisers',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-fundraisers.css',
            array('peerraiser-font-awesome', 'peerraiser-admin', 'peerraiser-select2'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-fundraisers' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-fundraisers',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-fundraisers.js',
            array( 'jquery', 'peerraiser-admin', 'peerraiser-select2' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-fundraisers' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-fundraisers',
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
        $fields = array( '_fundraiser_campaign', '_fundraiser_participant', '_fundraiser_team' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_fundraiser_campaign':
                p2p_type( 'campaign_to_fundraiser' )->connect( $_meta_value, $object_id, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_fundraiser_participant':
                p2p_type( 'fundraiser_to_participant' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_fundraiser_team':
                p2p_type( 'fundraiser_to_team' )->connect( $object_id, $_meta_value, array(
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
        $fields = array( '_fundraiser_campaign', '_fundraiser_participant', '_fundraiser_team' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_fundraiser_campaign':
                // Remove the value from connection
                p2p_type( 'campaign_to_fundraiser' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'campaign_to_fundraiser' )->connect( $_meta_value, $object_id, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_fundraiser_participant':
                // Remove the value from connection
                p2p_type( 'fundraiser_to_participant' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'fundraiser_to_participant' )->connect( $object_id, $_meta_value, array(
                    'date' => current_time('mysql')
                ) );
                break;

            case '_fundraiser_team':
                // Remove the value from connection
                p2p_type( 'fundraiser_to_team' )->disconnect( $old_value, $object_id );
                // Add the new connection
                p2p_type( 'fundraiser_to_team' )->connect( $object_id, $_meta_value, array(
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
        $fields = array( '_fundraiser_campaign', '_fundraiser_participant', '_fundraiser_team' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        // Get the old value
        $old_value = get_metadata('post', $object_id, $meta_key, true);

        switch ( $meta_key ) {
            case '_fundraiser_campaign':
                // Remove the value from connection
                p2p_type( 'campaign_to_fundraiser' )->disconnect( $old_value, $object_id );
                break;

            case '_fundraiser_participant':
                // Remove the value from connection
                p2p_type( 'fundraiser_to_participant' )->disconnect( $old_value, $object_id );
                break;

            case '_fundraiser_team':
                // Remove the value from connection
                p2p_type( 'fundraiser_to_team' )->disconnect( $old_value, $object_id );
                break;

            default:
                break;
        }

    }


    public function manage_columns( $column_name, $post_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        switch ( $column_name ) {

            case 'campaign':
                $campaign_id = get_post_meta( $post_id, '_peerraiser_fundraiser_campaign', true );
                echo '<a href="post.php?action=edit&post='.$campaign_id.'">' . get_the_title( $campaign_id ) . '</a>';
                break;

            case 'participant':
                $participant_id = get_post_meta( $post_id, '_peerraiser_fundraiser_participant', true );
                $user_info = get_userdata( $participant_id );
                echo '<a href="user-edit.php?user_id='.$participant_id.'">' . $user_info->user_login  . '</a>';
                break;

            case 'team':
                $team_id = get_post_meta( $post_id, '_peerraiser_fundraiser_team', true );
                echo ( !empty($team_id) ) ? '<a href="post.php?action=edit&post='.$team_id.'">' . get_the_title( $team_id ) . '</a>' : '&mdash;';
                break;

            case 'goal_amount':
                $goal_amount = get_post_meta( $post_id, '_peerraiser_fundraiser_goal', true);
                echo ( !empty($goal_amount) && $goal_amount != '0.00' ) ? $currency_symbol . $goal_amount : '&mdash;';
                break;

            case 'amount_raised':
                echo $currency_symbol . \PeerRaiser\Helper\Stats::get_total_donations_by_fundraiser( $post_id );
                break;

        }

    }

    public function add_meta_boxes() {
        if ( $this->is_edit_page( 'new' ) )
            return;

        add_meta_box(
            'fundraiser_donations',
            __('Donations', 'peerraiser'),
            array( $this, 'display_donations_list' ),
            'fundraiser'
        );
    }

    public function display_donations_list() {
        global $post;
        $paged = isset($_GET['donations_page']) ? $_GET['donations_page'] : 1;

        $fundraisers_model    = new \PeerRaiser\Model\Admin\Fundraisers();
        $fundraiser_donations = $fundraisers_model->get_donations( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $fundraiser_donations,
            'paged'        => isset($_GET['donations_page']) ? $_GET['donations_page'] : 1,
            'paged_name'   => 'donations_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_donations' => $fundraiser_donations->found_posts,
            'pagination'          => $pagination,
            'currency_symbol'     => $currency_symbol,
            'donations'           => $fundraiser_donations->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/fundraiser-donations' );
    }

    public function add_peerraiser_class() {
        // If this isn't the Fundraiser post type, exit early
        global $post_type;
        if ( 'fundraiser' != $post_type )
            return;

        echo ' class="peerraiser-form"';
    }

 }
