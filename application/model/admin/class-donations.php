<?php

namespace PeerRaiser\Model\Admin;

class Donations extends \PeerRaiser\Model\Admin {

    private $fields = array();

    public function __construct() {
        $this->fields = array(
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
                        'options' => array( $this, 'get_selected_post' ),
                        'attributes'  => array(
                            'required' => 'required'
                        ),
                    ),
                    'donation_amount' => array(
                        'name'         => __( 'Donation Amount', 'peerraiser'),
                        'id'           => '_donation_amount',
                        'type'         => 'text',
                        'attributes' => array(
                            'pattern' => '^\d*(\.\d{2}$)?',
                            'title'   => __( 'No commas. Cents (.##) are optional', 'peerraiser'),
                            'required' => 'required'
                        ),
                        'before_field' => $this->get_currency_symbol(),
                    ),
                    'campaign' => array(
                        'name'    => __( 'Campaign', 'peerraiser' ),
                        'desc'    => __( 'The campaign should this donation be attributed to (required)' ),
                        'id'      => '_campaign',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options' => array( $this, 'get_selected_post' ),
                        'attributes'  => array(
                            'required' => 'required'
                        ),
                    ),
                    'fundraiser' => array(
                        'name'    => __( 'Fundraiser', 'peerraiser'),
                        'desc'    => __( 'The fundraiser this donation is attributed to (optional)' ),
                        'id'      => '_fundraiser',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options' => array( $this, 'get_selected_post' ),
                        'attributes'  => array(
                            'disabled' => 'disabled'
                        ),
                    ),
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

    public function custom_label( $field_args, $field ) {

        $label = $field_args['name'];

        if ( $field_args['options']['tooltip'] ) {
            $label .= sprintf( '<span class="pr_tooltip"><i class="pr_icon fa %s"></i><span class="pr_tip">%s</span></span>', $field_args['options'][ 'tooltip-class' ], $field_args['options'][ 'tooltip' ]);
        }

        return $label;
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

    private function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }


}