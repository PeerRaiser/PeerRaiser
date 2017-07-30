<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Model\Team as Team_Model;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;

class Teams extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                  array( $this, 'register_meta_boxes' ) );
        add_action( 'peerraiser_page_peerraiser-teams', array( $this, 'load_assets' ) );
        add_action( 'peerraiser_add_team',	            array( $this, 'handle_add_team' ) );
        add_action( 'peerraiser_update_team',           array( $this, 'handle_update_team' ) );
        add_action( 'peerraiser_delete_team',           array( $this, 'delete_team' ) );
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
            'currency_symbol'   => $currency_symbol,
            'standard_currency' => $plugin_options['currency'],
            'admin_url'         => get_admin_url(),
            'list_table'        => new \PeerRaiser\Model\Admin\Team_List_Table(),
            'team_admin'        => new \PeerRaiser\Model\Admin\Teams_Admin()
        );

        if ( $view === 'summary' ) {
            $view_args['team'] = new \PeerRaiser\Model\Team( $_REQUEST['team'] );
        }

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/team-' . $view );
    }

    public function register_meta_boxes() {

        $teams_model = new \PeerRaiser\Model\Admin\Teams_Admin();
        $team_field_groups = $teams_model->get_fields();

        foreach ( $team_field_groups as $field_group ) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'        => $field_group['title'],
                'object_types' => array( 'pr_team' ),
                'context'      => $field_group['context'],
                'priority'     => $field_group['priority'],
            ) );
            foreach ( $field_group['fields'] as $key => $value ) {
                if ( $key === 'team_campaign' && $this->is_edit_page( 'edit' ) ) {
                    $value['type']       = 'text';
                    $value['attributes'] = array(
                        'readonly' => 'readonly',
                    );
                }
                $cmb->add_field( $value );
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

    public function display_fundraisers_list() {
        global $post;
        $paged = isset( $_GET['fundraisers_page'] ) ? $_GET['fundraisers_page'] : 1;

        $teams_model      = new \PeerRaiser\Model\Admin\Teams_Admin();
        $team_fundraisers = $teams_model->get_fundraisers( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code( $plugin_options['currency'] );

        $args       = array(
            'custom_query' => $team_fundraisers,
            'paged'        => isset( $_GET['fundraisers_page'] ) ? $_GET['fundraisers_page'] : 1,
            'paged_name'   => 'fundraisers_page'
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
            die( __( 'Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_team();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $team = new Team_Model();

        $this->add_fields( $team );

        // Save to the database
        $team->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-teams',
            'view'               => 'summary',
            'team'               => $team->ID,
            'peerraiser_notice' => 'team_added',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    public function handle_update_team() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_update_team_' . $_REQUEST['team_id'] ) ) {
            die( __( 'Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_team();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $team = new Team_Model( $_REQUEST['team_id'] );

        $this->update_fields( $team );
        $team->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-teams',
            'view'               => 'summary',
            'team'               => $team->ID,
            'peerraiser_notice' => 'team_updated',
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
        $teams_model     = new \PeerRaiser\Model\Admin\Teams_Admin();
        $required_fields = $teams_model->get_required_field_ids();

        $data = array(
            'is_valid'     => true,
            'field_errors' => array(),
        );

        // Make sure team name isn't already taken
        if ( isset( $_REQUEST['peerraiser_action'] ) && 'add_team' === $_REQUEST['peerraiser_action'] ) {
            $team_exists = term_exists( $_REQUEST['_peerraiser_team_name'], 'peerraiser_team' );

            if ( $team_exists !== 0 && $team_exists !== null ) {
                $data['field_errors']['_peerraiser_team_name'] = __( 'This team name already exists', 'peerraiser' );
            }
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

    /**
     * Handle "delete campaign" action
     *
     * @since 1.0.0
     */
    public function delete_team() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_delete_team_' . $_REQUEST['team_id'] ) ) {
            die( __( 'Security check failed.', 'peerraiser' ) );
        }

        // Delete the campaign
        $team = new \PeerRaiser\Model\Team( $_REQUEST['team_id'] );

        $team->delete();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-teams',
            'peerraiser_notice' => 'team_deleted',
        ), admin_url( 'admin.php' ) );

        wp_safe_redirect( $location );
    }

    private function add_fields( $team ) {
        $teams_model = new \PeerRaiser\Model\Admin\Teams_Admin();

        $field_ids = $teams_model->get_field_ids();

        // Add team name to field list, since its not a CMB2 field
        $field_ids['team_name'] = '_peerraiser_team_name';

        foreach ( $field_ids as $key => $value ) {
            switch ( $value ) {
                case "_peerraiser_team_name" :
                    $team->team_name = $_REQUEST['_peerraiser_team_name'];
                    break;
                default :
                    if ( isset( $_REQUEST[ $value ] ) ) {
                        $team->$key = $_REQUEST[ $value ];
                    }
                    break;
            }
        }

        foreach ( array ('aa', 'mm', 'jj') as $timeunit ) {
            if ( !empty( $_POST['hidden_' . $timeunit] ) && $_POST['hidden_' . $timeunit] != $_POST[$timeunit] ) {
                $_POST['edit_date'] = '1';
                break;
            }
        }

        if ( !empty ( $_POST['edit_date'] ) ) {
            $aa = $_POST['aa'];
            $mm = $_POST['mm'];
            $jj = $_POST['jj'];
            $jj = ( $jj > 31 ) ? 31 : $jj;

            $team->created = "$aa-$mm-$jj 00:00:00";
        }
    }

    private function update_fields( $team ) {
        $teams_model = new \PeerRaiser\Model\Admin\Teams_Admin();

        $field_ids = $teams_model->get_field_ids();

        if ( isset( $_REQUEST['_peerraiser_team_name'] ) ) {
            $team->update_team_name( $_REQUEST['_peerraiser_team_name'] );
        }

        if ( ! empty( $_REQUEST['_peerraiser_team_name'] ) && $team->team_name !== $_REQUEST['_peerraiser_team_name'] ) {
            $slug = ( isset( $_REQUEST['slug'] ) && ! empty( $_REQUEST['slug'] ) ) ? $_REQUEST['slug'] : $team->team_slug;
            $team->update_team_name( $_REQUEST['_peerraiser_team_name'], $slug );
        } elseif ( isset( $_REQUEST['slug'] ) && ! empty( $_REQUEST['slug'] ) && $_REQUEST['slug'] !== $team->team_slug ) {
            $team->update_team_name( $team->team_name, $_REQUEST['slug'] );
        }

        $current = $team->get_meta();

        foreach ( $field_ids as $key => $value ) {
            // Skip field if it isn't set
            if ( ! isset( $_REQUEST[$value] ) ) {
                continue;
            }

            // Delete field from database if its empty
            if ( trim( $_REQUEST[$value] ) === '' ) {
                if ( ! isset( $current[$value][0] ) ) {
                    continue;
                }

                $team->delete_meta($value);
                continue;
            }

            // Skip field if data didn't change
            if ( isset( $current[$value][0] ) && $_REQUEST[$value] === $current[$value][0] ) {
                continue;
            }

            // Update the data. It changed and isn't empty
            $team->$key = $_REQUEST[$value];
        }
    }

 }
