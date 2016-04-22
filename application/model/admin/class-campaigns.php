<?php

namespace PeerRaiser\Model\Admin;

class Campaigns extends \PeerRaiser\Model\Admin {

    private static $fields = array();
    private static $instance = null;

    public function __construct() {}

    /**
     * Singleton to get only one Campaigns model
     *
     * @return    \PeerRaiser\Model\Admin\Campaigns
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
            self::$fields = array(
                'campaign_start_date' =>   array(
                    'name'     => __( 'Start Date', 'peerraiser' ),
                    'id'       => '_peerraiser_campaign_start_date',
                    'type'     => 'text_date_timestamp',
                    'label_cb' => array(self::get_instance(), 'custom_label'),
                    'options'  => array(
                        'tooltip-class'   => 'fa-question-circle',
                        'tooltip'         => __('Leave blank if the campaign starts when you click the Publish button.', 'peerraiser' ),
                    ),
                ),
                'campaign_end_date' =>   array(
                    'name' => __( 'End Date', 'peerraiser' ),
                    'id'   => '_peerraiser_campaign_end_date',
                    'type' => 'text_date_timestamp',
                    'label_cb' => array(self::get_instance(), 'custom_label'),
                    'options'  => array(
                        'tooltip-class'   => 'fa-question-circle',
                        'tooltip'         => __('Leave blank if the campaign doesn\'t end.', 'peerraiser' ),
                    ),
                ),
                'campaign_description' => array(
                    'name'    => __('Campaign Description', 'peerraiser'),
                    'id'      => '_peerraiser_campaign_description',
                    'type'    => 'wysiwyg',
                    'options' => array(),
                ),
                'campaign_image' => array(
                    'name'    => __('Campaign Image', 'peerraiser'),
                    'id'      => '_peerraiser_campaign_image',
                    'type'    => 'file',
                    'options' => array(
                        'url' => false,
                        'add_upload_file_text' => 'Add Image'
                    ),
                ),
                'campaign_goal' => array(
                    'name' => __('Campaign Goal', 'peerraiser'),
                    'id' => '_peerraiser_campaign_goal',
                    'type' => 'text_money',
                    'before_field' => self::get_currency_symbol(),
                ),
                'individual_goal' => array(
                    'name' => __('Suggested Individual Goal', 'peerraiser'),
                    'id' => '_peerraiser_suggested_individual_goal',
                    'type' => 'text_money',
                    'before_field' => self::get_currency_symbol(),
                    'label_cb' => array(self::get_instance(), 'custom_label'),
                    'options'  => array(
                        'tooltip-class'   => 'fa-question-circle',
                        'tooltip'         => __('The amount to display as a fundraising target to participants.', 'peerraiser' ),
                    ),
                ),
                'team_goal' => array(
                    'name' => __('Suggested Team Goal', 'peerraiser'),
                    'id' => '_peerraiser_suggested_team_goal',
                    'type' => 'text_money',
                    'before_field' => self::get_currency_symbol(),
                    'label_cb' => array(self::get_instance(), 'custom_label'),
                    'options'  => array(
                        'tooltip-class'   => 'fa-question-circle',
                        'tooltip'         => __('The amount to display as a fundraising target to team captains.', 'peerraiser' ),
                    ),
                ),
                'campaign_limit' => array(
                    'name' => __( 'Registration Limit', 'peerraiser' ),
                    'id'   => '_peerraiser_campaign_limit',
                    'type' => 'text_small',
                    'attributes' => array(
                        'type' => 'number',
                        'pattern' => '\d*',
                    ),
                    'label_cb' => array(self::get_instance(), 'custom_label'),
                    'options'  => array(
                        'tooltip-class'   => 'fa-question-circle',
                        'tooltip'         => __('Enter the max number of participants that can register. Leave blank for unlimited.', 'peerraiser' ),
                    ),
                ),
                'team_limit' => array(
                    'name' => __( 'Team Limit', 'peerraiser' ),
                    'id'   => '_peerraiser_team_limit',
                    'type' => 'text_small',
                    'attributes' => array(
                        'type' => 'number',
                        'pattern' => '\d*',
                    ),
                    'label_cb' => array(self::get_instance(), 'custom_label'),
                    'options'  => array(
                        'tooltip-class'   => 'fa-question-circle',
                        'tooltip'         => __('Enter the max number of teams that can be formed. Leave blank for unlimited.', 'peerraiser' ),
                    ),
                ),
                'thank_you_page' =>   array(
                    'name'              => 'Thank You Page',
                    'id'                => '_thank_you_page',
                    'type'              => 'select',
                    'options'           => array(self::get_instance(), 'get_selected_post'),
                    'attributes'        => array(
                        'data-tooltip' => __( 'The page people will see after making a donation.', 'peerraiser' ),
                    ),
                ),
                'campaign_participants' => array(
                    'name'              => 'Participants',
                    'id'                => '_campaign_participants',
                    'type'              => 'pr_multiselect',
                    'options'           => array(__CLASS__, 'get_participants_for_select_field'),
                    'attributes'        => array(
                        'data-tooltip' => __( 'The participants involved in this campaign', 'peerraiser' ),
                    ),
                )
                // 'registration_donation_ask' => array(
                //     'name'    => __('Ask for Registration Donation?', 'peerraiser'),
                //     'id'      => '_peerraiser_registration_donation_ask',
                //     'type'    => 'radio_inline',
                //     'options' => array(
                //         'yes' => __( 'Yes', 'peerraiser' ),
                //         'no'  => __( 'No', 'peerraiser' ),
                //     ),
                // ),
                // 'registration_donation_required' => array(
                //     'name'    => __('Registration donation required?', 'peerraiser'),
                //     'id'      => '_peerraiser_registration_donation_required',
                //     'type'    => 'radio_inline',
                //     'options' => array(
                //         'yes' => __( 'Yes', 'peerraiser' ),
                //         'no'  => __( 'No', 'peerraiser' ),
                //     ),
                // )
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

        // var_dump($field_args);

        $label = $field_args['name'];

        if ( $field_args['options']['tooltip'] ) {
            $label .= sprintf( '<span class="pr_tooltip"><i class="pr_icon fa %s"></i><span class="pr_tip">%s</span></span>', $field_args['options'][ 'tooltip-class' ], $field_args['options'][ 'tooltip' ]);
        }

        return $label;
    }


    public static function get_selected_post( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) && $field->value !== '' ) {
            $post = get_post($field->value);
            $results[$field->value] = get_the_title( $post );
        }

        return $results;

    }


    public static function get_participants_for_select_field( $field ) {
        // Empty array to fill with participants
        $results = array();

        $values = ( !empty($field->value) ) ? $field->value : array();

        foreach ($values as $key => $value) {
            $user_info = get_userdata($value);
            $results[$value] = $user_info->display_name;
        }

        return $results;
    }

    private static function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }

}