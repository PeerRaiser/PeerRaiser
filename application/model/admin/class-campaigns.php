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
                array(
                    'title'    => __('Campaign Options', 'peerraiser'),
                    'id'       => 'peerraiser-campaign',
                    'context'  => 'normal',
                    'priority' => 'default',
                    'fields'   => array(
                        'campaign_start_date' => array(
                            'name'     => __( 'Start Date', 'peerraiser' ),
                            'id'       => '_peerraiser_campaign_start_date',
                            'type'     => 'text_date_timestamp',
                            'attributes'        => array(
                                'data-tooltip' => __('Leave blank if the campaign starts when you click the Publish button.', 'peerraiser' ),
                            ),
                        ),
                        'campaign_end_date' =>   array(
                            'name' => __( 'End Date', 'peerraiser' ),
                            'id'   => '_peerraiser_campaign_end_date',
                            'type' => 'text_date_timestamp',
                            'attributes'        => array(
                                'data-tooltip' => __('Leave blank if the campaign is ongoing.', 'peerraiser' ),
                                'placeholder' => '&infin;',
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
                            'id'   => '_peerraiser_campaign_goal',
                            'type' => 'text',
                            'attributes' => array(
                                'pattern' => '^\d*(\.\d{2}$)?',
                                'title'   => __('No commas. Cents (.##) are optional', 'peerraiser')
                            ),
                            'before_field' => self::get_currency_symbol(),
                        ),
                        'individual_goal' => array(
                            'name' => __('Suggested Individual Goal', 'peerraiser'),
                            'id'   => '_peerraiser_suggested_individual_goal',
                            'type' => 'text',
                            'attributes' => array(
                                'pattern' => '^\d*(\.\d{2}$)?',
                                'title'   => __('No commas. Cents (.##) are optional', 'peerraiser'),
                                'data-tooltip' => __('The amount to display as a fundraising target to participants.', 'peerraiser' ),
                            ),
                        ),
                        'team_goal' => array(
                            'name' => __('Suggested Team Goal', 'peerraiser'),
                            'id'   => '_peerraiser_suggested_team_goal',
                            'type' => 'text',
                            'attributes' => array(
                                'pattern' => '^\d*(\.\d{2}$)?',
                                'title'   => __('No commas. Cents (.##) are optional', 'peerraiser'),
                                'data-tooltip' => __('The amount to display as a fundraising target to team captains.', 'peerraiser' ),
                            ),
                            'before_field' => self::get_currency_symbol(),
                        ),
                        'campaign_limit' => array(
                            'name' => __( 'Registration Limit', 'peerraiser' ),
                            'id'   => '_peerraiser_campaign_limit',
                            'type' => 'text_small',
                            'attributes' => array(
                                'type' => 'number',
                                'pattern' => '\d*',
                            ),
                            'attributes'        => array(
                                'data-tooltip' => __('Enter the max number of participants that can register. Leave blank for unlimited.', 'peerraiser' ),
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
                            'attributes'        => array(
                                'data-tooltip' => __('Enter the max number of teams that can be formed. Leave blank for unlimited.', 'peerraiser' ),
                            ),
                        ),
                    ),
                ),
                array(
                    'title'    => __('Donation Form', 'peerraiser'),
                    'id'       => 'peerraiser-campaign-donation-form',
                    'context'  => 'normal',
                    'priority' => 'default',
                    'fields'   => array(
                        'anonymous_donations' => array(
                            'name'              => __( 'Allow Anonymous Donations', 'peerraiser' ),
                            'id'                => '_anonymous_donations',
                            'type'              => 'select',
                            'options'           => array(
                                'true' => __('Yes', 'peerraiser'),
                                'false' => __('No', 'peerraiser'),
                            ),
                            'attributes'        => array(
                                'data-tooltip' => __( 'Should donors to this campaign have the option to remain anonymous? You will still receive their info, but it will not be displayed publicly', 'peerraiser' ),
                            ),
                        ),
                        'donation_comments' => array(
                            'name'              => __( 'Allow Donation Comments', 'peerraiser' ),
                            'id'                => '_donation_comments',
                            'type'              => 'select',
                            'options'           => array(
                                'true' => __('Yes', 'peerraiser'),
                                'false' => __('No', 'peerraiser'),
                            ),
                            'attributes'        => array(
                                'data-tooltip' => __( 'Should donors to this campaign have the option to leave a comment with their donation?', 'peerraiser' ),
                            ),
                        ),
                        'transaction_fee_option' => array(
                            'name'              => __( 'Ask donors to cover transaction fees?', 'peerraiser' ),
                            'id'                => '_transaction_fee_option',
                            'type'              => 'select',
                            'options'           => array(
                                'true' => __('Yes', 'peerraiser'),
                                'false' => __('No', 'peerraiser'),
                            ),
                            'attributes'        => array(
                                'data-tooltip' => __( 'Should donors to this campaign have the option to pay the transaction fee?', 'peerraiser' ),
                            ),
                        ),
                        'thank_you_page' =>   array(
                            'name'              => __('Thank You Page', 'peerraiser'),
                            'id'                => '_thank_you_page',
                            'type'              => 'select',
                            'options'           => array(self::get_instance(), 'get_selected_post'),
                            'attributes'        => array(
                                'data-tooltip' => __( 'The page people will see after making a donation.', 'peerraiser' ),
                            ),
                        ),
                    ),
                ),
            );
        }

        return self::$instance;
    }


    /**
     * Get all fields
     *
     * @param    $title    The title of the field group to get. Leave blank for all groups
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public static function get_fields( $title = null ) {
        return ( is_null($title) ) ? self::$fields : self::$fields[$title];
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