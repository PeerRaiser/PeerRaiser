<?php

namespace PeerRaiser\Model\Admin;

class Campaigns extends \PeerRaiser\Model\Admin {

    private $fields = array();

    public function __construct() {
        $this->fields = array(
            array(
                'title'    => __('Campaign Options', 'peerraiser'),
                'id'       => 'peerraiser-campaign',
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
                        'name'    => __('Campaign Banner Image', 'peerraiser'),
                        'id'      => '_peerraiser_campaign_image',
                        'type'    => 'file',
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                    ),
                    'campaign_thumbnail' => array(
                        'name'    => __('Campaign Thumbnail Image', 'peerraiser'),
                        'id'      => '_peerraiser_campaign_thumbnail',
                        'type'    => 'file',
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                        'attributes'        => array(
                            'data-tooltip' => __('A square image at least 150x150 pixels works best', 'peerraiser' ),
                        ),
                    ),
                    'campaign_goal' => array(
                        'name' => __('Campaign Goal', 'peerraiser'),
                        'id'   => '_peerraiser_campaign_goal',
                        'desc' => __( 'Format should be XXXX.XX', 'peerraiser' ),
                        'type' => 'text',
                        'attributes' => array(
                            'data-rule-required' => "true",
                            'data-msg-required' => __( 'Campaign Goal is required', 'peerraiser' ),
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                        ),
                        'before_field' => $this->get_currency_symbol(),
                    ),
                    'individual_goal' => array(
                        'name' => __('Suggested Individual Goal', 'peerraiser'),
                        'id'   => '_peerraiser_suggested_individual_goal',
                        'desc' => __( 'Format should be XXXX.XX', 'peerraiser' ),
                        'type' => 'text',
                        'attributes' => array(
                            'data-rule-required' => "true",
                            'data-msg-required' => __( 'Individual Goal is required', 'peerraiser' ),
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                            'data-tooltip' => __('The amount to display as a fundraising target to participants.', 'peerraiser' ),
                        ),
                        'before_field' => $this->get_currency_symbol(),
                    ),
                    'team_goal' => array(
                        'name' => __('Suggested Team Goal', 'peerraiser'),
                        'id'   => '_peerraiser_suggested_team_goal',
                        'desc' => __( 'Format should be XXXX.XX', 'peerraiser' ),
                        'type' => 'text',
                        'attributes' => array(
                            'data-rule-required' => "true",
                            'data-msg-required' => __( 'Suggested Team Goal is required', 'peerraiser' ),
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                            'data-tooltip' => __('The amount to display as a fundraising target to team captains.', 'peerraiser' ),
                        ),
                        'before_field' => $this->get_currency_symbol(),
                    ),
                    'campaign_limit' => array(
                        'name' => __( 'Registration Limit', 'peerraiser' ),
                        'id'   => '_peerraiser_campaign_limit',
                        'type' => 'text_small',
                        'attributes' => array(
                            'type' => 'number',
                        ),
                        'attributes'        => array(
                            'placeholder' => '&infin;',
                            'data-rule-min' => 1,
                            'data-rule-integer' => true,
                            'data-msg-min' => __( 'Please enter a positive number, or leave blank for unlimited', 'peerraiser' ),
                            'data-msg-integer' => __( 'Please enter a whole number', 'peerraiser' ),
                            'data-tooltip' => __('Enter the max number of participants that can register. Leave blank for unlimited.', 'peerraiser' ),
                        ),
                    ),
                    'team_limit' => array(
                        'name' => __( 'Team Limit', 'peerraiser' ),
                        'id'   => '_peerraiser_team_limit',
                        'type' => 'text_small',
                        'attributes' => array(
                            'type' => 'number',
                        ),
                        'attributes'        => array(
                            'placeholder' => '&infin;',
                            'data-rule-min' => 1,
                            'data-rule-integer' => true,
                            'data-msg-min' => __( 'Please enter a positive number, or leave blank for unlimited', 'peerraiser' ),
                            'data-msg-integer' => __( 'Please enter a whole number', 'peerraiser' ),
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
                        'id'                => '_peerraiser_anonymous_donations',
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
                        'id'                => '_peerraiser_donation_comments',
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
                        'id'                => '_peerraiser_transaction_fee_option',
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
                        'id'                => '_peerraiser_thank_you_page',
                        'type'              => 'select',
                        'options'           => array( $this, 'get_selected_post'),
                        'attributes'        => array(
                            'data-tooltip' => __( 'The page people will see after making a donation.', 'peerraiser' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Get all fields
     *
     * @param    $title    The title of the field group to get. Leave blank for all groups
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public function get_fields( $title = null ) {
        return ( is_null($title) ) ? $this->fields : $this->fields[$title];
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


    public function get_selected_post( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) && $field->value !== '' ) {
            $post = get_post($field->value);
            $results[$field->value] = get_the_title( $post );
        } else {
            $plugin_options = get_option( 'peerraiser_options', array() );
            $post = get_post( $plugin_options[ 'thank_you_page' ] );
            $results[ $plugin_options[ 'thank_you_page' ] ] = get_the_title( $post );
        }

        return $results;

    }


    public function get_participants_for_select_field( $field ) {
        // Empty array to fill with participants
        $results = array();

        $values = ( !empty($field->value) ) ? $field->value : array();

        foreach ($values as $key => $value) {
            $user_info = get_userdata($value);
            $results[$value] = $user_info->display_name;
        }

        return $results;
    }

    public function get_fundraisers( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'campaign_to_fundraiser',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    public function get_donations( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_campaign',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    public function get_teams( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'pr_team',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'campaigns_to_teams',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    private function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }

}
