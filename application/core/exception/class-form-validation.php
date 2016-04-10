<?php

namespace PeerRaiser\Core\Exception;

class Form_Validation extends \PeerRaiser\Core\Exception {

    /**
     * @param    string    $form
     * @param    array     $errors
     */
    public function __construct( $form, $errors = array() ) {
        $this->setContext( $errors );
        $message = sprintf( __( 'Form "%s" validation failed.', 'peerraiser' ), $form );
        parent::__construct( $message );
    }

}