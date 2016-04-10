<?php

namespace PeerRaiser\Core\Exception;

class Post_Not_Found extends \PeerRaiser\Core\Exception {

    public function __construct( $post_id = '', $message = '' ) {
        if ( ! $message ) {
            $message = sprintf( __( 'Post with id "%s" not exist', 'peerraiser' ), $post_id );
        }
        parent::__construct( $message );
    }

}