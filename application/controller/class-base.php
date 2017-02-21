<?php

namespace PeerRaiser\Controller;

/**
 *  Base controller.
 */
class Base extends \PeerRaiser\Core\View {
    /**
     * @param    \PeerRaiser\Model\Config    $config
     *
     * @return    \PeerRaiser\Core\View
     */
    public function __construct( $config = null ) {
        parent::__construct( $config );
    }

}
