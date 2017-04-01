<?php

namespace PeerRaiser\Model\Admin;

class Fundraisers extends \PeerRaiser\Model\Admin {

    private $fields = array();

    public function __construct() {
        $this->fields = array(
            array(
                'title'    => __('Fundraiser Info', 'peerraiser'),
                'id'       => 'peerraiser-fundraiser',
                'context'  => 'normal',
                'priority' => 'default',
                'fields'   => array(
                    'fundraiser_campaign' => array(
                        'name'    => __('Campaign', 'peerraiser'),
                        'id'      => '_peerraiser_fundraiser_campaign',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options' => array( $this, 'get_selected_post'),
                        'attributes'  => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A campaign is required', 'peerraiser' ),
                        ),
                    ),
                    'fundraiser_participant' => array(
                        'name'    => __('Participant', 'peerraiser'),
                        'id'      => '_peerraiser_fundraiser_participant',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options' => array( $this, 'get_participants_for_select_field'),
                        'attributes'  => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A participant is required', 'peerraiser' ),
                        ),
                    ),
                    'fundraiser_team' => array(
                        'name'    => __('Team', 'peerraiser'),
                        'id'      => '_peerraiser_fundraiser_team',
                        'type'    => 'select',
                        'default' => 'custom',
                        'options' => array( $this, 'get_selected_post'),
                    ),
                    'fundraiser_goal' => array(
                        'name'         => __( 'Fundraising Goal', 'peerraiser'),
                        'id'           => '_peerraiser_fundraiser_goal',
                        'type'         => 'text',
                        'attributes' => array(
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A goal amount is required', 'peerraiser' ),
                        ),
                        'before_field' => $this->get_currency_symbol(),
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

    /**
     * Get posts for CMB2 Select fields
     *
     * @since     1.0.0
     * @param     CMB2_Field    $field    The CMB2 field object
     * @return    array                   An array of posts
     */
    public function get_posts_for_select_field( $field ) {

        switch ( $field->args['name'] ) {
            case 'Campaign':
            case 'Campaigns':
                $post_type = 'pr_campaign';
                break;
            case 'Team':
            case 'Teams':
                $post_type = 'pr_team';
                break;
            case 'Fundraiser':
            case 'Fundraisers':
                $post_type = 'fundraiser';
                break;
            default:
                $post_type = 'post';
                break;
        }

        // Empty array to fill with posts
        $results = array();

        // WP_Query arguments
        $args = array (
            'post_type'              => array( $post_type ),
            'posts_per_page'         => '-1'
        );

        // The Query
        $query = new \WP_Query( $args );
        $posts = $query->get_posts();

        foreach($posts as $post) {
            $title = '(ID: ' . $post->ID .') '. $post->post_title;
            $results[$post->ID] = $title;
        }

        return $results;
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


    public function get_participants_for_select_field( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) ) {
            $user_info = get_userdata($field->value);
            if ( $user_info ) {
                $results[$field->value] = $user_info->display_name;
            }
        }

        return $results;
    }


    public function get_donations( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_fundraiser',
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