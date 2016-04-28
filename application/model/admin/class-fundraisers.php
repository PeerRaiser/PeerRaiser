<?php

namespace PeerRaiser\Model\Admin;

class Fundraisers extends \PeerRaiser\Model\Admin {

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
                array(
                    'title'    => 'Fundraiser Info',
                    'id'       => 'peerraiser-fundraiser',
                    'context'  => 'normal',
                    'priority' => 'default',
                    'fields'   => array(
                        'fundraiser_campaign' => array(
                            'name'             => 'Campaign',
                            'id'               => '_fundraiser_campaign',
                            'type'             => 'select',
                            'default'          => 'custom',
                            'options'          => array(self::get_instance(), 'get_selected_post'),
                        ),
                        'fundraiser_participant' => array(
                            'name'             => 'Participant',
                            'id'               => '_fundraiser_participant',
                            'type'             => 'select',
                            'default'          => 'custom',
                            'options'          => array(self::get_instance(), 'get_participants_for_select_field'),
                        ),
                        'fundraiser_team' => array(
                            'name'             => 'Team',
                            'id'               => '_fundraiser_team',
                            'type'             => 'select',
                            'default'          => 'custom',
                            'options'          => array(self::get_instance(), 'get_selected_post'),
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

    /**
     * Get posts for CMB2 Select fields
     *
     * @since     1.0.0
     * @param     CMB2_Field    $field    The CMB2 field object
     * @return    array                   An array of posts
     */
    public static function get_posts_for_select_field( $field ) {

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

}