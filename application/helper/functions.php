<?php
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

function peerraiser_get_team( $id ) {
    if ( is_null( $id ) ) {
        return false;
    }

    $team_model = new \PeerRaiser\Model\Team( $id );
    return $team_model;
}

function peerraiser_get_campaign( $id ) {
    if ( is_null( $id ) ) {
        return false;
    }

    $campaign_model = new \PeerRaiser\Model\Campaign( $id );

    return $campaign_model;
}

function peerraiser_get_fundraiser( $id ) {
    if ( is_null ( $id ) ) {
        return false;
    }

    $fundraiser_model = new \PeerRaiser\Model\Fundraiser( $id );

    return $fundraiser_model;
}

function peerraiser_money_format( $amount, $with_symbol = true  ) {
    $currency_model  = new \PeerRaiser\Model\Currency();
    $plugin_options  = get_option( 'peerraiser_options', array() );

    $currency          = $plugin_options['currency'];
    $thousands_sep     = $plugin_options['thousands_separator'];
    $currency_position = $plugin_options['currency_position'];
    $decimal_sep       = $plugin_options['decimal_separator'];
    $number_decimals   = $plugin_options['number_decimals'];
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

function peerraiser_get_top_donors( $count = 20, $args = array() ) {
    $donor = new PeerRaiser\Model\Donor();
    return $donor->get_top_donors( $count, $args );
}

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

function peerraiser_get_top_teams( $count = 20, $args = array() ) {
	$team = new PeerRaiser\Model\Team();

	if ( isset( $args['campaign_id'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'peerraiser_campaign',
				'field'    => 'id',
				'terms'    => $args['campaign_id'],
			),
		);

		unset( $args['campaign_id'] );
	}

	return $team->get_top_teams( $count, $args );
}

function peerraiser_get_current_campaign() {
	$queried_object = get_queried_object();

	// Make sure the current queried object is a peerraiser campaign
	if ( ! is_a( $queried_object, 'WP_Term' ) || $queried_object->taxonomy !== 'peerraiser_campaign' ) {
		return false;
	}

	return new \PeerRaiser\Model\Campaign( $queried_object->term_id );
}