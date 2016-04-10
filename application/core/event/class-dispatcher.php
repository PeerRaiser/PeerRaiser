<?php

namespace PeerRaiser\Core\Event;

class Dispatcher implements Dispatcher_Interface {

    /**
     * Default priority
     */
    const DEFAULT_PRIORITY = 10;

    /**
     * Dispatcher
     * @var    null
     */
    private static $dispatcher = null;

    /**
     * Listeners
     * @var    array
     */
    private $listeners = array();

    /**
     * Shared events that could be called from any place
     * @var    array
     */
    private $shared_listeners = array();

    /**
     * Sorted
     * @var    array
     */
    private $sorted = array();

    /**
     * Is debug enabled
     * @var    boolean
     */
    protected $debug_enabled = false;

    /**
     * Debug data
     * @var    array
     */
    protected $debug_data = array();


    /**
     * Singleton. Returns only one event dispatcher
     *
     * @since     1.0.0
     * @return    PeerRaiser\Core\Event\Dispatcher
     */
    public static function get_dispatcher() {
        if ( !isset( self::$dispatcher ) ) {
            self::$dispatcher = new self();
        }

        return self::$dispatcher;
    }


    /**
     * Dispatches an event to all registered listeners.
     *
     * @since     1.0.0
     * @param     string                                $event_name    The name of the event to dispatch
     * @param     PeerRaiser\Core\Event|array|null      $args          The event to pass to the event handlers/listeners
     * @return    PeerRaiser\Core\Event
     */
    public function dispatch( $event_name, $args = null ) {
        if ( is_array( $args ) ) {
            $event = new \PeerRaiser\Core\Event( $args );
        } elseif ( $args instanceof \PeerRaiser\Core\Event ) {
            $event = $args;
        } else {
            $event = new \PeerRaiser\Core\Event();
        }
        $event->set_name( $event_name );

        if ( !isset( $this->listeners[ $event_name] ) ) {
            return $event;
        }

        $arguments = \PeerRaiser\Hooks::apply_arguments_filters( $event_name, $event->get_arguments() );
        $event->set_arguments( $arguments );

        $this->do_dispatch( $this->get_listeners( $event_name ), $event );

        $result = \PeerRaiser\Hooks::apply_filters( $event_name, $event->get_result() );
        $event->set_result( $result );

        if ( $event->is_echo_enabled() ) {
            echo $event->get_formatted_result();
        }

        $this->set_debug_data( $event_name, $event->get_debug() );
        if ( $event->is_ajax() ) {
            wp_die();
        }
        return $event;
    }


    protected function do_dispatch( $listeners, \PeerRaiser\Core\Event $event ) {
        foreach ( $listeners as $listener ) {
            try {
                $arguments = $this->get_arguments( $listener, $event );
                call_user_func_array( $listener, $arguments );
            } catch ( \PeerRaiser\Core\Exception $e ) {

            }
        }
    }


    /**
     * Processes callback description to get required list of arguments.
     *
     * @param     callable|array|object        $callback      The event listener.
     * @param     PeerRaiser\Core\Event        $event         The event object.
     * @param     array                        $attributes    The context to get attributes.
     * @throws    PeerRaiser\Core\Exception
     *
     * @return array
     */
    protected function get_arguments( $callback, \PeerRaiser\Core\Event $event, $attributes = array() ) {
        $arguments = array();
        if ( is_array( $callback ) ) {
            if ( ! method_exists( $callback[0], $callback[1] ) && is_callable( $callback ) ) {
                return $arguments;
            } elseif ( method_exists( $callback[0], $callback[1] ) ) {
                $callbackReflection = new \ReflectionMethod( $callback[0], $callback[1] );
            } else {
                throw new \PeerRaiser\Core\Exception( sprintf( 'Callback method "%s" is not found', print_r( $callback, true ) ) );
            }
        } elseif ( is_object( $callback ) ) {
            $callbackReflection = new \ReflectionObject( $callback );
            $callbackReflection = $callbackReflection->getMethod( '__invoke' );
        } else {
            $callbackReflection = new \ReflectionFunction( $callback );
        }

        if ( $callbackReflection->getNumberOfParameters() > 0 ) {
            $parameters = $callbackReflection->getParameters();
            foreach ( $parameters as $param ) {
                if ( array_key_exists( $param->name, $attributes ) ) {
                    $arguments[] = $attributes[ $param->name ];
                } elseif ( $param->getClass() && $param->getClass()->isInstance( $event ) ) {
                    $arguments[] = $event;
                } elseif ( $param->isDefaultValueAvailable() ) {
                    $arguments[] = $param->getDefaultValue();
                } else {
                    $arguments[] = $event;
                }
            }
        }

        return (array) $arguments;
    }


    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param    string|null    $event_name    The event name to get listeners or null to get all.
     *
     * @return    mixed
     */
    public function get_listeners( $event_name = null ) {
        if ( null !== $event_name ) {
            if ( ! isset( $this->sorted[ $event_name ] ) ) {
                $this->sort_listeners( $event_name );
            }
            return $this->sorted[ $event_name ];
        }

        foreach ( $this->listeners as $event_name => $event_listeners ) {
            if ( ! isset( $this->sorted[ $event_name ] ) ) {
                $this->sort_listeners( $event_name );
            }
        }

        return array_filter( $this->sorted );
    }


    /**
     * Sorts the internal list of listeners for the given event by priority.
     *
     * @param    string    $event_name    The name of the event.
     *
     * @return    null
     */
    private function sort_listeners( $event_name ) {
        $this->sorted[ $event_name ] = array();

        if ( isset( $this->listeners[ $event_name ] ) ) {
            krsort( $this->listeners[ $event_name ] );
            // we should make resulted array unique to avoid duplicated calls.
            // php function `array_unique` works wrong and has bugs working with objects/arrays.
            $temp_array = call_user_func_array( 'array_merge', $this->listeners[ $event_name ] );
            $result = array();
            foreach ( $temp_array as $callback ) {
                if ( ! in_array( $callback, $result ) ) {
                    $result[] = $callback;
                }
            }
            $this->sorted[ $event_name ] = $result;
        }
    }


    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string|null $event_name
     *
     * @return mixed
     */
    public function has_listeners( $event_name = null ) {
        return (bool) count( $this->get_listeners( $event_name ) );
    }


    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events he is
     * interested in and added as a listener for these events.
     *
     * @param    \PeerRaiser\Core\Event\Subscriber_Interface    $subscriber    The subscriber.
     */
    public function add_subscriber( \PeerRaiser\Core\Event\Subscriber_Interface $subscriber ) {
        foreach ( $subscriber->get_shared_events() as $event_name => $params ) {
            if ( is_string( $params ) ) {
                $this->add_shared_listener( $event_name, array( $subscriber, $params ) );
            } elseif ( is_string( $params[0] ) ) {
                $this->add_shared_listener( $event_name, array( $subscriber, $params[0] ) );
            } else {
                foreach ( $params as $listener ) {
                    $this->add_shared_listener( $event_name, array( $subscriber, $listener[0] ) );
                }
            }
        }

        foreach ( $subscriber->get_subscribed_events() as $event_name => $params ) {
            if ( is_string( $params ) ) {
                $this->add_listener( $event_name, array( $subscriber, $params ) );
            } else {
                foreach ( $params as $listener ) {
                    if ( method_exists( $subscriber, $listener[0] ) ) {
                        $this->add_listener( $event_name, array( $subscriber, $listener[0] ), isset( $listener[1] ) ? $listener[1] : self::DEFAULT_PRIORITY );
                    } elseif ( ($callable = $this->get_shared_listener( $listener[0] )) !== null ) {
                        $this->add_listener( $event_name, $callable, isset( $listener[1] ) ? $listener[1] : self::DEFAULT_PRIORITY );
                    }
                }
            }
        }
    }


    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string $event_name The event name to listen on.
     * @param callable $listener The event listener.
     * @param int $priority The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to self::DEFAULT_PRIORITY)
     *
     * @return null
     */
    public function add_listener( $event_name, $listener, $priority = self::DEFAULT_PRIORITY ) {
        \PeerRaiser\Hooks::register_peerraiser_action( $event_name );
        $this->listeners[ $event_name ][ $priority ][] = $listener;
        unset( $this->sorted[ $event_name ] );
    }


    /**
     * Adds an shared event listener that listens on the specified events.
     *
     * @param string $event_name The event name to listen on.
     * @param callable $listener The event listener.
     *
     * @return null
     */
    public function add_shared_listener( $event_name, $listener ) {
        $this->shared_listeners[ $event_name ] = $listener;
    }


    /**
     * Returns shared event listener.
     *
     * @param string $event_name The event name.
     *
     * @return callable|null
     */
    public function get_shared_listener( $event_name ) {
        if ( isset( $this->shared_listeners[ $event_name ] ) ) {
            return $this->shared_listeners[ $event_name ];
        }
        return null;
    }


    /**
     * Removes an event subscriber.
     *
     * @param    \PeerRaiser\Core\Event\Subscriber_Interface    $subscriber    The subscriber
     */
    public function remove_subscriber( \PeerRaiser\Core\Event\Subscriber_Interface $subscriber ) {
        foreach ( $subscriber->get_subscribed_events() as $event_name => $params ) {
            if ( is_array( $params ) && is_array( $params[0] ) ) {
                foreach ( $params as $listener ) {
                    $this->remove_listener( $event_name, array( $subscriber, $listener[0] ) );
                }
            } else {
                $this->remove_listener( $event_name, array( $subscriber, is_string( $params ) ? $params : $params[0] ) );
            }
        }
    }


    /**
     * Removes an event listener from the specified events.
     *
     * @param    string      $event_name    The event name to listen on.
     * @param    callable    $listener      The event listener.
     *
     * @return bool
     */
    public function remove_listener( $event_name, $listener ) {
        if ( ! isset( $this->listeners[ $event_name ] ) ) {
            return false;
        }
        $result = false;
        foreach ( $this->listeners[ $event_name ] as $priority => $listeners ) {
            if ( false !== ( $key = array_search( $listener, $listeners, true ) ) ) {
                unset( $this->listeners[ $event_name ][ $priority ][ $key ], $this->sorted[ $event_name ] );
                $result = true;
            }
        }
        return $result;
    }


    /**
     * Enables collecting of the debug information about raised events.
     *
     * @param     boolean    $debug_enabled
     *
     * @return    \PeerRaiser\Core\Event\Dispatcher
     */
    public function set_debug_enabled( $debug_enabled ) {
        $this->debug_enabled = $debug_enabled;
        return $this;
    }


    /**
     * Returns event's debug information
     *
     * @return    array
     */
    public function get_debug_data() {
        return $this->debug_data;
    }


    /**
     * Formats and adds event debug information into collection.
     *
     * @param    string    $event_name    The name of the event.
     * @param    array     $context       Debug information.
     *
     * @return    \PeerRaiser\Core\Event\Dispatcher
     */
    public function set_debug_data( $event_name, $context ) {
        if ( in_array( $event_name, array( 'peerraiser_post_metadata' ) ) ) {
            return $this;
        }
        if ( $this->debug_enabled ) {
            $listeners = $this->get_listeners( $event_name );
            $record = array(
                'message'       => (string) $event_name,
                'context'       => $context,
                'extra'         => (array) $listeners,
                'level'         => count( $listeners ) > 0 ? \PeerRaiser\Core\Logger::DEBUG : \PeerRaiser\Core\Logger::WARNING,
            );
            $this->debug_data[] = $record;
        }
        return $this;
    }

}