<?php

namespace PeerRaiser\Controller\Frontend;

use PeerRaiser\Controller\Base;

class Registration extends Base {

	public function register_actions() {
		add_action( 'cmb2_init', array( $this, 'register_fields') );
	}

	public function register_fields() {
		$registration_model = new \PeerRaiser\Model\Frontend\Registration();
		$fields = $registration_model->get_fields();

		foreach ( $fields as $key => $value ) {
			$metabox = new_cmb2_box( array(
				'id'           => 'peerraiser-'.$key,
				'object_types' => array( 'fundraiser' ),
				'hookup'       => false,
				'save_fields'  => false,
			) );

			foreach ( $fields[$key] as $field ) {
				$metabox->add_field( $field );
			}
		}
	}
}