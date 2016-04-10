<?php

namespace PeerRaiser\Core;

/**
 * PeerRaiser core request.
 */
class Request extends Entity {

    /**
     * POST method
     *
     * @var    string
     */
    const POST = 'POST';

    /**
     * PUT method
     *
     * @var    string
     */
    const PUT = 'PUT';

    /**
     * GET method
     *
     * @var    string
     */
    const GET = 'GET';

    /**
     * HEAD method
     *
     * @var    string
     */
    const HEAD = 'HEAD';

    /**
     * DELETE method
     *
     * @var    string
     */
    const DELETE = 'DELETE';

    /**
     * PATCH method
     *
     * @link    http://tools.ietf.org/html/rfc5789
     *
     * @var    string
     */
    const PATCH = 'PATCH';

    public function _construct() {
        parent::_construct();
        $this->set_data( 'get',     $_GET );
        $this->set_data( 'post',    $_POST );
        $this->set_data( 'cookie',  $_COOKIE );
        $this->set_data( 'server',  $_SERVER );
        $this->set_data( 'env',     $_ENV );
    }

    /**
     * Retrieve a parameter.
     *
     * Retrieves a parameter from the instance. Priority is in the order of
     * userland parameters $_GET, $_POST.
     * If a parameter matching the $key is not found, null is returned.
     *
     * @param    mixed    $key
     * @param    mixed    $default    Default value to use if key not found
     *
     * @return    mixed
     */
    public function get_param( $key, $default = null ) {
        if ( isset( $this->_data[ $key ] ) ) {
            return $this->_data[ $key ];
        } elseif ( isset( $this->_data['get'] ) && isset( $this->_data['get'][ $key ] ) ) {
            return $this->_data['get'][ $key ];
        } elseif ( isset( $this->_data['post'] ) &&  isset( $this->_data['post'][ $key ] ) ) {
            return $this->_data['get'][ $key ];
        }

        return $default;
    }

}
