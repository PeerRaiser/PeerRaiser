<?php

namespace PeerRaiser\Model\Admin;

class Donations extends \PeerRaiser\Model\Admin {

    private static $fields = array();
    private static $instance = null;

    public function __construct() {}

    /**
     * Singleton to get only one Donations model
     *
     * @return    \PeerRaiser\Model\Admin\Donations
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
            self::$fields = array(
                array(
                    'title'    => __('Offline Donation', 'peerraiser'),
                    'id'       => 'offline-donation',
                    'context'  => 'normal',
                    'priority' => 'default',
                    'fields'   => array(
                        'donor' => array(
                            'name'    => __('Donor', 'peerraiser'),
                            'desc'    => __('The donor record this donation is tied to (required)'),
                            'id'      => '_donor',
                            'type'    => 'select',
                            'default' => 'custom',
                            'options' => array(self::get_instance(), 'get_selected_post'),
                            'attributes'  => array(
                                'required' => 'required'
                            ),
                        ),
                        'donation_amount' => array(
                            'name'         => __('Donation Amount', 'peerraiser'),
                            'id'           => '_donation_amount',
                            'type'         => 'text_money',
                            'before_field' => self::get_currency_symbol(),
                            'attributes'  => array(
                                'required' => 'required'
                            ),
                        ),
                        'campaign' => array(
                            'name'    => __('Campaign', 'peerraiser'),
                            'desc'    => __('The campaign should this donation be attributed to (required)'),
                            'id'      => '_campaign',
                            'type'    => 'select',
                            'default' => 'custom',
                            'options' => array(self::get_instance(), 'get_selected_post'),
                            'attributes'  => array(
                                'required' => 'required'
                            ),
                        ),
                        'fundraiser' => array(
                            'name'    => __('Fundraiser', 'peerraiser'),
                            'desc'    => __('The fundraiser this donation is attributed to (optional)'),
                            'id'      => '_fundraiser',
                            'type'    => 'select',
                            'default' => 'custom',
                            'options' => array(self::get_instance(), 'get_selected_post'),
                            'attributes'  => array(
                                'disabled' => 'disabled'
                            ),
                        ),
                        'team' => array(
                            'name'    => __('Team', 'peerraiser'),
                            'desc'    => __('The team this donation is attributed to (optional)'),
                            'id'      => '_team',
                            'type'    => 'select',
                            'default' => 'custom',
                            'options' => array(self::get_instance(), 'get_selected_post'),
                            'attributes'  => array(
                                'disabled' => 'disabled'
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


    public static function get_selected_post( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) && $field->value !== '' ) {
            $post = get_post($field->value);
            $results[$field->value] = get_the_title( $post );
        }

        return $results;
    }

    private static function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }


}