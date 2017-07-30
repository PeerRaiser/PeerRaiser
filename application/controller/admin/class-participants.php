<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Model\Participant as Participant_Model;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;

class Participants extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                         array( $this, 'register_meta_boxes' ) );
        add_action( 'peerraiser_after_participant_metaboxes',  array( $this, 'participant_notes_metabox' ), 50, 1 );
        add_action( 'peerraiser_page_peerraiser-participants', array( $this, 'load_assets' ) );
        add_action( 'peerraiser_add_participant',          	   array( $this, 'handle_add_participant' ) );
        add_action( 'peerraiser_update_participant',           array( $this, 'handle_update_participant' ) );
        add_action( 'peerraiser_delete_participant',           array( $this, 'delete_participant' ) );
    }

    /**
     * Singleton to get only one Campaigns controller
     *
     * @return    \PeerRaiser\Controller\Admin\Participants
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $plugin_options = get_option( 'peerraiser_options', array() );

        $default_views = array( 'list', 'add', 'summary' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_participant_admin_view', 'list', $view );

        $view_args = array(
            'admin_url'         => get_admin_url(),
            'list_table'        => new \PeerRaiser\Model\Admin\Participant_List_Table(),
            'profile_image_url' => ( !empty($donor_user_account) ) ? get_avatar_url( $donor_user_account ) : \PeerRaiser\Core\Setup::get_plugin_config()->get('images_url') . 'profile-mask.png',
        );

        if ( $view === 'summary' ) {
            $view_args['participant']       = new \PeerRaiser\Model\Participant( $_REQUEST['participant'] );
            $view_args['profile_image_url'] = $view_args['profile_image_url'] = $view_args['participant']->get_profile_image();
        }

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/participant-' . $view );
    }

    public function register_meta_boxes() {

        $participants_model = new \PeerRaiser\Model\Admin\Participants_Admin();
        $participant_field_groups = $participants_model->get_fields();

        foreach ($participant_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'         => $field_group['title'],
                'object_types'  => array( 'participant' ),
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

        // Register and enqueue styles
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
            array('peerraiser-font-awesome'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_register_style(
            'peerraiser-admin-participants',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-participants.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-participants' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-participants',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-participants.js',
            array( 'jquery', 'peerraiser-admin' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-participants' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-participants',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
            )
        );
    }

    public function participant_notes_metabox( $peerraiser ) {
        if ( ! apply_filters( 'peerraiser_show_participant_notes_metabox', true ) )
            return;

        $this->assign( 'peerraiser', $peerraiser );

        $this->render( 'backend/partials/participant-box-notes' );
    }

    public function handle_add_participant() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_participant_nonce' ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_participant();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $participant = new Participant_Model();

        if ( isset( $_REQUEST['participant_note'] ) && ! empty( trim( $_REQUEST['participant_note'] ) ) ) {
            $user = wp_get_current_user();
            $participant->add_note( $_REQUEST['participant_note'], $user->user_login );
        }

        $this->add_fields( $participant );

        // Save to the database
        $participant->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-participants',
            'view'               => 'summary',
            'participant'        => $participant->ID,
            'peerraiser_notice' => 'participant_added'
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new participant
        wp_safe_redirect( $location );
    }

    public function handle_update_participant() {
        $participant_id = intval( $_REQUEST['participant_id'] );

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_update_participant_' . $participant_id ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $participant = new \PeerRaiser\Model\Participant( (int) $_REQUEST['participant_id'] );

        if ( isset( $_REQUEST['participant_note'] ) && ! empty( trim( $_REQUEST['participant_note'] ) ) ) {
            $user = wp_get_current_user();
            $participant->add_note( $_REQUEST['participant_note'], $user->user_login );
        }

        $this->update_fields( $participant );
        $participant->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-participants',
            'view'               => 'summary',
            'participant'        => $participant->ID,
            'peerraiser_notice' => 'participant_updated',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    /**
     * Handle "delete participant" action
     *
     * @since 1.0.0
     */
    public function delete_participant() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_delete_participant_' . $_REQUEST['participant_id'] ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        // Delete the participant
        $participant = new \PeerRaiser\Model\Participant( $_REQUEST['participant_id'] );

        $participant->delete();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-participants',
            'peerraiser_notice' => 'participant_deleted',
        ), admin_url( 'admin.php' ) );

        wp_safe_redirect( $location );
    }

    /**
     * Checks if the fields are valid
     *
     * @since     1.0.0
     * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
     */
    private function is_valid_participant() {
        $participants_model     = new \PeerRaiser\Model\Admin\Participants_Admin();
        $required_fields = $participants_model->get_required_field_ids();

        // Unset required fields based on account type
        if ( isset( $_REQUEST['_account_type'] ) ) {
            if ( 'new' === $_REQUEST['_account_type'] ) {
                unset( $required_fields[array_search('user_id', $required_fields)] );
            } elseif ( 'existing' === $_REQUEST['_account_type'] ) {
                unset( $required_fields[array_search('username', $required_fields)] );
            }
        }

        $data = array(
            'is_valid'     => true,
            'field_errors' => array(),
        );

        // Make sure username isn't already taken
        if ( isset( $_REQUEST['username'] ) && ! empty( $_REQUEST['username'] ) ) {
            if ( username_exists( $_REQUEST['username'] ) ) {
                $data['field_errors']['username'] = __( 'This username already exists', 'peerraiser' );
            }
        }

        foreach ( $required_fields as $field ) {
            if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
                $data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
            }
        }

        if ( isset( $_REQUEST['email_address'] ) && ! empty( $_REQUEST['email_address'] ) && ! is_email( $_REQUEST['email_address'] ) ) {
            $data['field_errors'][ 'email_address' ] = __( 'Not a valid email address.', 'peerraiser' );
        }

        if ( ! empty( $_REQUEST['user_id'] ) && $this->is_user_a_participant( $_REQUEST['user_id'] ) ) {
            $data['field_errors'][ 'user_id' ] = __( 'This user is already associated with a participant.', 'peerraiser' );
        }

        if ( ! empty( $data['field_errors'] ) ) {
            $message = __( 'One or more of the required fields was empty, please fix them and try again.', 'peerraiser' );
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

    private function add_fields( $participant) {
        $participant_model = new \PeerRaiser\Model\Admin\Participants_Admin();

        $field_ids = $participant_model->get_field_ids();

        $field_ids['date'] = '_peerraiser_date';

        foreach ( $field_ids as $key => $value ) {
            switch ( $value ) {
                case "_peerraiser_date" :
                    if ( isset( $_REQUEST['_peerraiser_date'] ) ) {
                        $participant->date = $_REQUEST['_peerraiser_date'];
                    } else {
                        $participant->date = current_time( 'mysql' );
                    }
                    break;
                case "user_id" :
                    if ( isset( $_REQUEST['user_id'] ) && ! empty( $_REQUEST['user_id'] ) ) {
                        $participant->ID = $_REQUEST['user_id'];
                    }
                    break;
                default :
                    if ( isset( $_REQUEST[$value] ) ) {
                        $participant->$key = $_REQUEST[$value];
                    }
                    break;
            }
        }
    }

    private function update_fields( $participant ) {
        $participants_model = new \PeerRaiser\Model\Admin\Participants_Admin();

        $field_ids = $participants_model->get_field_ids();

        // If the date is empty, set it to today's date
        if ( ! isset( $_REQUEST['date'] ) || empty( $_REQUEST['date'] ) ) {
            $_REQUEST['date'] = current_time( 'mysql' );
        }

        foreach ( $field_ids as $key => $value ) {
            if ( isset( $_REQUEST[$value] ) && $_REQUEST[$value] !== $participant->$key ) {
                $participant->$key = $_REQUEST[$value];
            } elseif ( ! isset( $_REQUEST[$value] ) || $_REQUEST[$value] === '' ) {
                $participant->delete_meta($value);
            }
        }
    }

    private function is_user_a_participant( $user_id ) {
        $participant_model = new \PeerRaiser\Model\Participant( $_REQUEST['user_id'] );

        $participant_term = get_term_by( 'slug', 'participant', 'peerraiser_group' );

        return is_object_in_term( $user_id, 'peerraiser_group', $participant_term->term_id );
    }
}
