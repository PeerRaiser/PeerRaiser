<?php

namespace PeerRaiser\Controller\Admin;

class Settings extends Base {

    public function register_actions() {
        add_action( 'wp_ajax_peerraiser_update_settings', array( $this, 'ajax_update_settings' ) );
        add_action( 'wp_ajax_peerraiser_update_settings', array( $this, 'peerraiser_on_ajax_send_json' ) );
        add_action( 'wordpress_init',                     array( $this, 'add_thumbnail_sizes' ) );
        add_action( 'wordpress_init',                     array( $this, 'maybe_flush_rewrite_rules' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();
        $this->register_meta_boxes();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $active_tab     = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        $active_section = isset( $_GET[ 'section' ] ) ? sanitize_text_field( $_GET['section'] ) : $active_tab;

        $view_args = array(
            'active_tab'     => $active_tab,
            'active_section' => $active_section,
            'tabs'           => $this->get_tabs(),
            'sections'       => $this->get_sections(),
            'content'        => $this->get_settings_content( $active_tab, $active_section ),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/settings' );

    }

    /**
     * @see PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        wp_register_style(
            'peerraiser-ladda',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'vendor/ladda.min.css',
            array(),
            '4.0.2'
        );
        wp_register_style(
            'peerraiser-admin-settings',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-settings.css',
            array('peerraiser-ladda'),
            \PeerRaiser\Core\Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin-fundraisers' );
        wp_enqueue_style( 'peerraiser-ladda' );
        wp_enqueue_style( 'peerraiser-admin-settings' );
        wp_enqueue_style( 'peerraiser-font-awesome' );


        wp_register_script(
            'peerraiser-admin-settings',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-settings.js',
            array( 'jquery', 'peerraiser-ladda', 'peerraiser-spin' ),
            '1.0.0',
            true
        );
        wp_register_script(
            'peerraiser-ladda',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'vendor/ladda.min.js',
            array( 'peerraiser-spin' ),
            '1.0.0',
            true
        );
        wp_register_script(
            'peerraiser-spin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'vendor/spin.min.js',
            array(),
            '1.0.0',
            true
        );
        wp_enqueue_script( 'peerraiser-admin-settings' );
        wp_enqueue_script( 'peerraiser-ladda' );
        wp_enqueue_script( 'peerraiser-spin' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-settings',
            'peerraiser_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri()
            )
        );

    }


    public function register_meta_boxes() {

        $settings_model        = new \PeerRaiser\Model\Admin\Settings();
        $settings_field_groups = $settings_model->get_fields();

        foreach ($settings_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'hookup'       => false,
                'save_fields'  => false,
            ) );
            foreach ($field_group['fields'] as $key => $value) {
                $cmb->add_field($value);
            }
        }

    }


    public function ajax_update_settings() {
        check_ajax_referer($_POST['none_name']);

        $model = new \PeerRaiser\Model\Admin\Settings();
        $default_fields = $model->get_field_names();

        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
            )
        );

        $formData = $_POST['formData'];
        $plugin_options = get_option( 'peerraiser_options', array() );
        $settings_updated = 0;

        foreach ($formData as $data) {
            if ( in_array($data['name'], $default_fields) ) {
                $plugin_options[$data['name']] = $data['value'];
                $settings_updated++;
            }
        }

        update_option( 'peerraiser_options', $plugin_options );
        set_transient( 'peerraiser_options_updated', true );

        $data = array(
            'success' => true,
            'message' => sprintf( _n( '%d setting updated.', '%d settings updated', $settings_updated, 'peerraiser' ), $settings_updated ),
            'settings_updated' => $settings_updated,
            'field_names' => $default_fields
            // settings => json_encode( $plugin_options ),
        );

        $event->set_result( $data );
    }


    /**
     * If the plugin settings have recently been updated, flush the rewrite rules.
     * This is because some of the settings modify custom post type rewrite rules.
     *
     * @since     1.0.0
     * @return    null
     */
    public function maybe_flush_rewrite_rules() {
        if ( get_transient( 'peerraiser_options_updated' ) ) {
            flush_rewrite_rules();
            delete_transient('peerraiser_options_updated');
        }
    }


    public function get_tabs() {
        $settings_model = new \PeerRaiser\Model\Admin\Settings();
        $default_tabs = $settings_model->get_settings_tabs();

        return apply_filters( 'peerraiser_settings_tabs', $default_tabs );
    }


    public function get_sections() {
        $settings_model = new \PeerRaiser\Model\Admin\Settings();
        $default_sections = $settings_model->get_settings_sections();

        return apply_filters( 'peerraiser_settings_sections', $default_sections );
    }


    public function get_settings_content( $active_tab, $active_section ) {
        $settings_model = new \PeerRaiser\Model\Admin\Settings();
        $default_content = $settings_model->get_settings_content( $active_tab, $active_section );

        return apply_filters( 'peerraiser_settings_content', $default_content );
    }


    public function add_thumbnail_sizes() {
        add_image_size( 'peerraiser_campaign_thumbnail', 150, 150, true );
    }

}
