<?php

namespace PeerRaiser\Controller\Frontend;

class Template extends \PeerRaiser\Controller\Base {

	public function register_actions() {
		add_filter( 'archive_template', array( $this, 'load_campaign_template' ) );
	}

	public function load_campaign_template( $template ) {
		$term = get_queried_object();

		// If this isn't the peerraiser campaign, return default template
		if ( $term->taxonomy !== "peerraiser_campaign" ) {
			return $template;
		}

		$template_file = 'template-peerraiser-campaign.php';

		if ( $theme_file = locate_template( array ( $template_file ) ) ) {
			$template = $theme_file;
		} else {
			$template = PEERRAISER_PATH . 'views/frontend/' . $template_file;
		}

		return $template;
	}
}