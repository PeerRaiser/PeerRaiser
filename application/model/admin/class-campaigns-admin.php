<?php

namespace PeerRaiser\Model\Admin;

class Campaigns_Admin extends Admin {

    protected $fields = array();

    public function __construct() {
        $this->fields = array(
            array(
                'title'    => __('Campaign Options', 'peerraiser'),
                'id'       => 'peerraiser-campaign',
                'fields'   => array(
                    'start_date' => array(
	                    'name'        => __( 'Start Date', 'peerraiser' ),
	                    'id'          => '_peerraiser_start_date',
	                    'type'        => 'text_date',
	                    'attributes'  => array(
		                    'data-tooltip' => __( 'When people can start registering for this campaign. Leave blank if the campaign starts when you click the Publish button.', 'peerraiser' ),
	                    ),
	                    'default_cb'  => array( $this, 'get_field_value' ),
	                    'date_format' => apply_filters( 'peerraiser_date_field_format', 'm/d/Y' ),
                    ),
                    'start_time' => array(
                    	'name' => __( 'Start Time', 'peerraiser' ),
	                    'id' => '_peerraiser_start_time',
	                    'type' => 'text_time',
	                    'default_cb'  => array( $this, 'get_field_value' ),
	                    'time_format' => apply_filters( 'peerraiser_time_field_format', 'g:i a' ),
	                    'attributes'  => array(
		                    'data-timepicker' => json_encode( array(
			                    'stepMinute' => 1,
		                    ) ),
	                    ),
                    ),
                    'end_date' =>   array(
	                    'name'        => __( 'End Date', 'peerraiser' ),
	                    'id'          => '_peerraiser_end_date',
	                    'type'        => 'text_date',
	                    'attributes'  => array(
		                    'data-tooltip' => __( 'The registration deadline. Leave blank if the campaign is ongoing.', 'peerraiser' ),
		                    'placeholder'  => '&infin;',
	                    ),
	                    'default_cb'  => array( $this, 'get_field_value' ),
	                    'date_format' => apply_filters( 'peerraiser_date_field_format', 'm/d/Y' ),
                    ),
                    'end_time' => array(
	                    'name' => __( 'End Time', 'peerraiser' ),
	                    'id' => '_peerraiser_end_time',
	                    'type' => 'text_time',
	                    'attributes'  => array(
		                    'placeholder'  => '&infin;',
		                    'data-timepicker' => json_encode( array(
			                    'stepMinute' => 1,
		                    ) ),
	                    ),
	                    'default_cb'  => array( $this, 'get_field_value' ),
	                    'time_format' => apply_filters( 'peerraiser_time_field_format', 'g:i a' ),
                    ),
                    'timezone' => array(
                    	'name' => __( 'Timezone', 'peerraiser' ),
	                    'id'   => '_peerraiser_timezone',
	                    'type' => 'select_timezone',
	                    'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'campaign_description' => array(
                        'name'    => __('Campaign Description', 'peerraiser'),
                        'id'      => '_peerraiser_campaign_description',
                        'type'    => 'wysiwyg',
                        'options' => array(),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'banner_image' => array(
                        'name'    => __('Campaign Banner Image', 'peerraiser'),
                        'id'      => '_peerraiser_banner_image',
                        'type'    => 'file',
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'thumbnail_image' => array(
                        'name'    => __('Campaign Thumbnail Image', 'peerraiser'),
                        'id'      => '_peerraiser_thumbnail_image',
                        'type'    => 'file',
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),
                        'attributes'        => array(
                            'data-tooltip' => __('A square image at least 150x150 pixels works best', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'campaign_goal' => array(
                        'name' => __('Campaign Goal', 'peerraiser'),
                        'id'   => '_peerraiser_campaign_goal',
                        'desc' => __( 'Format should be XXXX.XX', 'peerraiser' ),
                        'type' => 'text',
                        'attributes' => array(
                            'data-rule-required' => "true",
                            'data-msg-required' => __( 'Campaign Goal is required', 'peerraiser' ),
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                            'data-tooltip' => __('The total goal amount for the entire campaign.', 'peerraiser' ),
                        ),
                        'before_field' => $this->get_currency_symbol(),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'registration_limit' => array(
                        'name' => __( 'Registration Limit', 'peerraiser' ),
                        'id'   => '_peerraiser_registration_limit',
                        'type' => 'text_small',
                        'attributes' => array(
                            'type' => 'number',
                        ),
                        'attributes'        => array(
                            'placeholder' => '&infin;',
                            'data-rule-min' => 1,
                            'data-rule-integer' => true,
                            'data-msg-min' => __( 'Please enter a positive number, or leave blank for unlimited', 'peerraiser' ),
                            'data-msg-integer' => __( 'Please enter a whole number', 'peerraiser' ),
                            'data-tooltip' => __('Enter the max number of participants that can register. Leave blank for unlimited.', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'team_limit' => array(
                        'name' => __( 'Team Limit', 'peerraiser' ),
                        'id'   => '_peerraiser_team_limit',
                        'type' => 'text_small',
                        'attributes' => array(
                            'type' => 'number',
                        ),
                        'attributes'        => array(
                            'placeholder' => '&infin;',
                            'data-rule-min' => 1,
                            'data-rule-integer' => true,
                            'data-msg-min' => __( 'Please enter a positive number, or leave blank for unlimited', 'peerraiser' ),
                            'data-msg-integer' => __( 'Please enter a whole number', 'peerraiser' ),
                            'data-tooltip' => __('Enter the max number of teams that can be formed. Leave blank for unlimited.', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                ),
            ),
            array(
            	'title' => __( 'Fundraising Page Options', 'peerraiser' ),
	            'id' => 'peerraiser-campaign-fundraiser-options',
	            'context' => 'normal',
	            'priority' => 'default',
	            'fields' => array(
		            'suggested_individual_goal' => array(
			            'name' => __('Suggested Individual Goal', 'peerraiser'),
			            'id'   => '_peerraiser_suggested_individual_goal',
			            'desc' => __( 'Format should be XXXX.XX', 'peerraiser' ),
			            'type' => 'text',
			            'attributes' => array(
				            'data-rule-required' => "true",
				            'data-msg-required' => __( 'Individual Goal is required', 'peerraiser' ),
				            'data-rule-currency' => '["",false]',
				            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
				            'data-tooltip' => __('The amount to display as a fundraising target to participants.', 'peerraiser' ),
			            ),
			            'before_field' => $this->get_currency_symbol(),
			            'default_cb' => array( $this, 'get_field_value'),
		            ),
		            'default_fundraiser_title' => array(
		            	'name' => __( 'Default Fundraiser Title', 'peerraiser' ),
			            'id' => '_peerraiser_default_fundraiser_title',
			            'type' => 'text',
			            'attributes' => array(
				            'data-rule-required' => "true",
				            'data-msg-required' => __( 'Default fundraiser title is required', 'peerraiser' ),
				            'data-tooltip' => __('The fundraiser title if participant leaves it blank', 'peerraiser' ),
			            ),
			            'default_cb' => array( $this, 'get_field_value'),
		            ),
		            'default_fundraiser_content' => array(
		            	'name' => __( 'Default Fundraiser Content', 'peerraiser' ),
			            'id' => '_peerraiser_default_fundraiser_content',
			            'type' => 'wysiwyg',
			            'default_cb' => array( $this, 'get_field_value'),
		            )
	            ),
            ),
	        array(
		        'title' => __( 'Team Options', 'peerraiser' ),
		        'id' => 'peerraiser-campaign-team-options',
		        'context' => 'normal',
		        'priority' => 'default',
		        'fields' => array(
			        'suggested_team_goal' => array(
				        'name' => __('Suggested Team Goal', 'peerraiser'),
				        'id'   => '_peerraiser_suggested_team_goal',
				        'desc' => __( 'Format should be XXXX.XX', 'peerraiser' ),
				        'type' => 'text',
				        'attributes' => array(
					        'data-rule-required' => "true",
					        'data-msg-required' => __( 'Suggested Team Goal is required', 'peerraiser' ),
					        'data-rule-currency' => '["",false]',
					        'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
					        'data-tooltip' => __('The amount to display as a fundraising target to team captains.', 'peerraiser' ),
				        ),
				        'before_field' => $this->get_currency_symbol(),
				        'default_cb' => array( $this, 'get_field_value'),
			        ),
			        'default_team_title' => array(
				        'name' => __( 'Default Team Title', 'peerraiser' ),
				        'id' => '_peerraiser_default_team_title',
				        'type' => 'text',
				        'attributes' => array(
					        'data-rule-required' => "true",
					        'data-msg-required' => __( 'Default team title is required', 'peerraiser' ),
					        'data-tooltip' => __('The team title if participant leaves it blank', 'peerraiser' ),
				        ),
				        'default_cb' => array( $this, 'get_field_value'),
			        ),
			        'default_team_content' => array(
				        'name' => __( 'Default Team Content', 'peerraiser' ),
				        'id' => '_peerraiser_default_team_content',
				        'type' => 'wysiwyg',
				        'default_cb' => array( $this, 'get_field_value'),
			        )
		        ),
	        ),
            array(
                'title'    => __('Donation Form', 'peerraiser'),
                'id'       => 'peerraiser-campaign-donation-form',
                'context'  => 'normal',
                'priority' => 'default',
                'fields'   => array(
                    'allow_anonymous_donations' => array(
                        'name'              => __( 'Allow Anonymous Donations', 'peerraiser' ),
                        'id'                => '_peerraiser_allow_anonymous_donations',
                        'type'              => 'select',
                        'options'           => array(
                            'true' => __('Yes', 'peerraiser'),
                            'false' => __('No', 'peerraiser'),
                        ),
                        'attributes'        => array(
                            'data-tooltip' => __( 'Should donors to this campaign have the option to remain anonymous? You will still receive their info, but it will not be displayed publicly', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'allow_comments' => array(
                        'name'              => __( 'Allow Donation Comments', 'peerraiser' ),
                        'id'                => '_peerraiser_allow_comments',
                        'type'              => 'select',
                        'options'           => array(
                            'true' => __('Yes', 'peerraiser'),
                            'false' => __('No', 'peerraiser'),
                        ),
                        'attributes'        => array(
                            'data-tooltip' => __( 'Should donors to this campaign have the option to leave a comment with their donation?', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'allow_fees_covered' => array(
                        'name'              => __( 'Ask donors to cover transaction fees?', 'peerraiser' ),
                        'id'                => '_peerraiser_allow_fees_covered',
                        'type'              => 'select',
                        'options'           => array(
                            'true' => __('Yes', 'peerraiser'),
                            'false' => __('No', 'peerraiser'),
                        ),
                        'attributes'        => array(
                            'data-tooltip' => __( 'Should donors to this campaign have the option to pay the transaction fee?', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'thank_you_page' =>   array(
                        'name'              => __('Thank You Page', 'peerraiser'),
                        'id'                => '_peerraiser_thank_you_page',
                        'type'              => 'select',
                        'options_cb'        => array( $this, 'get_selected_post'),
                        'attributes'        => array(
                            'data-tooltip' => __( 'The page people will see after making a donation.', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                ),
            ),
        );
    }

    /**
     * Get all fields
     *
     * @param    $title    The title of the field group to get. Leave blank for all groups
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public function get_fields( $title = null ) {
        return ( is_null($title) ) ? $this->fields : $this->fields[$title];
    }

	public function get_field_ids() {
		$ids = parent::get_field_ids();

		$ids['banner_image_id']    = '_peerraiser_banner_image_id';
		$ids['thumbnail_image_id'] = '_peerraiser_thumbnail_image_id';

		return $ids;
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


    public function get_selected_post( $field ) {
        $id = isset( $_GET['campaign'] ) ? $_GET['campaign'] : 0;
	    $campaign_model = new \PeerRaiser\Model\Campaign( $id );
	    $short_field = substr( $field->args['id'], 12 );

	    $results = array();

	    if ( ! empty( $campaign_model->$short_field ) ) {
            $post = get_post( $campaign_model->$short_field );
            $results[ $campaign_model->$short_field ] = get_the_title( $post );
        } else {
            $plugin_options = get_option( 'peerraiser_options', array() );
            $post = get_post( $plugin_options[ 'thank_you_page' ] );
            $results[ $plugin_options[ 'thank_you_page' ] ] = get_the_title( $post );
        }

        return $results;
    }

    public function get_participants_for_select_field( $field ) {
        // Empty array to fill with participants
        $results = array();

        $values = ( !empty($field->value) ) ? $field->value : array();

        foreach ($values as $key => $value) {
            $user_info = get_userdata($value);
            $results[$value] = $user_info->display_name;
        }

        return $results;
    }

    public function get_fundraisers( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'campaign_to_fundraiser',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    public function get_donations( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_campaign',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    public function get_teams( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'pr_team',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'campaigns_to_teams',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

	public function get_field_value( $field ) {
		$campaign_id    = isset( $_GET['campaign'] ) ? $_GET['campaign'] : 0;
		$campaign_model = new \PeerRaiser\Model\Campaign( $campaign_id );
		$short_field    = substr( $field['id'], 12 );

		switch ( $field['id'] ) {
			case '_peerraiser_default_fundraiser_title':
				$field_value = isset( $campaign_model->$short_field ) ? $campaign_model->$short_field : sprintf( __( 'Help Me Support %s!', 'peerraiser'), get_bloginfo( 'name') );
				break;
			case '_peerraiser_default_fundraiser_content':
				$field_value = isset( $campaign_model->$short_field ) ? $campaign_model->$short_field : sprintf( __( "<h2>Thanks for visiting my fundraising page!</h2><p>Please help me support %s by making a donation through this page. The process is easy and secure. Don't forget to share this page on Facebook and Twitter!</p><p>Thank you for supporting this important cause!</p>", 'peerraiser'), get_bloginfo( 'name') );
				break;
			case '_peerraiser_default_team_title':
				$field_value = isset( $campaign_model->$short_field ) ? $campaign_model->$short_field : sprintf( __( 'Help Us Support %s!', 'peerraiser'), get_bloginfo( 'name') );
				break;
			case '_peerraiser_default_team_content':
				$field_value = isset( $campaign_model->$short_field ) ? $campaign_model->$short_field : sprintf( __( "<h2>Welcome to our team page!</h2><p>Please help us support %s by making a donation to our team. The process is easy and secure. Don't forget to share this page on Facebook and Twitter!</p><p>Thank you for supporting this important cause!</p>", 'peerraiser'), get_bloginfo( 'name') );
				break;
			default:
				$field_value = isset( $campaign_model->$short_field ) ? $campaign_model->$short_field : '';
				break;
		}

		return $field_value;
	}

	public function get_campaign_statuses() {
    	$default_campaign_statuses = array(
    		'active' => __( 'Active', 'peerraiser' ),
		    'private' => __( 'Private', 'peerraiser' ),
			'ended' => __( 'Ended', 'peerraiser' ),
	    );

    	return apply_filters( 'peerraiser_campaign_statuses', $default_campaign_statuses );
	}

	/**
	 * Get the campaign status label by its key
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function get_campaign_status_by_key( $key ) {
		$campaign_statuses = $this->get_campaign_statuses();

		return isset( $campaign_statuses[$key] ) ? $campaign_statuses[$key] : reset( $campaign_statuses );
	}
}
