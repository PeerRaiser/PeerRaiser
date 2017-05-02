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

function peerraiser_get_top_donors( $count ) {
    $donor = new PeerRaiser\Model\Donor();
    return $donor->get_top_donors( $count );
}