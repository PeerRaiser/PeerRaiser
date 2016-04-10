<?php

namespace PeerRaiser\Core\Logger\Processor;

/**
 * PeerRaiser core logger processor memory peak usage.
 */
class Memory_Peak_Usage extends \PeerRaiser\Core\Logger\Processor\Memory implements Processor_Interface {

    /**
     * Record processor
     *
     * @param     array    record data
     *
     * @return    array    processed record
     */
    public function process( array $record ) {
        $bytes      = memory_get_peak_usage( $this->real_usage );
        $formatted  = $this->format_bytes( $bytes );

        $record['extra'] = array_merge(
            $record['extra'],
            array( 'memory_peak_usage' => $formatted, )
        );

        return $record;
    }

}