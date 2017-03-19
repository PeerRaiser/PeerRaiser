<?php

namespace PeerRaiser\Controller\Admin;

class Donors extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                     array( $this, 'register_meta_boxes' ) );
		add_action( 'peerraiser_page_peerraiser-donors',   array( $this, 'load_assets' ) );
        add_action( 'add_meta_boxes',                      array( $this, 'add_meta_boxes' ) );
        add_action( 'admin_menu',                          array( $this, 'maybe_replace_submit_box' ) );
        add_action( 'user_register',                       array( $this, 'maybe_connect_user_to_donor' ) );
        add_action( 'manage_pr_donor_posts_custom_column', array( $this, 'manage_columns' ) );
    }

    /**
     * Singleton to get only one Campaigns controller
     *
     * @return    \PeerRaiser\Admin\Campaigns
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
            'list_table'           => new \PeerRaiser\Model\Admin\Donor_List_Table(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/donor-' . $view );
    }

    public function register_meta_boxes() {

        $donors_model = new \PeerRaiser\Model\Admin\Donors();
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

    public function add_meta_boxes() {
        if ( !$this->is_edit_page( 'edit' ) )
            return;

        add_meta_box(
            'donor_info',
            __('Donor Info', 'peerraiser'),
            array( $this, 'display_donor_box' ),
            'pr_donor',
            'normal',
            'high'
        );

        add_meta_box(
            'donor_donations',
            __('Donations', 'peerraiser'),
            array( $this, 'display_donation_list' ),
            'pr_donor'
        );

    }

    public function maybe_replace_submit_box() {

        if ( !$this->is_edit_page( 'edit' ) )
            return;

        remove_meta_box('submitdiv', 'pr_donor', 'core');
        add_meta_box('submitdiv', __('Donor'), array( $this, 'get_submit_box'), 'pr_donor', 'side', 'low');
    }

    public function get_submit_box( $object ) {
        $post_type_object = get_post_type_object($object->post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        $is_published = ( in_array( $object->post_status, array('publish', 'future', 'private') ) );

        $view_args = array(
            'object'             => $object,
            'can_publish'        => $can_publish,
            'is_published'       => $is_published,
            'lifetime_donations' => $this->get_lifetime_donation_amount( $object->ID ),
            'largest_donation'   => $this->get_largest_donation_amount( $object->ID ),
            'latest_donation'    => $this->get_latest_donation_amount( $object->ID ),
            'first_donation'     => $this->get_first_donation_amount( $object->ID ),
        );

        $this->assign( 'peerraiser', $view_args );

        $view_file = ( $is_published ) ? 'backend/partials/donor-box-edit' : 'backend/partials/donor-box-add';

        $this->render( $view_file );

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

        $donors_model = new \PeerRaiser\Model\Admin\Donors();
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

    public function maybe_connect_user_to_donor( $user_id ){
        $user_info = get_userdata( $user_id );
        $email_address = $user_info->user_email;

        $donor = $this->get_donor_by_email( $email_address );
        update_post_meta( $donor->ID, '_donor_user_account', $user_id );
    }

    private function get_donor_by_email( $email ) {
        $query_args = array(
            'post_type'  => 'pr_donor',
            'meta_query' => array(
                array(
                    'key' => '_donor_email',
                    'value' => $email,
                ),
            )
        );
        $donor_query = new \WP_Query( $query_args );

        if ( $donor_query->found_posts == 0 ) {
            return false;
        }

        $donors = $donor_query->get_posts();
        return $donors[0];

    }

    public static function get_lifetime_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'posts_per_page'  => -1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donations = get_posts( $args );

        if ( empty($donations) ) {
            return $currency_symbol. '0.00';
        }

        $donor_value = 0.00;

        foreach ($donations as $donation) {
            $donor_value += get_post_meta( $donation->ID, '_donation_amount', true );
        }

        return $currency_symbol . number_format_i18n( $donor_value, 2 );

    }

    public static function get_latest_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'order'           => 'DESC',
            'orderby'         => 'date',
            'posts_per_page'  => 1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donation = get_posts( $args );

        if ( !empty($donation) ){
            $amount = $currency_symbol . number_format_i18n( get_post_meta( $donation[0]->ID, '_donation_amount', true ), 2 );
        } else {
            $amount = $currency_symbol. '0.00';
        }

        return $amount;
    }

    public static function get_first_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'order'           => 'ASC',
            'orderby'         => 'date',
            'posts_per_page'  => 1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donation = get_posts( $args );

        if ( !empty($donation) ){
            $amount = $currency_symbol . number_format_i18n( get_post_meta( $donation[0]->ID, '_donation_amount', true ), 2 );
        } else {
            $amount = $currency_symbol. '0.00';
        }

        return $amount;
    }

    public static function get_largest_donation_amount( $donor_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'post_type'       => 'pr_donation',
            'post_status'     => 'any',
            'meta_key'        => '_donation_amount',
            'order'           => 'DESC',
            'orderby'         => 'meta_value_num',
            'posts_per_page'  => 1,
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $donor_id,
        );
        $donation = get_posts( $args );

        if ( !empty($donation) ){
            $amount = $currency_symbol . number_format_i18n( get_post_meta( $donation[0]->ID, '_donation_amount', true ), 2 );
        } else {
            $amount = $currency_symbol. '0.00';
        }

        return $amount;
    }

    public function manage_columns( $column_name, $post_id ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        switch ( $column_name ) {

            case 'id':
                echo $post_id;
                break;

            case 'link':
                echo '<a href="post.php?action=edit&post=' . $post_id . '">' . __( 'View Details', 'peerraiser' ) . '</a>';
                break;

            case 'first_name':
                echo get_post_meta( $post_id, '_donor_first_name', true );
                break;

            case 'last_name':
                echo get_post_meta( $post_id, '_donor_last_name', true );
                break;

            case 'email_address':
                echo get_post_meta( $post_id, '_donor_email', true );
                break;

            case 'username':
                $user_id = get_post_meta( $post_id, '_donor_user_account', true );
                $user_info = get_userdata( $user_id );
                echo ( $user_id ) ? '<a href="user-edit.php?user_id='.$user_id.'">' . $user_info->user_login  . '</a>' : '&mdash;';
                break;

            case 'total_donated':
                echo $this->get_lifetime_donation_amount( $post_id );
                break;

        }

    }

}
