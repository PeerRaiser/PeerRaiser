<?php
/**
 * Returns a PeerRaiser option.
 *
 * @param string $option The PeerRaiser option to retrieve
 *
 * @return mixed|WP_Error
 */
function peerraiser_get_option( $option = '' ) {
	$plugin_options = get_option( 'peerraiser_options', array() );

	if ( empty( $option ) ) {
		return $plugin_options;
	}

	if ( ! isset( $plugin_options[$option] ) ) {
		return new WP_Error( 'peerraiser-invalid-option', sprintf( __( 'Can\'t get option %s', 'peerraiser' ), $option ) );
	}

	return $plugin_options[$option];
}

/**
 * Returns 'test' if plugin is in test mode or 'live' if not in test mode
 *
 * @return string
 */
function peerraiser_get_plugin_mode() {
	return \PeerRaiser\Helper\View::get_plugin_mode();
}

/**
 * Returns true if plugin is in test mode, or false if not in test mode
 *
 * @return bool
 */
function peerraiser_is_test_mode() {
	return peerraiser_get_plugin_mode() === 'test';
}

/**
 * Returns a team by team id.
 *
 * @param int $id The ID of the team to retrieve
 *
 * @return bool|\PeerRaiser\Model\Team
 */
function peerraiser_get_team( $id ) {
    if ( is_null( $id ) ) {
        return false;
    }

    $team_model = new \PeerRaiser\Model\Team( $id );

    return $team_model;
}

/**
 * Returns a campaign by campaign id.
 *
 * @param int $id
 *
 * @return bool|\PeerRaiser\Model\Campaign
 */
function peerraiser_get_campaign( $id ) {
    if ( is_null( $id ) ) {
        return false;
    }

    $campaign_model = new \PeerRaiser\Model\Campaign( $id );

    return $campaign_model;
}

/**
 * Returns a campaign by campaign slug.
 *
 * @param string $slug Campaign slug
 *
 * @return \PeerRaiser\Model\Campaign
 */
function peerraiser_get_campaign_by_slug( $slug ) {
	$term = get_term_by( 'slug', $slug, 'peerraiser_campaign' );

	return new \PeerRaiser\Model\Campaign( $term->term_id );
}

/**
 * Returns a fundraiser by fundraiser id.
 *
 * @param int $id The fundraiser ID
 *
 * @return bool|\PeerRaiser\Model\Fundraiser
 */
function peerraiser_get_fundraiser( $id ) {
    if ( is_null ( $id ) ) {
        return false;
    }

    $fundraiser_model = new \PeerRaiser\Model\Fundraiser( $id );

    return $fundraiser_model;
}

/**
 * Returns a fundraiser by fundraiser slug.
 *
 * @param string $slug Fundraiser slug
 *
 * @return \PeerRaiser\Model\Fundraiser
 */
function peerraiser_get_fundraiser_by_slug( $slug ) {
	$fundraiser = get_page_by_path( $slug, OBJECT, 'fundraiser' );

	return new \PeerRaiser\Model\Fundraiser( $fundraiser->ID );
}

/**
 * Returns an amount in the correct format.
 *
 * @param float $amount      The number being formatted
 * @param bool  $with_symbol True if the amount should include a currency symbol
 * @param bool  $decimal     True if the amount should include decimal, False returns a whole number
 *
 * @return string
 */
function peerraiser_money_format( $amount, $with_symbol = true, $decimal = true  ) {
    $currency_model  = new \PeerRaiser\Model\Currency();
    $plugin_options  = get_option( 'peerraiser_options', array() );

    $currency          = $plugin_options['currency'];
    $thousands_sep     = $plugin_options['thousands_separator'];
    $currency_position = $plugin_options['currency_position'];
    $decimal_sep       = $plugin_options['decimal_separator'];
    $number_decimals   = $decimal ? $plugin_options['number_decimals'] : 0;
    $currency_symbol   = $currency_model->get_currency_symbol_by_iso4217_code( $currency );

    $amount = ! empty( $amount ) ? $amount : 0;

    $number_format = number_format( $amount, $number_decimals, $decimal_sep, $thousands_sep );

    if ( $with_symbol ) {
        if ( $currency_position === 'before' ) {
            return $currency_symbol . $number_format;
        } else {
            return $number_format . $currency_symbol;
        }
    } else {
        return $number_format;
    }
}

/**
 * Returns the top donors (donors that have given the most money).
 *
 * @param int $count The maximum number of donors to return
 *
 * @return array
 */
function peerraiser_get_top_donors( $count = 20 ) {
    $donor = new PeerRaiser\Model\Donor();

    return $donor->get_top_donors_to_campaign( 0, $count );
}

/**
 * Get the top donors to a specific campaign.
 *
 * @param int $id    The campaign id to get the donors for
 * @param int $count The maximum number of donors to return
 *
 * @return array
 */
function peerraiser_get_top_donors_to_campaign( $id, $count = 20 ) {
	$donor = new PeerRaiser\Model\Donor();

	return $donor->get_top_donors_to_campaign( $id, $count );
}

/**
 * Get the fundraisers associated with a specific team.
 *
 * @param int   $id      The team ID to get fundraisers for
 * @param array $options Optional arguments
 *
 * @return array|WP_Error An array of fundraiser, or error if no team found by given id
 */
function peerraiser_get_team_fundraisers( $id = 0, $options = array() ) {
    if ( empty( $id ) ) {
        $id = peerraiser_get_current_team();

        if ( empty ( $id ) ) {
            return new WP_Error( 'peerraiser_team_not_found', escape_html_e( 'Team not found.', 'peerraiser' ) );
        }
    }

    $team = new \PeerRaiser\Model\Team( $id );

    return $team->get_fundraisers( $options );
}

/**
 * Returns the top donors to a specific fundraiser.
 *
 * @param int $id    The fundraiser id to get the donors for
 * @param int $count The maximum number of donors to return
 *
 * @return array
 */
function peerraiser_get_top_donors_to_fundraiser( $id, $count = 20 ) {
	$donor = new PeerRaiser\Model\Donor();

	return $donor->get_top_donors_to_fundraiser( $id, $count );
}

/**
 * Returns the top fundraisers (fundraisers that have received the most highest total donations).
 *
 * This function can be used to get top fundraisers for a specific campaign or teams if the campaign/team id is passed
 * in the arguments. If a campaign/team isn't specified, fundraisers for all campaigns/teams will be returned.
 *
 * @param int   $count The maximum number of fundraisers to return
 * @param array $args  Optional arguments
 *
 * @return array
 */
function peerraiser_get_top_fundraisers( $count = 20, $args = array() ) {
	$fundraiser = new PeerRaiser\Model\Fundraiser();

	if ( isset( $args['campaign_id'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'peerraiser_campaign',
				'field'    => 'id',
				'terms'    => $args['campaign_id'],
			),
		);

		unset( $args['campaign_id'] );
	} elseif ( isset( $args['team_id'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'peerraiser_team',
				'field'    => 'id',
				'terms'    => $args['team_id'],
			),
		);

		unset( $args['team_id'] );
	}

	return $fundraiser->get_top_fundraisers( $count, $args );
}

/**
 * Returns the top teams (teams that have received the most highest total donations).
 *
 * This function can be used to get top teams for a specific campaign if the id is passed in the arguments. If an id
 * isn't specified, fundraisers for all campaigns will be returned.
 *
 * @param int   $count The maximum number of fundraisers to return
 * @param array $args  Optional arguments
 *
 * @return array
 */
function peerraiser_get_top_teams( $count = 20, $args = array() ) {
	$team = new PeerRaiser\Model\Team();

	if ( isset( $args['campaign_id'] ) ) {
		$args['meta_key']= '_peerraiser_campaign_id';
		$args['meta_value']= $args['campaign_id'];

		unset( $args['campaign_id'] );
	}

	return $team->get_top_teams( $count, $args );
}

/**
 * Returns the current campaign.
 *
 * This function will attempt to get the current campaign. The visitor/user must be on a campaign page for this to work.
 *
 * @return bool|\PeerRaiser\Model\Campaign
 */
function peerraiser_get_current_campaign() {
	$queried_object = get_queried_object();

	// Make sure the current queried object is a peerraiser campaign
	if ( ! is_a( $queried_object, 'WP_Term' ) || $queried_object->taxonomy !== 'peerraiser_campaign' ) {
		return false;
	}

	return new \PeerRaiser\Model\Campaign( $queried_object->term_id );
}

/**
 * Returns the current fundraiser.
 *
 * This function will attempt to get the current fundraiser. The visitor/user must be on a fundraising page for this to
 * work.
 *
 * @return bool|\PeerRaiser\Model\Fundraiser
 */
function peerraiser_get_current_fundraiser() {
	// Make sure the current queried object is a peerraiser campaign
	if ( ! is_single() || get_post_type() !== 'fundraiser' ) {
		return false;
	}

	return new \PeerRaiser\Model\Fundraiser( get_the_ID() );
}

/**
 * Returns the current team.
 *
 * This function will attempt to get the current team. The visitor/user must be on a team page for this to work.
 *
 * @return bool|\PeerRaiser\Model\Team
 */
function peerraiser_get_current_team() {
	$queried_object = get_queried_object();

	// Make sure the current queried object is a peerraiser campaign
	if ( ! is_a( $queried_object, 'WP_Term' ) || $queried_object->taxonomy !== 'peerraiser_team' ) {
		return false;
	}

	return new \PeerRaiser\Model\Team( $queried_object->term_id );
}

/**
 * Returns the currency symbol.
 *
 * @return string
 */
function peerraiser_get_currency_symbol() {
	$plugin_options = get_option( 'peerraiser_options', array() );

	$currency = new \PeerRaiser\Model\Currency();

	return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
}