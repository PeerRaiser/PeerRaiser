<?php

namespace PeerRaiser\Model\Admin;

class Settings extends \PeerRaiser\Model\Admin {

    private static $fields = array();
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
                'show_welcome_message' => array(
                    'name'    => 'Show Welcome Message on Dashboard?',
                    'id'      => 'show_welcome_message',
                    'type'    => 'select',
                    'default' => array(__CLASS__, 'get_field_value'),
                    'options' => array(
                        'true'    => __( 'Yes', 'peerraiser' ),
                        'false'   => __( 'No', 'peerraiser' ),
                    )
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

            case 'show_welcome_message':
                $field_value = ( isset($plugin_options[$field['id']]) ) ? var_export($plugin_options[$field['id']],1) : 'true';
                break;

            default:
                $field_value = '';
                break;
        }

        return $field_value;

    }


    public static function get_field_names(){
        return array_keys( self::$fields );
    }

}