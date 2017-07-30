<?php

namespace PeerRaiser\Model\Frontend;

use PeerRaiser\Model\Admin\Admin;
use PeerRaiser\Model\Campaign;
use PeerRaiser\Model\Currency;

class Registration extends Admin {

    private $fields = array();

    public function __construct() {
        $this->fields = array(
            'individual' => array(
                'fundraising_goal' => array(
                    'name' => __( 'Your Fundraising Goal', 'peerraiser' ),
                    'id'   => '_peerraiser_fundraiser_goal',
                    'type' => 'text',
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'A fundraising goal is required', 'peerraiser' ),
                    ),
                    'default_cb' => array( $this, 'get_field_value'),
                ),
                'headline_individual' => array(
                    'name' => __( "Your Page's Headline", 'peerraiser' ),
                    'id' => '_peerraiser_headline_individual',
                    'type' => 'text',
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'A page headline is required', 'peerraiser' ),
                    ),
                    'default_cb' => array( $this, 'get_field_value'),
                ),
                'image' => array(
                    'name' => __( 'Your photo', 'peerraiser' ),
                    'id' => '_peerraiser_photo',
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'file',
                    ),
                ),
                'body_individual' => array(
                    'name' => __( 'Your Story', 'peerraiser' ),
                    'id' => '_peerraiser_body_individual',
                    'type' => 'wysiwyg',
                    'options' => array(
                        'media_buttons' => false,
                        'teeny' => false,
                        'tinymce' => array(
                            'toolbar1' => 'bold,italic,bullist,numlist,hr,alignleft,aligncenter,alignright,alignjustify,wp_adv',
                            'toolbar2' => 'formatselect,underline,strikethrough,forecolor,pastetext,removeformat',
                        ),
                    ),
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'The page cannot be blank', 'peerraiser' ),
                    ),
                    'default_cb' => array( $this, 'get_field_value'),
                ),
                'peerraiser_action' => array(
                    'id' => 'peerraiser_action',
                    'type' => 'hidden',
                    'default' => 'register_individual',
                )
            ),
            'start-team' => array(
                'team_name' => array(
                    'name' => __( 'Your Team Name', 'peerraiser' ),
                    'id'   => '_peerraiser_team_name',
                    'type' => 'text',
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'A team name is required', 'peerraiser' ),
                    ),
                ),
                'team_goal' => array(
                    'name' => __( 'Team Fundraising Goal', 'peerraiser' ),
                    'id'   => '_peerraiser_team_goal',
                    'type' => 'text',
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'A fundraising goal is required', 'peerraiser' ),
                    ),
                    'default_cb' => array( $this, 'get_field_value'),
                ),
                'headline_team' => array(
                    'name' => __( "Team's Page Headline", 'peerraiser' ),
                    'id' => '_peerraiser_headline_team',
                    'type' => 'text',
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'A team headline is required', 'peerraiser' ),
                    ),
                    'default_cb' => array( $this, 'get_field_value'),
                ),
                'image' => array(
                    'name' => __( 'Team photo', 'peerraiser' ),
                    'id' => '_peerraiser_photo',
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'file',
                    ),
                ),
                'body_team' => array(
                    'name' => __( 'Team Story', 'peerraiser' ),
                    'id' => '_peerraiser_body_team',
                    'type' => 'wysiwyg',
                    'options' => array(
                        'media_buttons' => false,
                        'teeny' => false,
                        'tinymce' => array(
                            'toolbar1' => 'bold,italic,bullist,numlist,hr,alignleft,aligncenter,alignright,alignjustify,wp_adv',
                            'toolbar2' => 'formatselect,underline,strikethrough,forecolor,pastetext,removeformat',
                        ),
                    ),
                    'attributes'  => array(
                        'data-rule-required' => 'true',
                        'data-msg-required' => __( 'The page cannot be blank', 'peerraiser' ),
                    ),
                    'default_cb' => array( $this, 'get_field_value'),
                ),
                'peerraiser_action' => array(
                    'id' => 'peerraiser_action',
                    'type' => 'hidden',
                    'default' => 'register_team',
                )
            )
        );

        $this->add_currency_symbol_to_fields();
    }

    public function get_registration_choices( $campaign ) {
        $all_choices = array(
            'individual' => __('Individual', 'peerraiser' ),
            'join-team'  => __('Join a Team', 'peerraiser' ),
            'start-team' => __('Start a Team', 'peerraiser' ),
        );

        return $all_choices;
    }

    public function get_fields() {
        return apply_filters( 'peerraiser_registration_fields', $this->fields );
    }

    public function get_required_field_ids( $group = false ) {
        $required_fields = array();

        if ( ! isset( $this->fields[$group] ) ) {
            return new \WP_Error( 'unknown_field_group', __( 'The group ID passed is not a valid type.' ) );
        }

        foreach ( $this->fields[$group] as $field ) {
            if ( isset( $field['attributes']['data-rule-required'] ) ) {
                $required_fields[] =  $field['id'];
            }
        }

        return $required_fields;
    }

    public function get_field_value( $field ) {
        $campaign_slug = get_query_var( 'peerraiser_campaign' );
        $campaign_model = new Campaign();
        $campaign = $campaign_model->get_campaigns( array( 'slug' => $campaign_slug ) );

        switch ( $field['id'] ) {
            case '_peerraiser_fundraiser_goal' :
                return $campaign[0]->suggested_individual_goal;
            case '_peerraiser_team_goal' :
                return $campaign[0]->suggested_team_goal;
            case '_peerraiser_headline_individual' :
                return $campaign[0]->default_fundraiser_title;
            case '_peerraiser_headline_team' :
                return $campaign[0]->default_team_title;
            case '_peerraiser_body_individual' :
                return $campaign[0]->default_fundraiser_content;
            case '_peerraiser_body_team' :
                return $campaign[0]->default_team_content;
            default:
                return '';
        }
    }

    private function add_currency_symbol_to_fields() {
        $currency_model  = new Currency();
        $plugin_options  = get_option( 'peerraiser_options', array() );

        $currency          = $plugin_options['currency'];
        $currency_position = $plugin_options['currency_position'];
        $currency_symbol   = $currency_model->get_currency_symbol_by_iso4217_code( $currency );

        if ( $currency_position === 'before' ) {
            $this->fields['individual']['fundraising_goal']['before_field'] = sprintf( '<span class="peerraiser-fundraising-goal"><span class="peerraiser-currency-symbol">%s</span>', $currency_symbol );
            $this->fields['individual']['fundraising_goal']['after_field'] = '</span>';

            $this->fields['start-team']['team_goal']['before_field'] = sprintf( '<span class="peerraiser-fundraising-goal"><span class="peerraiser-currency-symbol">%s</span>', $currency_symbol );
            $this->fields['start-team']['team_goal']['after_field'] = '</span>';
        } else {
            $this->fields['individual']['fundraising_goal']['after_field'] = sprintf( '<span class="currency">%s</span>', $currency_symbol );
            $this->fields['start-team']['team_goal']['after_field'] = sprintf( '<span class="currency">%s</span>', $currency_symbol );
        }
    }

}

