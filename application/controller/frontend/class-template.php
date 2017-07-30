<?php

namespace PeerRaiser\Controller\Frontend;

class Template extends \PeerRaiser\Controller\Base {

    public function register_actions() {
        add_filter( 'archive_template', array( $this, 'load_template' ) );
    }

    public function load_template( $template ) {
        $term = get_queried_object();

        if ( $term->taxonomy === 'peerraiser_campaign' ) {
            return $this->load_campaign_template( $term );
        } elseif ( $term->taxonomy === 'peerraiser_team' ) {
            return $this->load_team_template( $term );
        }

        return $template;
    }

    public function load_campaign_template( $term ) {
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

    public function load_team_template( $term ) {
        $specific_template_file = 'template-peerraiser-team-' . $term->slug . '.php';
        $general_template_file  = 'template-peerraiser-team.php';

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