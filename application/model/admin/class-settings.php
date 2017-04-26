<?php

namespace PeerRaiser\Model\Admin;

class Settings extends Admin {

    private $fields   = array();
    private $tabs     = array();
    private $content  = array();

    public function __construct() {
        $this->fields = array(
            array(
                'id'     => 'general-settings',
                'fields' => array(
                    'test_mode' => array(
                        'name'       => __( 'Enable Test Mode', 'peerraiser' ),
                        'id'         => 'test_mode',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options'    => array(
                            'true'    => __( 'Yes', 'peerraiser' ),
                            'false'   => __( 'No', 'peerraiser' ),
                        ),
                    ),
                    'fundraiser_slug' => array(
                        'name'       => __( 'Fundraiser Slug', 'peerraiser' ),
                        'id'         => 'fundraiser_slug',
                        'type'       => 'text_small',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'campaign_slug' => array(
                        'name'       => __( 'Campaign Slug', 'peerraiser' ),
                        'id'         => 'campaign_slug',
                        'type'       => 'text_small',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'show_welcome_message' => array(
                        'name'       => __( 'Show Welcome Message on Dashboard?', 'peerraiser' ),
                        'id'         => 'show_welcome_message',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options' => array(
                            'true'    => __( 'Yes', 'peerraiser' ),
                            'false'   => __( 'No', 'peerraiser' ),
                        ),
                    ),
                    'disable_css_styles' => array(
                        'name'       => __( 'Disable Default CSS Styles?', 'peerraiser' ),
                        'id'         => 'disable_css_styles',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options' => array(
                            'false'   => __( 'No', 'peerraiser' ),
                            'true'    => __( 'Yes', 'peerraiser' ),
                        ),
                    ),
                    'campaign_thumbnail_image' => array(
                        'name'       => __('Default Campaign Thumbnail', 'peerraiser'),
                        'id'         => 'campaign_thumbnail_image',
                        'type'       => 'file',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                    ),
                    'user_thumbnail_image' => array(
                        'name'       => __('Default User Thumbnail', 'peerraiser'),
                        'id'         => 'user_thumbnail_image',
                        'type'       => 'file',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options'    => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                    ),
                    'team_thumbnail_image' => array(
                        'name'       => __('Default Team Thumbnail', 'peerraiser'),
                        'id'         => 'team_thumbnail_image',
                        'type'       => 'file',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                    ),
                    'thank_you_page' => array(
                        'name'              => __('Thank You Page', 'peerraiser'),
                        'id'                => 'thank_you_page',
                        'type'              => 'select',
                        'options_cb'        => array( $this, 'get_selected_post'),
                    ),
                    'login_page' => array(
                        'name'              => __('Login Page', 'peerraiser'),
                        'id'                => 'login_page',
                        'type'              => 'select',
                        'options_cb'        => array( $this, 'get_selected_post'),
                    ),
                    'signup_page' => array(
                        'name'              => __('Signup Page', 'peerraiser'),
                        'id'                => 'signup_page',
                        'type'              => 'select',
                        'options_cb'        => array( $this, 'get_selected_post'),
                    ),
                    'participant_dashboard' => array(
                        'name'              => __('Participant Dashboard', 'peerraiser'),
                        'id'                => 'participant_dashboard',
                        'type'              => 'select',
                        'options_cb'        => array( $this, 'get_selected_post'),
                    ),

                ),
            ),
            array(
                'id'     => 'currency-settings',
                'fields' => array(
                    'currency' => array(
                        'name'       => __( 'Currency', 'peerraiser' ),
                        'id'         => 'currency',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options_cb' => array( $this, 'get_select_options'),
                    ),
                    'currency_position' => array(
                        'name' => __( 'Currency Position', 'peerraiser' ),
                        'id'   => 'currency_position',
                        'type' => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options' => array(
                            'before' => __( 'Before', 'peerraiser' ),
                            'after' => __( 'After', 'peerraiser' ),
                        )
                    ),
                    'thousands_separator' => array(
                        'name' => __( 'Thousands Separator', 'peerraiser' ),
                        'id'   => 'thousands_separator',
                        'type' => 'text_small',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                    'decimal_separator' => array(
                        'name' => __( 'Decimal Separator', 'peerraiser' ),
                        'id'   => 'decimal_separator',
                        'type' => 'text_small',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                    'number_decimals' => array(
                        'name' => __( 'Number of Decimals', 'peerraiser' ),
                        'id'   => 'number_decimals',
                        'type' => 'text',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                )
            ),
			array(
				'id' => 'account-settings',
				'fields' => array(
					'peerraiser_username' => array(
						'name'       => __('PeerRaiser.com Username', 'peerraiser' ),
						'id'         => 'peerraiser_username',
						'type'       => 'text',
						'default_cb' => array( $this, 'get_field_value' ),
					),
					'peerraiser_password' => array(
						'name' => __('PeerRaiser.com Password', 'peerraiser' ),
						'id'   => 'peerraiser_password',
						'type' => 'text',
						'attributes' => array(
							'type' => 'password',
						),
						'default_cb' => array( $this, 'get_field_value' ),
					),
					'peerraiser_secret_key' => array(
						'name' => __('PeerRaiser.com Key', 'peerraiser' ),
						'id'   => 'peerraiser_secret_key',
						'type' => 'text',
						'attributes' => array(
							'type' => 'password',
						),
						'default_cb' => array( $this, 'get_field_value' ),
					),
				)
			),
            array(
                'id'     => 'email-settings',
                'fields' => array(
                    'from_name' => array(
                        'name'       => __('From Name', 'peerraiser'),
                        'id'         => 'from_name',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'from_email' => array(
                        'name'       => __('From Email', 'peerraiser'),
                        'id'         => 'from_email',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                ),
            ),
            array(
                'id'     => 'donation-receipt',
                'fields' => array(
                    'donation_receipt_enabled' => array(
                        'name'       => __('Enabled', 'peerraiser'),
                        'id'         => 'donation_receipt_enabled',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options'    => array(
                            'true'    => __( 'Yes', 'peerraiser' ),
                            'false'   => __( 'No', 'peerraiser' ),
                        ),
                    ),
                    'donation_receipt_subject' => array(
                        'name'       => __('Email Subject', 'peerraiser'),
                        'id'         => 'donation_receipt_subject',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'donation_receipt_body' => array(
                        'name'       => __('Email Body', 'peerraiser'),
                        'id'         => 'donation_receipt_body',
                        'type'       => 'wysiwyg',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                ),
            ),
            array(
                'id'     => 'new-donation-notification',
                'fields' => array(
                    'new_donation_notification_enabled' => array(
                        'name'       => __('Enabled', 'peerraiser'),
                        'id'         => 'new_donation_notification_enabled',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options'    => array(
                            'true'    => __( 'Yes', 'peerraiser' ),
                            'false'   => __( 'No', 'peerraiser' ),
                        ),
                    ),
                    'new_donation_notification_to' => array(
                        'name'       => __('Notification Recipients', 'peerraiser'),
                        'id'         => 'new_donation_notification_to',
                        'type'       => 'text',
                        'desc'       => __('A comma-separated list of email addresses that should receive this email.', 'peerraiser'),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'new_donation_notification_subject' => array(
                        'name'       => __('Email Subject', 'peerraiser'),
                        'id'         => 'new_donation_notification_subject',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'new_donation_notification_body' => array(
                        'name'       => __('Email Body', 'peerraiser'),
                        'id'         => 'new_donation_notification_body',
                        'type'       => 'wysiwyg',
                        'default_cb' => array( $this, 'get_field_value'),
                    )
                ),
            ),
            array(
                'id'     => 'welcome-email',
                'fields' => array(
                    'welcome_email_enabled' => array(
                        'name'       => __('Enabled', 'peerraiser'),
                        'id'         => 'welcome_email_enabled',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options'    => array(
                            'true'    => __( 'Yes', 'peerraiser' ),
                            'false'   => __( 'No', 'peerraiser' ),
                        ),
                    ),
                    'welcome_email_subject' => array(
                        'name'       => __('Email Subject', 'peerraiser'),
                        'id'         => 'welcome_email_subject',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'welcome_email_body' => array(
                        'name'       => __('Email Body', 'peerraiser'),
                        'id'         => 'welcome_email_body',
                        'type'       => 'wysiwyg',
                        'default_cb' => array( $this, 'get_field_value'),
                    )
                ),
            ),
            array(
                'id'     => 'advanced-settings',
                'fields' => array(
                    'uninstall_deletes_data' => array(
                        'name'       => __( 'Delete all data when uninstalling plugin?', 'peerraiser' ),
                        'id'         => 'uninstall_deletes_data',
                        'type'       => 'select',
                        'default_cb' => array( $this, 'get_field_value'),
                        'options'    => array(
                            'true'    => __( 'Yes', 'peerraiser' ),
                            'false'   => __( 'No', 'peerraiser' ),
                        ),
                    ),
                ),
            ),
        );
        $this->tabs = array(
            'general'  => __('General', 'peerraiser'),
			'account'  => __('Account', 'peerraiser'),
            'emails'   => __('Emails', 'peerraiser'),
            'advanced' => __('Advanced', 'peerraiser'),
        );
        $this->content = array(
            'general' => array (
                'general' => array(
                    'name'   => __('General Settings', 'peerraiser'),
                    'fields' => 'general-settings',
                ),
                'currency' => array(
                    'name' => __('Currency', 'peerraiser'),
                    'fields' => 'currency-settings',
                )
            ),
			'account' => array(
				'account' => array(
					'name' => __('Account Settings', 'peerraiser'),
					'fields' => 'account-settings',
				)
			),
            'emails' => array (
                'emails' => array(
                    'name'   => __('Email Settings', 'peerraiser'),
                    'fields' => 'email-settings',
                ),
                'donation_receipt' => array(
                    'name'   => __('Donation Receipt', 'peerraiser'),
                    'fields' => 'donation-receipt',
                ),
                'new_donation_notification' => array(
                    'name'   => __('New Donation Notification', 'peerraiser'),
                    'fields' => 'new-donation-notification',
                ),
                'welcome_email' => array(
                    'name'   => __('Welcome Email', 'peerraiser'),
                    'fields' => 'welcome-email',
                ),
            ),
            'advanced' => array (
                'advanced' => array(
                    'name'   => __('Advanced Settings', 'peerraiser'),
                    'fields' => 'advanced-settings',
                ),
            ),
        );
    }

    /**
     * Get all fields
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public function get_fields() {
        return $this->fields;
    }

    /**
     * Get a specific field by id
     *
     * @since     1.0.0
     * @param     string    $id    The field ID
     *
     * @return    array|false    The field data if available, or false if not
     */
    public function get_field( $id ) {
        if ( isset( $this->fields[$id] ) ) {
            return $this->fields[$id];
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
    public function add_fields( array $fields ) {
        array_push($this->fields, $fields);

        return $this->fields;
    }

    public function get_select_options( $field ) {
        if ( $field->args['name'] === 'Currency' ) {

            $currency_model = new \PeerRaiser\Model\Currency();

            $currencies = $currency_model->get_currencies();

            foreach ( $currencies as $currency ) {
                $currency_options[$currency['short_name']] = $currency['full_name'] . ' ('.$currency['short_name'].')';
            }

            return ( isset($currency_options) ) ? $currency_options : array();
        }
    }

    public function get_field_value( $field ) {
        $plugin_options = get_option( 'peerraiser_options', array() );

        switch ($field['id']) {
            case 'currency':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : 'USD';
                break;

            case 'currency_position':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : 'before';
                break;

            case 'thousands_separator':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : ',';
                break;

            case 'decimal_separator':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : '.';
                break;

            case 'number_decimals':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? $plugin_options[$field['id']] : 2;
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

    public function get_selected_post( $field ) {
        $plugin_options = get_option( 'peerraiser_options', array() );
        $results = array();

        $page_id = $plugin_options[ $field->args['id'] ];
        $page    = get_post($page_id);

        $results[$page_id] = get_the_title( $page );

        return $results;
    }

    public function get_field_names() {
        $field_names = array();
        foreach ($this->fields as $field_group) {
            $field_names = array_merge($field_names, array_keys($field_group['fields']) );
        }
        return $field_names;
    }

    public function get_settings_tabs() {
        return $this->tabs;
    }


    public function get_settings_sections() {
        return $this->content;
    }


    public function get_settings_content( $tab, $section ) {
        $content = $this->content;
        $data = array();

        $section = $content[$tab][$section];

        $html = '';
        $data['title'] = $section['name'];

        if ( isset($section['before_fields']) ) {
            $html .= $section['before_fields'];
        }

        if (  isset($section['fields']) ) {
            $field_html = cmb2_get_metabox_form(
                $section['fields'],
                0,
                array(
                    'form_format' => '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<button class="ladda-button" data-style="expand-right" data-color="blue" data-size="s"><span class="ladda-label">%4$s</span></button></form>',
                    'save_button' => sprintf( '<span class="ladda-label">%s</span>', __( 'Save Settings', 'peerraiser') ),
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
