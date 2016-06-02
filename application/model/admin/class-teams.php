<?php

namespace PeerRaiser\Model\Admin;

class Teams extends \PeerRaiser\Model\Admin {

    private static $fields = array();
    private static $instance = null;

    public function __construct() {}

    /**
     * Singleton to get only one Teams model
     *
     * @return    \PeerRaiser\Model\Admin\Teams
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
            self::$fields = array(
                array(
                    'title'    => 'Team Info',
                    'id'       => 'peerraiser-team',
                    'context'  => 'normal',
                    'priority' => 'default',
                    'fields'   => array(
                        'team_leader' => array(
                            'name'    => __('Team Leader', 'peerraiser'),
                            'id'      => '_team_leader',
                            'type'    => 'select',
                            'options' => array(__CLASS__, 'get_participants_for_select_field'),
                        ),
                        'team_campaign' => array(
                            'name'    => __('Campaign', 'peerraiser'),
                            'id'      => '_team_campaign',
                            'type'    => 'select',
                            'default' => 'custom',
                            'desc'    => __( 'Campaign can\'t be changed after Team is created.', 'peerraiser' ),
                            'options' => array(self::get_instance(), 'get_selected_post'),
                        ),
                        'goal_amount' => array(
                            'name' => __('Goal Amount', 'peerraiser'),
                            'id'   => '_goal_amount',
                            'type' => 'text',
                            'attributes' => array(
                                'pattern' => '^\d*(\.\d{2}$)?',
                                'title'   => __('No commas. Cents (.##) are optional', 'peerraiser')
                            ),
                            'before_field' => self::get_currency_symbol(),
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


    public function get_fundraisers( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'fundraiser_to_team',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }


    public function get_participants( $post_id, $paged = 1 ){
        $args = array(
            'number'  => 20,
            // 'connected_type'  => 'team_to_participants',
            // 'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_User_Query( $args );
    }

    private static function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }

}