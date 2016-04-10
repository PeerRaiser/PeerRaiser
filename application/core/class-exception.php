<?php

namespace PeerRaiser\Core;

/**
 * PeerRaiser core exception.
 */
class Exception extends \Exception {
    /**
     * Context
     * @var
     */
    protected $context;


    /**
     * Get context
     * @return    mixed
     */
    public function getContext() {
        return $this->context;
    }


    /**
     * Set context
     * @param     array    $data
     * @return    void
     */
    public function setContext( array $data = array() ) {
        $this->context = $data;
    }

}
