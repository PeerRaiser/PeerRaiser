<?php

namespace PeerRaiser\Core\Exception;

class Invalid_Incoming_Data extends \PeerRaiser\Core\Exception {

    public function __construct( $param = '', $message = '' ) {
        if ( ! $message ) {
            $message = sprintf( __( '"%s" param missed or has incorrect value', 'peerraiser' ), $param );
        }
        parent::__construct( $message );
    }

}