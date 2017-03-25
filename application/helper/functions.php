<?php
function peerraiser_get_team( $id ) {
    if ( is_null( $id ) ) {
        return false;
    }

    $team_model = new \PeerRaiser\Model\Team();
}

function peerraiser_get_campaign( $id ) {
    if ( is_null( $id ) ) {
        return false;
    }

    $campaign_model = new \PeerRaiser\Model\Campaign( $id );

    return $campaign_model;
}

function peerraiser_money_format( $amount ) {
    
}