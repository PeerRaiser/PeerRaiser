<?php

namespace PeerRaiser\Controller\Admin;

class Fundraisers extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                       array( $this, 'register_meta_boxes' ) );
        add_action( 'admin_print_styles-post-new.php',       array( $this, 'load_assets' ) );
        add_action( 'admin_print_styles-post.php',           array( $this, 'load_assets' ) );
        add_action( 'added_post_meta',                       array( $this, 'add_connections' ), 10, 4 );
        add_action( 'update_post_meta',                      array( $this, 'update_connections'), 10, 4 );
        add_action( 'delete_post_meta',                      array( $this, 'delete_connections'), 10, 4 );
        add_action( 'manage_fundraiser_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
        add_action( 'meta_boxes',                            array( $this, 'add_meta_boxes' ) );
        add_action( 'post_edit_form_tag',                    array( $this, 'add_peerraiser_class' ) );
        add_action( 'pre_get_posts',                         array( $this, 'orderby_donation_value' ) );

        add_filter( 'manage_edit-fundraiser_sortable_columns', array( $this, 'sortable_columns' ) );
    }

    public function register_meta_boxes() {
        $fundraisers_model = new \PeerRaiser\Model\Admin\Fundraisers_Admin();
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
        $fields = array( '_peerraiser_fundraiser_campaign', '_peerraiser_fundraiser_participant', '_peerraiser_fundraiser_team' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_peerraiser_fundraiser_campaign':
                $campaign = get_term_by( 'id', $_meta_value, 'peerraiser_campaign' );
                wp_set_post_terms( $object_id, $campaign->name, 'peerraiser_campaign' );
                break;

            case '_peerraiser_fundraiser_participant':
                break;

            case '_peerraiser_fundraiser_team':
                $team = get_term_by( 'id', $_meta_value, 'peerraiser_team' );
                wp_set_post_terms( $object_id, $team->name, 'peerraiser_team' );
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
        $fields = array( '_peerraiser_fundraiser_campaign', '_peerraiser_fundraiser_participant', '_peerraiser_fundraiser_team' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( !in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_peerraiser_fundraiser_campaign':
                // Add the new connection
                $new_value = get_term_by( 'id', $_meta_value, 'peerraiser_campaign' );
                wp_set_post_terms( $object_id, $new_value->name, 'peerraiser_campaign' );
                break;

            case '_peerraiser_fundraiser_participant':
                // Remove the value from connection
                // Add the new connection
                break;

            case '_peerraiser_fundraiser_team':
                // Add the new connection
                $new_value = get_term_by( 'id', $_meta_value, 'peerraiser_team' );
                wp_set_post_terms( $object_id, $new_value->name, 'peerraiser_team' );
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
    public function delete_connections( $meta_ids, $object_id, $meta_key, $_meta_value ) {
        $fields = array( '_peerraiser_fundraiser_campaign', '_peerraiser_fundraiser_participant', '_peerraiser_fundraiser_team' );

        // If the field updated isn't the type that needs to be connected, exit early
        if ( ! in_array($meta_key, $fields) )
            return;

        switch ( $meta_key ) {
            case '_peerraiser_fundraiser_campaign':
                $old_value = get_post_meta( $object_id, '_peerraiser_fundraiser_team', true );
                $campaign  = get_term_by( 'id', $old_value, 'peerraiser_campaign' );
                wp_remove_object_terms( $object_id, $campaign->name, 'peerraiser_campaign' );
                break;

            case '_peerraiser_fundraiser_participant':
                // Remove the value from connection

            case '_peerraiser_fundraiser_team':
                $old_value = get_post_meta( $object_id, '_peerraiser_fundraiser_team', true );
                $team      = get_term_by( 'id', $old_value, 'peerraiser_team' );
                wp_remove_object_terms( $object_id, $team->name, 'peerraiser_team' );
                break;

            default:
                break;
        }

    }

    public function manage_columns( $column_name, $post_id ) {
        $is_test = peerraiser_is_test_mode();

        switch ( $column_name ) {
            case 'campaign':
                $campaigns = wp_get_post_terms( $post_id, 'peerraiser_campaign' );
                if ( ! empty( $campaigns ) ) {
                    $campaign = new \PeerRaiser\Model\Campaign( $campaigns[0]->term_id );
                    echo '<a href="admin.php?page=peerraiser-campaigns&campaign=' . $campaign->ID . '&view=summary">' . $campaign->campaign_name . '</a>';
                } else {
                    echo '&mdash;';
                }
                break;
            case 'participant':
                $participant_id = get_post_meta( $post_id, '_peerraiser_fundraiser_participant', true );
                $user_info = get_userdata( $participant_id );
                echo $user_info ? '<a href="user-edit.php?user_id='.$participant_id.'">' . $user_info->user_login  . '</a>' : '&mdash;';
                break;
            case 'team':
                $teams = wp_get_post_terms( $post_id, 'peerraiser_team' );
                echo ( ! empty($teams) ) ? '<a href="admin.php?page=peerraiser-teams&view=team-details&team='.$teams[0]->term_id.'">' . $teams[0]->name . '</a>' : '&mdash;';
                break;
            case 'goal_amount':
                $goal_amount = get_post_meta( $post_id, '_peerraiser_fundraiser_goal', true);
                echo ( !empty($goal_amount) && $goal_amount != '0.00' ) ? peerraiser_money_format( $goal_amount ) : '&mdash;';
                break;
            case 'amount_raised':
            	$meta_key = $is_test ? '_peerraiser_test_donation_value' : '_peerraiser_donation_value';
                $amount_raised = get_post_meta( $post_id, $meta_key, true);
                echo  $amount_raised ? peerraiser_money_format( $amount_raised ) : peerraiser_money_format( 0.00 );
                break;
        }
    }

    public function sortable_columns( $columns ) {
        $columns['amount_raised'] = 'donation_value';

        return $columns;
    }

    function orderby_donation_value( $query ) {
        if( ! is_admin() )
            return;

        $orderby = $query->get( 'orderby');

        if( 'donation_value' == $orderby ) {
            $query->set( 'meta_key', '_peerraiser_donation_value' );
            $query->set( 'orderby', 'meta_value_num' );
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

        $fundraisers_model    = new \PeerRaiser\Model\Admin\Fundraisers_Admin();
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
