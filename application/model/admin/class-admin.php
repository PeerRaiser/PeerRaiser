<?php

namespace PeerRaiser\Model\Admin;

class Admin {

    protected $menu_items = array();

    public function __construct(){
        $this->menu_items = array(
            'dashboard' => array(
                'url'   => 'peerraiser-dashboard',
                'title' => __( 'Dashboard', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'campaigns' => array(
                'url'   => 'peerraiser-campaigns',
                'title' => __( 'Campaigns', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'fundraisers' => array(
                'url'   => 'edit.php?post_type=fundraiser',
                'title' => __( 'Fundraisers', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'teams' => array(
                'url'   => 'peerraiser-teams',
                'title' => __( 'Teams', 'peerraiser' ),
                'cap'   => 'activate_plugins'
            ),
            'donations' => array(
                'url'   => 'peerraiser-donations',
                'title' => __( 'Donations', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'donors' => array(
                'url'   => 'peerraiser-donors',
                'title' => __( 'Donors', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'participants' => array(
                'url'   => 'peerraiser-participants',
                'title' => __( 'Participants', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            ),
            'settings' => array(
                'url'   => 'peerraiser-settings',
                'title' => __( 'Settings', 'peerraiser' ),
                'cap'   => 'activate_plugins',
            )
        );
    }

    public function get_menu_items() {
        return apply_filters( 'peerraiser_menu_items', $this->menu_items );
    }

    public function get_required_field_ids( $group = false ) {
        $required_fields = array();

        foreach ( $this->fields as $field_group ) {
            foreach ( $field_group['fields'] as $field ) {
                if ( isset( $field['attributes']['data-rule-required'] ) ) {
                    $required_fields[] =  $field['id'];
                }
            }
        }

        return $required_fields;
    }

    public function get_field_ids() {
        $ids = array();
        foreach ( $this->fields as $field_group ) {
            $ids = array_merge( $ids, wp_list_pluck( $field_group['fields'], 'id' ) );
        }

        return $ids;
    }

    public function get_field_value( $field ) {
        if ( ! isset( $_GET['team'] ) )
            return;

        $team_model = new \PeerRaiser\Model\Team( $_GET['team'] );
        $short_field = substr( $field['id'], 12 );

        switch ( $field['id'] ) {
            default:
                $field_value = isset( $team_model->$short_field ) ? $team_model->$short_field : '';
                break;
        }

        return $field_value;
    }

    public function get_selected_post( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) && $field->value !== '' ) {
            $post = get_post($field->value);
            $results[$field->value] = get_the_title( $post );
        }

        return $results;
    }

    public function custom_label( $field_args, $field ) {
        $label = $field_args['name'];

        if ( $field_args['options']['tooltip'] ) {
            $label .= sprintf( '<span class="pr_tooltip"><i class="pr_icon fa %s"></i><span class="pr_tip">%s</span></span>', $field_args['options'][ 'tooltip-class' ], $field_args['options'][ 'tooltip' ]);
        }

        return $label;
    }

    protected function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency       = new \PeerRaiser\Model\Currency();
        $iso4217_code   = isset( $plugin_options['currency'] ) ? $plugin_options['currency'] : 'USD';

        return $currency->get_currency_symbol_by_iso4217_code( $iso4217_code );
    }

}