<?php

namespace PeerRaiser\Model\Admin;

class Donations_Admin extends Admin {

    protected $fields = array();

    public function __construct() {
        $this->fields = array(
            array(
                'title'    => __('Offline Donation', 'peerraiser'),
                'id'       => 'peerraiser-offline-donation',
                'context'  => 'normal',
                'priority' => 'default',
                'fields'   => array(
                    'donor' => array(
                        'name'    => __('Donor', 'peerraiser'),
                        'desc'    => __('The donor record this donation is tied to', 'peerraiser'),
                        'id'      => 'donor',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options_cb' => array( $this, 'get_selected_post' ),
                        'attributes'  => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A donor record is required', 'peerraiser' ),
                        ),
                    ),
                    // 'donor_name' => array(
                    // 	'name' => __( 'Donor Name', 'peerraiser' ),
                     //    'desc' => __( 'The public name of the donor. Leave blank if anonymous.', 'peerraiser' ),
                     //    'id' => 'donor_name',
                     //    'type' => 'text',
                    // ),
                    'donation_amount' => array(
                        'name'         => __( 'Donation Amount', 'peerraiser'),
                        'desc'         => __( 'Format should be XXXX.XX', 'peerraiser'),
                        'id'           => 'donation_amount',
                        'type'         => 'text',
                        'attributes' => array(
                            'data-rule-required' => "true",
                            'data-msg-required' => __( 'Donation amount is required', 'peerraiser' ),
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                        ),
                        'before_field' => $this->get_currency_symbol(),
                    ),
                    'campaign' => array(
                        'name'    => __( 'Campaign', 'peerraiser' ),
                        'desc'    => __( 'The campaign this donation should be attributed to', 'peerraiser' ),
                        'id'      => 'campaign',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options_cb' => array( $this, 'get_selected_post' ),
                        'attributes'  => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A campaign is required', 'peerraiser' ),
                        ),
                    ),
                    'fundraiser' => array(
                        'name'    => __( 'Fundraiser', 'peerraiser'),
                        'desc'    => __( 'The fundraiser this donation is attributed to (optional)' ),
                        'id'      => 'fundraiser',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options_cb' => array( $this, 'get_selected_post' ),
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
}
