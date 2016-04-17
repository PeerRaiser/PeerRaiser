<?php

namespace PeerRaiser\Controller\Admin;

class Settings extends Base {

    /**
     * @see \PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_peerraiser_update_settings' => array(
                array( 'ajax_update_settings', 100 ),
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
            ),
            'peerraiser_wordpress_init' => array(
                array( 'maybe_flush_rewrite_rules' )
            ),
        );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();
        $this->register_meta_boxes();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $view_args = array(
            'fields' => cmb2_get_metabox_form(
                'peerraiser-settings',
                0,
                array(
                    'form_format' => '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<button class="ladda-button" data-style="expand-right" data-color="blue" data-size="s"><span class="ladda-label">%4$s</span></button></form>',
                    'save_button' => __( 'Save Settings', 'peerraiser' ),
                )
            ),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/settings' );

    }

    /**
     * @see LaterPay_Core_View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        wp_register_style(
            'peerraiser-ladda',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'vendor/ladda.min.css',
            array(),
            '4.0.2'
        );
        wp_enqueue_style( 'peerraiser-ladda' );

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

        $cmb = new_cmb2_box( array(
            'id'           => 'peerraiser-settings',
            'hookup'       => false,
            'save_fields'  => false,
        ) );

        $settings_model = \PeerRaiser\Model\Admin\Settings::get_instance();
        $settings_fields = $settings_model->get_fields();

        foreach ($settings_fields as $key => $value) {
            $cmb->add_field($value);
        }

    }


    public function ajax_update_settings( \PeerRaiser\Core\Event $event ) {
        check_ajax_referer('nonce_CMB2phppeerraiser-settings');

        $model = \PeerRaiser\Model\Admin\Settings::get_instance();
        $default_fields = $model::get_field_names();

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
                $plugin_options[$data['name']] = sanitize_text_field( $data['value'] );
                $settings_updated++;
            }
        }

        update_option( 'peerraiser_options', $plugin_options );
        set_transient( 'peerraiser_options_updated', true );

        $data = array(
            'success' => true,
            'message' => sprintf( _n( '%d setting updated.', '%d settings updated', $settings_updated, 'peerraiser' ), $settings_updated ),
            'settings_updated' => $settings_updated
        );

        $event->set_result( $data );
    }


    /**
     * If the plugin settings have recently been updated, flush the rewrite rules.
     * This is because one of the settings modifies a custom post type.
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

}
