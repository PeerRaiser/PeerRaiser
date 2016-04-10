<?php

namespace PeerRaiser\Core\Logger\Handler;

/**
 * Do nothing with log data.
  */
class Null extends Handler_Abstract {

    /**
     * @param    integer    $level    The minimum logging level at which this handler will be triggered
     */
    public function __construct( $level = \PeerRaiser\Core\Logger::DEBUG ) {
        parent::__construct( $level );
    }

    /**
     * To handle record or not
     *
     * @param     array    record data
     *
     * @return    bool
     */
    public function handle( array $record ) {
        if ( $record['level'] < $this->level ) {
            return false;
        }

        return true;
    }

}
