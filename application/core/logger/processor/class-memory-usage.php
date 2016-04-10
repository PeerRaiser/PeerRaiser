<?php

namespace PeerRaiser\Core\Logger\Processor;

/**
 * Injects memory_get_usage in all records
 */
class Memory_Usage extends Memory implements Processor_Interface {

    /**
     * Record processor
     *
     * @param     array    record data
     *
     * @return    array    processed record
     */
    public function process( array $record ) {
        $bytes      = memory_get_usage( $this->real_usage );
        $formatted  = $this->format_bytes( $bytes );

        $record['extra'] = array_merge(
            $record['extra'],
            array( 'memory_usage' => $formatted, )
        );

        return $record;
    }

}