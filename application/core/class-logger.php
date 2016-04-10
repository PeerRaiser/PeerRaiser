<?php

namespace PeerRaiser\Core;

class Logger {

    /**
     * Logger levels
     */
    const DEBUG     = 100;
    const INFO      = 200;
    const NOTICE    = 250;
    const WARNING   = 300;
    const ERROR     = 400;
    const CRITICAL  = 500;
    const ALERT     = 550;
    const EMERGENCY = 600;

    /**
     * Contains all debugging levels.
     *
     * @var array
     */
    protected $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );

    /**
     * @var \DateTimeZone
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $name;

    /**
     * The handler stack
     *
     * @var \PeerRaiser\Core\Logger\Handler\Interface[]
     */
    protected $handlers;

    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var \PeerRaiser\Core\Logger\Handler\Interface[]
     */
    protected $processors;


    /**
     * @param string                                          $name          The logging channel
     * @param \PeerRaiser\Core\Logger\Handler\Interface[]      $handlers      Optional stack of handlers, the first one in the
     *                                                                       array is called first, etc.
     * @param \PeerRaiser\Core\Logger\Processor\Interface[]    $processors    Optional array of processors
     */
    public function __construct( $name = 'default', array $handlers = array(), array $processors = array() ) {
        $this->name         = $name;
        $this->handlers     = $handlers;
        $this->processors   = $processors;
        $this->timezone     = new \DateTimeZone( date_default_timezone_get() ? date_default_timezone_get() : 'UTC' );
    }


    /**
     * Get logger
     *
     * @since     1.0.0
     * @return    PeerRaiser\Core\Logger
     */
    public static function get_logger() {

        $logger = wp_cache_get( 'logger', 'peerraiser' );
        if ( is_a( $logger, 'PeerRaiser\Core\Logger' ) ) {
            return $logger;
        }

        $config   = \PeerRaiser\Core\Setup::get_plugin_config();
        $handlers = array();

        if ( $config->get( 'debug_mode' ) ) {
            $wp_handler = new \PeerRaiser\Core\Logger\Handler\WordPress( PeerRaiser\Core\Logger::WARNING );
            $wp_handler->set_formatter( new \PeerRaiser\Core\Logger\Formatter\Html() );

            $handlers[] = $wp_handler;
        } else {
            $handlers[] = new \PeerRaiser\Core\Logger\Handler\Null();
        }

        // add additional processors for more detailed log entries
        $processors = array(
            new \PeerRaiser\Core\Logger\Processor\Web(),
            new \PeerRaiser\Core\Logger\Processor\Memory_Usage(),
            new \PeerRaiser\Core\Logger\Processor\Memory_Peak_Usage(),
        );
        $peerraiser_event_dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $peerraiser_event_dispatcher->set_debug_enabled( true );
        $logger = new \PeerRaiser\Core\Logger( 'peerraiser', $handlers, $processors );

        // cache the config
        wp_cache_set( 'logger', $logger, 'peerraiser' );

        return $logger;
    }


    /**
     * Add a log record at the DEBUG level.
     *
     * @param    string    $message    The log message
     * @param    array     $context    The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function debug( $message, array $context = array() ) {
        return $this->add_record( self::DEBUG, $message, $context );
    }


    /**
     * Add a log record at the ERROR level.
     *
     * @param    string    $message    The log message
     * @param    array     $context    The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function error( $message, array $context = array() ) {
        return $this->add_record( self::ERROR, $message, $context );
    }


    /**
     * Add a log record at the INFO level.
     *
     * @param    string    $message    The log message
     * @param    array     $context    The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function info( $message, array $context = array() ) {
        return $this->add_record( self::INFO, $message, $context );
    }


    /**
     * Add a log record at the NOTICE level.
     *
     * @param    string    $message The log message
     * @param    array     $context The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function notice( $message, array $context = array() ) {
        return $this->add_record( self::NOTICE, $message, $context );
    }


    /**
     * Add a log record at the WARNING level.
     *
     * @param    string    $message    The log message
     * @param    array     $context    The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function warning( $message, array $context = array() ) {
        return $this->add_record( self::WARNING, $message, $context );
    }


    /**
     * Add a log record at the CRITICAL level.
     *
     * @param    string    $message The log message
     * @param    array     $context The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function critical( $message, array $context = array() ) {
        return $this->add_record( self::CRITICAL, $message, $context );
    }


    /**
     * Add a log record at the ALERT level.
     *
     * @param    string    $message The log message
     * @param    array     $context The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function alert( $message, array $context = array() ) {
        return $this->add_record( self::ALERT, $message, $context );
    }


    /**
     * Add a log record at the EMERGENCY level.
     *
     * @param    string    $message The log message
     * @param    array     $context The log context
     *
     * @return    boolean    Whether the record has been processed
     */
    public function emergency( $message, array $context = array() ) {
        return $this->add_record( self::EMERGENCY, $message, $context );
    }


    /**
     * Add a record to the log.
     *
     * @param    integer    $level
     * @param    string     $message
     * @param    array      $context
     *
     * @return    boolean
     */
    public function add_record( $level, $message, array $context = array() ) {
        if ( ! $this->handlers ) {
            $this->push_handler( new \PeerRaiser\Core\Logger\Handler\Null( ) );
        }

        $date_time = new \DateTime( 'now', $this->timezone );

        $record = array(
            'message'       => (string) $message,
            'context'       => $context,
            'level'         => $level,
            'level_name'    => self::get_level_name( $level ),
            'channel'       => $this->name,
            'datetime'      => $date_time,
            'extra'         => array(),
        );

        // Check if any handler will handle this message
        $handler_key = null;
        foreach ( $this->handlers as $key => $handler ) {
            if ( $handler->is_handling( $record ) ) {
                $handler_key = $key;
                break;
            }
        }

        if ( $handler_key === null ) {
            // No handler found
            return false;
        }

        // Found at least one handler, so process message and dispatch it
        foreach ( $this->processors as $processor ) {
            $record = $processor->process( $record );
        }

        while (
           isset( $this->handlers[ $handler_key ] ) &&
           $this->handlers[ $handler_key ]->handle( $record ) === false
        ) {
            $handler_key++;
        }

        return true;
    }


    /**
     * @return    string
     */
    public function get_name() {
        return $this->name;
    }


    /**
     * Push a handler onto the stack.
     *
     * @param    \PeerRaiser\Core\Logger\Handler\Handler_Interface
     */
    public function push_handler( \PeerRaiser\Core\Logger\Handler\Handler_Interface $handler ) {
        array_unshift( $this->handlers, $handler );
    }


    /**
     * Pop a handler from the stack.
     *
     * @return    \PeerRaiser\Core\Logger\Handler\Handler_Interface
     */
    public function pop_handler() {
        if ( ! $this->handlers ) {
            throw new \LogicException( 'You tried to pop from an empty handler stack.' );
        }

        return array_shift( $this->handlers );
    }


    /**
     * @return    \PeerRaiser\Core\Logger\Handler\Handler_Interface
     */
    public function get_handlers() {
        return $this->handlers;
    }


    /**
     * Add a processor to the stack.
     *
     * @param    \PeerRaiser\Core\Logger\Processor\Processor    $callback
     */
    public function push_processor( \PeerRaiser\Core\Logger\Processor\Processor_Interface $callback ) {
        array_unshift( $this->processors, $callback );
    }


    /**
     * Remove the processor on top of the stack and return it.
     *
     * @return    callable
     */
    public function pop_processor() {
        if ( ! $this->processors ) {
            throw new \LogicException( 'You tried to pop from an empty processor stack.' );
        }

        return array_shift( $this->processors );
    }


    /**
     * @return    callable[]
     */
    public function get_processors() {
        return $this->processors;
    }


    /**
     * Check, if the logger has a handler that listens on the given level.
     *
     * @param    integer    $level
     *
     * @return    boolean
     */
    public function is_handling( $level ) {
        $record = array(
            'level' => $level,
        );

        foreach ( $this->handlers as $handler ) {
            if ( $handler->is_handling( $record ) ) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get the name of the logging level.
     *
     * @param    integer    $level
     *
     * @return    string    $level_name
     */
    public function get_level_name( $level ) {
        return $this->levels[ $level ];
    }

}