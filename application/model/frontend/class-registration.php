<?php

namespace PeerRaiser\Model\Frontend;

use PeerRaiser\Model\Admin\Admin;

class Registration extends Admin {

	public function get_registration_choices( $campaign ) {
		$all_choices = array(
			'individual' => __('Individual', 'peerraiser' ),
			'join-team'  => __('Join a Team', 'peerraiser' ),
			'start-team' => __('Start a Team', 'peerraiser' ),
		);

		return $all_choices;
	}

}

