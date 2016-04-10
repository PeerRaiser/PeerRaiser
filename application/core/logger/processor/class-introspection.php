<?php

namespace PeerRaiser\Core\Logger\Processor;

/**
 * PeerRaiser core logger processor introspection.
 */
class Introspection implements Processor_Interface {

    /**
     *
     * @var    int    level of records to log
     */
    private $level;

    /**
     * @var    array
     */
    private $skip_classes_partials;


    public function __construct( $level = \PeerRaiser\Core\Logger::DEBUG, array $skip_classes_partials = array() ) {
        $this->level = $level;
        $this->skip_classes_partials = $skip_classes_partials;
    }


    /**
     * Process record data
     *
     * @param     array    Record data
     *
     * @return    array    processed record
     */
    public function process( array $record ) {

        // Return, if the level is not high enough
        if ( $record['level'] < $this->level ) {
            return $record;
        }

        $trace = debug_backtrace();

        // Skip first since it's always the current method
        array_shift( $trace );
        // The call_user_func call is also skipped
        array_shift( $trace );

        $i = 0;

        while ( isset( $trace[ $i ]['class'] ) ) {
            foreach ( $this->skip_classes_partials as $part ) {
                if ( strpos( $trace[ $i ]['class'], $part ) !== false ) {
                    $i++;
                    continue 2;
                }
            }
            break;
        }

        // We should have the call source now
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'file'      => isset( $trace[ $i - 1 ]['file'] )  ? $trace[ $i - 1 ]['file']    : null,
                'line'      => isset( $trace[ $i - 1 ]['line'] )  ? $trace[ $i - 1 ]['line']    : null,
                'class'     => isset( $trace[ $i ]['class'] )     ? $trace[ $i ]['class']       : null,
                'function'  => isset( $trace[ $i ]['function'] )  ? $trace[ $i ]['function']    : null,
            )
        );

        return $record;
    }
}
