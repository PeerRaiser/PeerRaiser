<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Model\Donor as Donor_Model;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;

class Donors extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                     array( $this, 'register_meta_boxes' ) );
        add_action( 'peerraiser_after_donor_metaboxes',    array( $this, 'donor_notes_metabox' ), 50, 1 );
        add_action( 'peerraiser_page_peerraiser-donors',   array( $this, 'load_assets' ) );
        add_action( 'user_register',                       array( $this, 'maybe_connect_user_to_donor' ) );
        add_action( 'peerraiser_add_donor',          	   array( $this, 'handle_add_donor' ) );
        add_action( 'peerraiser_update_donor',             array( $this, 'handle_update_donor' ) );
        add_action( 'peerraiser_delete_donor',             array( $this, 'delete_donor' ) );
        add_action( 'peerraiser_donor_updated_first_name', array( $this, 'update_full_name' ), 10, 3 );
        add_action( 'peerraiser_donor_updated_last_name',  array( $this, 'update_full_name' ), 10, 3 );
    }

    /**
     * Singleton to get only one Campaigns controller
     *
     * @return    \PeerRaiser\Controller\Admin\Donors
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

        $currency        = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $default_views = array( 'list', 'add', 'summary' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_donor_admin_view', 'list', $view );

        $view_args = array(
            'currency_symbol'   => $currency_symbol,
            'standard_currency' => $plugin_options['currency'],
            'admin_url'         => get_admin_url(),
            'list_table'        => new \PeerRaiser\Model\Admin\Donor_List_Table(),
        );

        if ( $view === 'summary' ) {
            $view_args['donor'] = new \PeerRaiser\Model\Donor( $_REQUEST['donor'] );
            $view_args['profile_image_url'] = $view_args['donor']->get_profile_image();
        }

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/donor-' . $view );
    }

    public function register_meta_boxes() {

        $donors_model = new \PeerRaiser\Model\Admin\Donors_Admin();
        $donor_field_groups = $donors_model->get_fields();

        foreach ($donor_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'         => $field_group['title'],
                'object_types'  => array( 'pr_donor' ),
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
            'peerraiser-admin-donors',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-donors.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-donors' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-donors',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-donors.js',
            array( 'jquery', 'peerraiser-admin' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-donors' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-donors',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
            )
        );

    }

    public function display_donor_box( $object ) {
        $donor_user_account = get_post_meta( $object->ID, '_donor_user_account', true);
        $donor_user_info = get_userdata($donor_user_account);

        $view_args = array(
            'profile_image_url' => ( !empty($donor_user_account) ) ? get_avatar_url( $donor_user_account ) : \PeerRaiser\Core\Setup::get_plugin_config()->get('images_url') . 'profile-mask.png',
            'first_name' => get_post_meta( $object->ID, '_donor_first_name', true ),
            'last_name' => get_post_meta( $object->ID, '_donor_last_name', true ),
            'donor_email' => get_post_meta( $object->ID, '_donor_email', true ),
            'donor_id' => $object->ID,
            'donor_user_account' => ( !empty($donor_user_account) ) ? '<a href="user-edit.php?user_id='.$donor_user_account.'">'.$donor_user_info->user_login.'</a>' : __('None', 'peerraiser'),
            'donor_since' => get_the_date(),
            'donor_class' => ( !empty($donor_user_account) ) ? 'user' : 'guest',
        );
        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/donor-card' );
    }

    public function display_donation_list() {
        global $post;
        $paged = isset($_GET['donations_page']) ? $_GET['donations_page'] : 1;

        $donors_model = new \PeerRaiser\Model\Admin\Donors_Admin();
        $donor_donations = $donors_model->get_donations( $post->ID, $paged );

        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $donor_donations,
            'paged' => isset($_GET['donations_page']) ? $_GET['donations_page'] : 1,
            'paged_name' => 'donations_page'
        );
        $pagination = \PeerRaiser\Helper\View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_donations' => $donor_donations->found_posts,
            'pagination'          => $pagination,
            'currency_symbol'     => $currency_symbol,
            'donations'           => $donor_donations->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/donor-donations' );
    }

    public function maybe_connect_user_to_donor( $user_id ) {
        $user_info     = get_userdata( $user_id );
        $email_address = $user_info->user_email;

        if ( ! is_email( $email_address ) ) {
            return;
        }

        $donor = new \PeerRaiser\Model\Donor( $email_address );

        if ( $donor ) {
            update_post_meta( $donor->ID, '_donor_user_account', $user_id );
        }
    }

    public function donor_notes_metabox( $peerraiser ) {
        if ( ! apply_filters( 'peerraiser_show_donor_notes_metabox', true ) )
            return;

        $this->assign( 'peerraiser', $peerraiser );

        $this->render( 'backend/partials/donor-box-notes' );
    }

    public function handle_add_donor() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_donor_nonce' ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_donor();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $donor = new Donor_Model();

        if ( isset( $_REQUEST['donor_note'] ) && ! empty( trim( $_REQUEST['donor_note'] ) ) ) {
            $user = wp_get_current_user();
            $donor->add_note( $_REQUEST['donor_note'], $user->user_login );
        }

        $this->add_fields( $donor );

        // Save to the database
        $donor->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-donors',
            'view'               => 'summary',
            'donor'              => $donor->ID,
            'peerraiser_notice' => 'donor_added',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donor
        wp_safe_redirect( $location );
    }

    public function handle_update_donor() {
        $donor_id = intval( $_REQUEST['donor_id'] );

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_update_donor_' . $donor_id ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $donor = new \PeerRaiser\Model\Donor( (int) $_REQUEST['donor_id'] );

        if ( isset( $_REQUEST['donor_note'] ) && ! empty( trim( $_REQUEST['donor_note'] ) ) ) {
            $user = wp_get_current_user();
            $donor->add_note( $_REQUEST['donor_note'], $user->user_login );
        }

        $this->update_fields( $donor );
        $donor->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-donors',
            'view'               => 'summary',
            'donor'              => $donor->ID,
            'peerraiser_notice' => 'donor_updated',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donor
        wp_safe_redirect( $location );
    }

    /**
     * Handle "delete donor" action
     *
     * @since 1.0.0
     */
    public function delete_donor() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_delete_donor_' . $_REQUEST['donor_id'] ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        // Delete the donor
        $donor = new \PeerRaiser\Model\Donor( $_REQUEST['donor_id'] );

        $donor->delete();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-donors',
            'peerraiser_notice' => 'donor_deleted'
        ), admin_url( 'admin.php' ) );

        wp_safe_redirect( $location );
    }

    /**
     * If the first or last name was updated, update the full name
     *
     * @param $donor
     * @param $key
     * @param $value
     */
    public function update_full_name( $donor, $key, $value ) {
        if ( $key === 'first_name' ) {
            $donor->full_name = trim( $value . ' ' . $donor->last_name );
        } elseif ( $key === 'last_name' ) {
            $donor->full_name = trim( $donor->first_name . ' ' . $value );
        }

        $donor->save();
    }

    /**
     * Checks if the fields are valid
     *
     * @since     1.0.0
     * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
     */
    private function is_valid_donor() {
        $donors_model     = new \PeerRaiser\Model\Admin\Donors_Admin();
        $required_fields = $donors_model->get_required_field_ids();

        $data = array(
            'is_valid'     => true,
            'field_errors' => array(),
        );

        foreach ( $required_fields as $field ) {
            if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
                $data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
            }
        }

        if ( isset( $_REQUEST['email_address'] ) && ! empty( $_REQUEST['email_address'] ) && ! is_email( $_REQUEST['email_address'] ) ) {
            $data['field_errors'][ 'email_address' ] = __( 'Not a valid email address.', 'peerraiser' );
        }

        $donor_model = new \PeerRaiser\Model\Donor( $_REQUEST['user_id'] );
        $donor = $donor_model->get_donors( array( 'user_id' => $_REQUEST['user_id'] ) );

        if ( ! empty( $donor ) ) {
            $data['field_errors'][ 'user_id' ] = __( 'This user is already associated with a donor.', 'peerraiser' );
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

    private function add_fields( $donor) {
        $donor_model = new \PeerRaiser\Model\Admin\Donors_Admin();

        $field_ids = $donor_model->get_field_ids();

        $field_ids['date'] = '_peerraiser_date';

        foreach ( $field_ids as $key => $value ) {
            switch ( $value ) {
                case "_peerraiser_date" :
                    if ( isset( $_REQUEST['_peerraiser_date'] ) ) {
                        $donor->date = $_REQUEST['_peerraiser_date'];
                    } else {
                        $donor->date = current_time( 'mysql' );
                    }
                    break;
                default :
                    if ( isset( $_REQUEST[$value] ) ) {
                        $donor->$key = $_REQUEST[$value];
                    }
                    break;
            }
        }
    }

    private function update_fields( $donor ) {
        $donors_model = new \PeerRaiser\Model\Admin\Donors_Admin();

        $field_ids = $donors_model->get_field_ids();

        // If the date is empty, set it to today's date
        if ( ! isset( $_REQUEST['date'] ) || empty( $_REQUEST['date'] ) ) {
            $_REQUEST['date'] = current_time( 'mysql' );
        }

        foreach ( $field_ids as $key => $value ) {
            if ( isset( $_REQUEST[$value] ) && $_REQUEST[$value] !== $donor->$key ) {
                $donor->$key = $_REQUEST[$value];
            } elseif ( ! isset( $_REQUEST[$value] ) || $_REQUEST[$value] === '' ) {
                $donor->delete_meta($value);
            }
        }
    }

}
