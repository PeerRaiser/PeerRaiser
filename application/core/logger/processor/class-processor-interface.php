<?php

namespace PeerRaiser\Core\Logger\Processor;

/**
 * PeerRaiser core logger processor interface.
 */
interface Processor_Interface {

    /**
    * @param  array $record
    *
    * @return array $record
    */
    public function process( array $record );

}
