<?php

namespace PeerRaiser\Controller\Admin;

class Settings extends Base {

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

}
