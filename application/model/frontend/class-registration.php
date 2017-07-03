<?php

namespace PeerRaiser\Model\Frontend;

use PeerRaiser\Model\Admin\Admin;

class Registration extends Admin {

	private $fields = array();

	public function __construct() {
		$this->fields = array(
			'individual' => array(
				'fundraising_goal' => array(
					'name' => __( 'Your Fundraising Goal', 'peerraiser' ),
					'id'   => 'fundraising_goal',
					'type' => 'text',
				),
				'headline' => array(
					'name' => __( "Your Page's Headline", 'peerraiser' ),
					'id' => 'headline',
					'type' => 'text',
				),
				'body' => array(
					'name' => __( 'Your Story', 'peerraiser' ),
					'id' => 'body',
					'type' => 'wysiwyg',
					'options' => array(
						'media_buttons' => false,
						'teeny' => false,
						'tinymce' => array(
							'toolbar1' => 'bold,italic,bullist,numlist,hr,alignleft,aligncenter,alignright,alignjustify,wp_adv',
							'toolbar2' => 'formatselect,underline,strikethrough,forecolor,pastetext,removeformat',
						),
					)
				),
			),
			'start-team' => array(
				'team_name' => array(
					'name' => __( 'Your Team Name', 'peerraiser' ),
					'id'   => 'team_name',
					'type' => 'text',
				),
				'team_goal' => array(
					'name' => __( 'Team Fundraising Goal', 'peerraiser' ),
					'id'   => 'team_goal',
					'type' => 'text',
				),
				'headline' => array(
					'name' => __( "Team's Page Headline", 'peerraiser' ),
					'id' => 'headline',
					'type' => 'text',
				),
			)
		);
	}

	public function get_registration_choices( $campaign ) {
		$all_choices = array(
			'individual' => __('Individual', 'peerraiser' ),
			'join-team'  => __('Join a Team', 'peerraiser' ),
			'start-team' => __('Start a Team', 'peerraiser' ),
		);

		return $all_choices;
	}

	public function get_fields() {
		return apply_filters( 'peerraiser_registration_fields', $this->fields );
	}

}

