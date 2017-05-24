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

		$specific_template_file = 'template-peerraiser-campaign-' . $term->slug . '.php';
		$general_template_file  = 'template-peerraiser-campaign.php';

		if ( $specific_theme_file = locate_template( array( $specific_template_file ) ) ) {
			$template = $specific_theme_file;
		} elseif ( $general_theme_file = locate_template( array ( $general_template_file ) ) ) {
			$template = $general_theme_file;
		} else {
			$template = PEERRAISER_PATH . 'views/frontend/' . $general_template_file;
		}

		return $template;
	}
}