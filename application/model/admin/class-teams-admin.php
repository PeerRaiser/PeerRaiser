<?php

namespace PeerRaiser\Model\Admin;

class Teams_Admin extends Admin {

	protected $fields = array();

    public function __construct() {
        $this->fields = array(
            array(
                'title'    => 'Team Info',
                'id'       => 'peerraiser-team',
                'context'  => 'normal',
                'priority' => 'default',
                'fields'   => array(
	                'team_leader' => array(
		                'name'       => __('Team Leader', 'peerraiser'),
		                'id'         => '_peerraiser_team_leader',
		                'type'       => 'select',
		                'options_cb' => array( $this, 'get_participants_for_select_field'),
		                'attributes' => array(
			                'data-rule-required' => 'true',
			                'data-msg-required' => __( 'A team leader is required', 'peerraiser' ),
		                )
	                ),
	                'campaign_id' => array(
		                'name'       => __('Campaign', 'peerraiser'),
		                'id'         => '_peerraiser_campaign_id',
		                'type'       => 'select',
		                'default'    => 'custom',
		                'options_cb' => array( $this, 'get_selected_term'),
		                'attributes' => array(
			                'data-rule-required' => 'true',
			                'data-msg-required' => __( 'A campaign is required', 'peerraiser' ),
		                )
	                ),
	                'team_goal' => array(
		                'name' => __('Goal Amount', 'peerraiser'),
		                'id'   => '_peerraiser_team_goal',
		                'type' => 'text',
		                'attributes' => array(
			                'pattern' => '^\d*(\.\d{2}$)?',
			                'title'   => __('No commas. Cents (.##) are optional', 'peerraiser')
		                ),
		                'before_field' => $this->get_currency_symbol(),
		                'attributes' => array(
			                'data-rule-currency' => '["",false]',
			                'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
			                'data-rule-required' => 'true',
			                'data-msg-required' => __( 'A goal amount is required', 'peerraiser' ),
		                ),
		                'default_cb' => array( $this, 'get_field_value'),
	                ),
                	'team_title' => array(
                		'name' => __('Headline'),
		                'id'   => '_peerraiser_team_headline',
		                'type' => 'text',
		                'default_cb' => array( $this, 'get_field_value'),
	                ),
	                'team_content' => array(
		                'name' => __('Content'),
		                'id'   => '_peerraiser_team_content',
		                'type' => 'wysiwyg',
		                'default_cb' => array( $this, 'get_field_value'),
	                ),
                    'thumbnail_image' => array(
                        'name'    => __('Team Thumbnail Image', 'peerraiser'),
                        'id'      => '_peerraiser_thumbnail_image',
                        'type'    => 'file',
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    )
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

	public function get_field_ids() {
    	$ids = parent::get_field_ids();

    	$ids['thumbnail_image_id'] = '_peerraiser_thumbnail_image_id';

    	return $ids;
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

    public function get_selected_term( $field ) {
    	if ( ! isset( $_GET['team'] ) )
    		return;

        // Empty array to fill with posts
        $results = array();
	    $team_model = new \PeerRaiser\Model\Team( $_GET['team'] );
	    $short_field = substr( $field->args['id'], 12 );

        if ( isset( $team_model->$short_field ) && $team_model->$short_field !== '' ) {
            $term = get_term( $team_model->$short_field );
            $results[$team_model->$short_field] = $term->name;
        }

        return $results;
    }

    public function get_participants_for_select_field( $field ) {
	    if ( ! isset( $_GET['team'] ) )
		    return;

    	// Empty array to fill with posts
        $results = array();

        $team_model = new \PeerRaiser\Model\Team( $_GET['team'] );
	    $short_field = substr( $field->args['id'], 12 );

        if ( isset( $team_model->$short_field ) && $team_model->$short_field !== '' ) {
            $user_info = get_userdata( $team_model->$short_field );
            if ( $user_info ) {
                $results[$team_model->$short_field] = $user_info->display_name;
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

    public function get_teams_by_campaign( $campaign, $count = false ) {
        $args = array(
            "fields"    => "ids",
            "post_type' => 'fundraiser",
            "tax_query" => array(
                "taxonomy" => "peerraiser_campaign",
                "field"    => is_int( $campaign ) ? 'id' : 'slug',
                "terms"    => $campaign
            )
        );

        $fundraiser_ids = get_posts( $args );

        return wp_get_object_terms( $fundraiser_ids, "peerraiser_team" );
    }
}
