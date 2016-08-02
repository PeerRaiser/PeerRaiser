<?php

namespace PeerRaiser\Model\Admin;

class Settings extends \PeerRaiser\Model\Admin {

    private static $fields = array();
    private static $tabs = array();
    private static $content = array();
    private static $instance = null;

    public function __construct() {}

    /**
     * Singleton to get only one Fundraisers model
     *
     * @return    \PeerRaiser\Model\Admin\Fundraisers
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
            self::$fields = array(
                array(
                    'id'     => 'general-settings',
                    'fields' => array(
                        'test_mode' => array(
                            'name'    => 'Enable Test Mode',
                            'id'      => 'test_mode',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'true'    => __( 'Yes', 'peerraiser' ),
                                'false'   => __( 'No', 'peerraiser' ),
                            ),
                        ),
                        'currency' => array(
                            'name'    => 'Currency',
                            'id'      => 'currency',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(__CLASS__, 'get_select_options'),
                        ),
                        'fundraiser_slug' => array(
                            'name'    => 'Fundraiser Slug',
                            'id'      => 'fundraiser_slug',
                            'type'    => 'text_small',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'campaign_slug' => array(
                            'name'    => 'Campaign Slug',
                            'id'      => 'campaign_slug',
                            'type'    => 'text_small',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'show_welcome_message' => array(
                            'name'    => 'Show Welcome Message on Dashboard?',
                            'id'      => 'show_welcome_message',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'true'    => __( 'Yes', 'peerraiser' ),
                                'false'   => __( 'No', 'peerraiser' ),
                            ),
                        ),
                        'disable_css_styles' => array(
                            'name'    => 'Disable Default CSS Styles?',
                            'id'      => 'disable_css_styles',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'false'   => __( 'No', 'peerraiser' ),
                                'true'    => __( 'Yes', 'peerraiser' ),
                            ),
                        ),
                        'campaign_thumbnail_image' => array(
                            'name'    => __('Default Campaign Thumbnail', 'peerraiser'),
                            'id'      => 'campaign_thumbnail_image',
                            'type'    => 'file',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'url' => false,
                                'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                            ),
                        ),
                        'user_thumbnail_image' => array(
                            'name'    => __('Default User Thumbnail', 'peerraiser'),
                            'id'      => 'user_thumbnail_image',
                            'type'    => 'file',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'url' => false,
                                'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                            ),
                        ),
                        'team_thumbnail_image' => array(
                            'name'    => __('Default Team Thumbnail', 'peerraiser'),
                            'id'      => 'team_thumbnail_image',
                            'type'    => 'file',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'url' => false,
                                'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                            ),
                        ),
                        'thank_you_page' => array(
                            'name'              => __('Thank You Page', 'peerraiser'),
                            'id'                => 'thank_you_page',
                            'type'              => 'select',
                            'options'           => array(__CLASS__, 'get_selected_post'),
                        ),
                        'login_page' => array(
                            'name'              => __('Login Page', 'peerraiser'),
                            'id'                => 'login_page',
                            'type'              => 'select',
                            'options'           => array(__CLASS__, 'get_selected_post'),
                        ),
                        'signup_page' => array(
                            'name'              => __('Signup Page', 'peerraiser'),
                            'id'                => 'signup_page',
                            'type'              => 'select',
                            'options'           => array(__CLASS__, 'get_selected_post'),
                        ),
                        'participant_dashboard' => array(
                            'name'              => __('Participant Dashboard', 'peerraiser'),
                            'id'                => 'participant_dashboard',
                            'type'              => 'select',
                            'options'           => array(__CLASS__, 'get_selected_post'),
                        ),

                    ),
                ),
                array(
                    'id'     => 'email-settings',
                    'fields' => array(
                        'from_name' => array(
                            'name'    => __('From Name', 'peerraiser'),
                            'id'      => 'from_name',
                            'type'    => 'text',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'from_email' => array(
                            'name'    => __('From Email', 'peerraiser'),
                            'id'      => 'from_email',
                            'type'    => 'text',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                    ),
                ),
                array(
                    'id'     => 'donation-receipt',
                    'fields' => array(
                        'donation_receipt_enabled' => array(
                            'name'    => __('Enabled', 'peerraiser'),
                            'id'      => 'donation_receipt_enabled',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'true'    => __( 'Yes', 'peerraiser' ),
                                'false'   => __( 'No', 'peerraiser' ),
                            ),
                        ),
                        'donation_receipt_subject' => array(
                            'name'    => __('Email Subject', 'peerraiser'),
                            'id'      => 'donation_receipt_subject',
                            'type'    => 'text',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'donation_receipt_body' => array(
                            'name'    => __('Email Body', 'peerraiser'),
                            'id'      => 'donation_receipt_body',
                            'type'    => 'wysiwyg',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                    ),
                ),
                array(
                    'id'     => 'new-donation-notification',
                    'fields' => array(
                        'new_donation_notification_enabled' => array(
                            'name'    => __('Enabled', 'peerraiser'),
                            'id'      => 'new_donation_notification_enabled',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'true'    => __( 'Yes', 'peerraiser' ),
                                'false'   => __( 'No', 'peerraiser' ),
                            ),
                        ),
                        'new_donation_notification_to' => array(
                            'name'    => __('Notification Recipients', 'peerraiser'),
                            'id'      => 'new_donation_notification_to',
                            'type'    => 'text',
                            'desc'    => __('A comma-separated list of email addresses that should receive this email.', 'peerraiser'),
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'new_donation_notification_subject' => array(
                            'name'    => __('Email Subject', 'peerraiser'),
                            'id'      => 'new_donation_notification_subject',
                            'type'    => 'text',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'new_donation_notification_body' => array(
                            'name'    => __('Email Body', 'peerraiser'),
                            'id'      => 'new_donation_notification_body',
                            'type'    => 'wysiwyg',
                            'default' => array(__CLASS__, 'get_field_value'),
                        )
                    ),
                ),
                array(
                    'id'     => 'welcome-email',
                    'fields' => array(
                        'welcome_email_enabled' => array(
                            'name'    => __('Enabled', 'peerraiser'),
                            'id'      => 'welcome_email_enabled',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'true'    => __( 'Yes', 'peerraiser' ),
                                'false'   => __( 'No', 'peerraiser' ),
                            ),
                        ),
                        'welcome_email_subject' => array(
                            'name'    => __('Email Subject', 'peerraiser'),
                            'id'      => 'welcome_email_subject',
                            'type'    => 'text',
                            'default' => array(__CLASS__, 'get_field_value'),
                        ),
                        'welcome_email_body' => array(
                            'name'    => __('Email Body', 'peerraiser'),
                            'id'      => 'welcome_email_body',
                            'type'    => 'wysiwyg',
                            'default' => array(__CLASS__, 'get_field_value'),
                        )
                    ),
                ),
                array(
                    'id'     => 'advanced-settings',
                    'fields' => array(
                        'uninstall_deletes_data' => array(
                            'name'    => 'Delete all data when uninstalling plugin?',
                            'id'      => 'uninstall_deletes_data',
                            'type'    => 'select',
                            'default' => array(__CLASS__, 'get_field_value'),
                            'options' => array(
                                'true'    => __( 'Yes', 'peerraiser' ),
                                'false'   => __( 'No', 'peerraiser' ),
                            ),
                        ),
                    ),
                ),
            );
            self::$tabs = array(
                'general'  => __('General', 'peerraiser'),
                'emails'   => __('Emails', 'peerraiser'),
                'advanced' => __('Advanced', 'peerraiser')
            );
            self::$content = array(
                'general' => array (
                    'general' => array(
                        'name'   => __('General Settings', 'peerraiser'),
                        'fields' => 'general-settings'
                    )
                ),
                'emails' => array (
                    'emails' => array(
                        'name'   => __('Email Settings', 'peerraiser'),
                        'fields' => 'email-settings'
                    ),
                    'donation_receipt' => array(
                        'name'   => __('Donation Receipt', 'peerraiser'),
                        'fields' => 'donation-receipt'
                    ),
                    'new_donation_notification' => array(
                        'name'   => __('New Donation Notification', 'peerraiser'),
                        'fields' => 'new-donation-notification'
                    ),
                    'welcome_email' => array(
                        'name'   => __('Welcome Email', 'peerraiser'),
                        'fields' => 'welcome-email'
                    ),
                ),
                'advanced' => array (
                    'advanced' => array(
                        'name'   => __('Advanced Settings', 'peerraiser'),
                        'fields' => 'advanced-settings'
                    ),
                ),
            );
        }

        return self::$instance;
    }

    /**
     * Get all fields
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public static function get_fields() {
        return self::$fields;
    }

    /**
     * Get a specific field by id
     *
     * @since     1.0.0
     * @param     string    $id    The field ID
     *
     * @return    array|false    The field data if available, or false if not
     */
    public static function get_field( $id ) {
        if ( isset( self::$fields[$id] ) ) {
            return self::$fields[$id];
        } else {
            return false;
        }
    }

    /**
     * Add fields
     *
     * @since    1.0.0
     * @param    array    $fields    The fields to add
     *                               format: array( 'id' => array('key' => 'value' ) )
     *
     * @return    array    All of the current fields
     */
    public static function add_fields( array $fields ) {
        array_push(self::$fields, $fields);

        return self::$fields;
    }

    public static function custom_label( $field_args, $field ) {

        $label = $field_args['name'];

        if ( $field_args['options']['tooltip'] ) {
            $label .= sprintf( '<span class="pr_tooltip"><i class="pr_icon fa %s"></i><span class="pr_tip">%s</span></span>', $field_args['options'][ 'tooltip-class' ], $field_args['options'][ 'tooltip' ]);
        }

        return $label;
    }


    public static function get_select_options( $field ) {

        if ( $field->args['name'] === 'Currency' ) {

            $currency_model = new \PeerRaiser\Model\Currency();

            $currencies = $currency_model->get_currencies();

            foreach ( $currencies as $currency ) {
                $currency_options[$currency['short_name']] = $currency['full_name'] . ' ('.$currency['short_name'].')';
            }

            return ( isset($currency_options) ) ? $currency_options : array();

        }

    }


    public static function get_field_value( $field ) {

        $plugin_options = get_option( 'peerraiser_options', array() );

        switch ($field['id']) {
            case 'currency':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : 'custom';
                break;

            case 'fundraiser_slug':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : 'give';
                break;

            case 'campaign_slug':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : 'campaign';
                break;

            // True or False questions
            case 'show_welcome_message':
            case 'disable_css_styles':
            case 'donation_receipt_enabled':
            case 'new_donation_notification_enabled':
            case 'welcome_email_enabled':
            case 'test_mode':
            case 'uninstall_deletes_data':
                $field_value = ( filter_var($plugin_options[$field['id']], FILTER_VALIDATE_BOOLEAN) ) ? 'true' : 'false';
                break;

            case 'from_name':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : get_bloginfo( 'name' );
                break;

            case 'from_email':
            case 'new_donation_notification_to':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : get_bloginfo( 'admin_email' );
                break;

            case 'donation_receipt_subject':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : __('Thank you for your donation', 'peerraiser');
                break;

            case 'new_donation_notification_subject':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : __('New donation received', 'peerraiser');
                break;

            case 'welcome_email_subject':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : __('Welcome!', 'peerraiser');
                break;

            case 'donation_receipt_body':
                $default_body = __('Dear [peerraiser_email show=donor_first_name],

                Thank you so much for your generous donation.

                Transaction Summary
                [peerraiser_email show=donation_summary]

                With thanks,
                [peerraiser_email show=site_name]', 'peerraiser');
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : $default_body;
                break;

            case 'new_donation_notification_body':
                $default_body = __('[peerraiser_email show=donor] has just made a donation!

                Summary
                [peerraiser_email show=donation_summary]', 'peerraiser');
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : $default_body;
                break;

            case 'welcome_email_body':
                $default_body = __('Welcome to the [peerraiser_email show=campaign_name] campaign!', 'peerraiser');
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : $default_body;
                break;

            // case 'campaign_thumbnail_image' :
            //     $attachment_id = $plugin_options['campaign_thumbnail_image'];
            //     $image = wp_get_attachment_image_src( $attachment_id, $size = 'thumbnail' );
            //     $field_value = ( $image ) ? $image[0] : '';
            //     break;

            default:
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : '';
                break;
        }

        return $field_value;

    }


    public static function get_selected_post( $field ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $results = array();

        $page_id = $plugin_options[ $field->args['id'] ];
        $page    = get_post($page_id);

        $results[$page_id] = get_the_title( $page );

        return $results;
    }

    public static function get_field_names() {
        $field_names = array();
        foreach (self::$fields as $field_group) {
            $field_names = array_merge($field_names, array_keys($field_group['fields']) );
        }
        return $field_names;
    }


    public static function get_settings_tabs() {
        return self::$tabs;
    }


    public static function get_settings_sections() {
        return self::$content;
    }


    public static function get_settings_content( $tab, $section ) {
        $content = self::$content;
        $data = array();

        $section = $content[$tab][$section];

        $html = '';
        $data['title'] = $section['name'];

        if (  isset($section['before_fields']) ) {
            $html .= $section['before_fields'];
        }

        if (  isset($section['fields']) ) {
            $field_html = cmb2_get_metabox_form(
                $section['fields'],
                0,
                array(
                    'form_format' => '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<button class="ladda-button" data-style="expand-right" data-color="blue" data-size="s"><span class="ladda-label">%4$s</span></button></form>',
                    'save_button' => __( 'Save Settings', 'peerraiser' ),
                )
            );
            $html .= $field_html;
        }

        if (  isset($section['after_fields']) ) {
            $html .= $section['after_fields'];
        }

        $data['html'] = $html;

        return $data;

    }

}