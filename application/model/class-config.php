<?php

namespace PeerRaiser\Model;

class Config {

    /**
     * List of properties
     *
     * @var    array
     */
    protected $properties = array();

    /**
     * Parent object.
     *
     * @var    \PeerRaiser\Model\Config
     */
    protected $parent = null;

    /**
     * Record of deleted properties.
     *
     * Prevents access to the parent object's properties after deletion in this instance.
     *
     * @see    get()
     * @var    array
     */
    protected $deleted = array();

    /**
     * @var bool
     */
    protected $frozen = false;

    /**
     * Set new value
     *
     * @param $name
     * @param $value
     * @return $this|\Exception|\WP_Error
     */
    public function set( $name, $value ) {
        if ( $this->frozen ) {
            return $this->stop( 'This object has been frozen. You cannot change the parent anymore.' );
        }

        $this->properties[ $name ] = $value;
        unset( $this->deleted[ $name ] );

        return $this;
    }

    /**
     * Import an array or an object as properties
     *
     * @param $var
     * @return $this|\Exception|\WP_Error
     */
    public function import ( $var ) {
        if ( $this->frozen ) {
            return $this->stop( 'This object has been frozen. You cannot change the parent anymore.' );
        }

        if ( !is_array( $var ) && !is_object( $var ) ) {
            return $this->stop( 'Cannot import this variable. Use arrays or objects only, not a "'. gettype( $var ) . '".' );
        }

        foreach ( $var as $name => $value ) {
            $this->properties[ $name ] = $value;
        }

        return $this;
    }

    /**
     * Get a value.
     *
     * Might be retreived from parent object
     *
     * @since     1.0.0
     * @param     string    $name    property to retrieve
     * @return    mixed              value of property, or null
     */
    public function get( $name ) {
        if ( isset( $this->properties[ $name ] ) ) {
            return $this->properties[ $name ];
        }

        if ( isset( $this->deleted[ $name ] ) ) {
            return null;
        }

        if ( null === $this->parent ) {
            return null;
        }

        return $this->parent->get( $name );
    }

    /**
     * Get all properties.
     *
     * @since     1.0.0
     * @param     boolean    $use_parent    Get parent object's properties too
     * @return    array                     Array of properties and values
     */
    public function get_all( $use_parent = false ) {
        if ( !$use_parent ) {
            return $this->properties;
        }

        $parent_properties = $this->parent->get_all( true );
        $all = array_merge( $parent_properties, $this->properties );

        // Remove properties existing in the parent but deleted in this instance.
        return array_diff( $all, $this->deleted );
    }

    /**
     * Check if property exists
     *
     * @since     1.0.0
     * @param     string     $name    property name
     * @return    boolean
     */
    public function has( $name ) {
        if ( isset( $this->properties[ $name ] ) ) {
            return true;
        }

        if ( isset( $this->deleted[ $name ] ) ) {
            return false;
        }

        if ( null === $this->parent) {
            return false;
        }

        return $this->parent->has( $name );
    }

    /**
     * Delete a key and add its name to the $deleted array.
     *
     * Further calls to has() and get() will not take this property into account.
     *
     * @since     1.0.0
     * @param     string    $name
     * @return    void|\PeerRaiser\Model\Config
     */
    public function delete( $name ) {
        if ( $this->frozen ) {
            return $this->stop( 'This object has been frozen. You cannot change the parent anymore.' );
        }

        $this->deleted[ $name ] = true;
        unset( $this->properties[ $name ] );

        return $this;
    }

    /**
     * Set the parent object. Properties of this object will be inherited.
     *
     * @param Config $object
     * @return $this|\Exception|\WP_Error
     */
    public function set_parent( \PeerRaiser\Model\Config $object ) {
        if ( $this->frozen ) {
            return $this->stop( 'This object has been frozen. You cannot change the parent anymore.' );
        }

        $this->parent = $object;

        return $this;
    }

    /**
     * Test if the current instance has a parent.
     *
     * @since     1.0.0
     * @return    boolean
     */
    public function has_parent() {
        return null === $this-parent;
    }

    /**
     * Lock write access to this object's instance forever.
     *
     * @since     1.0.0
     * @return    \PeerRaiser\Model\Config
     */
    public function freeze() {
        $this->frozen = true;

        return $this;
    }

    /**
     * Test from outside if an object has been frozen
     *
     * @since     1.0.0
     * @return    boolean
     */
    public function is_frozen() {
        return $this->frozen;
    }

    /**
     * Used for attemps to write to a frozen instance
     *
     * @since     1.0.0
     * @param     string        $message    The error message
     * @param     string        $code       Code to group error messages
     * @throws    \Exception
     * @return    \Exception|\WP_Error
     */
    protected function stop( $message, $code = '' ) {
        if ( ''  === $code ) {
            $code = __CLASS__;
        }

        if ( class_exists( 'WP_Error' ) ) {
            return new \WP_Error( $code, $message );
        }

        throw new \Exception( $message, $code );
    }

    # ==== Magic functions ==== #

    /**
     * Wrapper for set().
     *
     * @see    set()
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return \PeerRaiser\Model\Config
     */
    public function __set( $name, $value ) {
        return $this->set( $name,  $value );
    }

    /**
     * Wrapper for get()
     *
     * @see    get()
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function __get( $name ) {
        return $this->get( $name );
    }

    /**
     * Wrapper for has().
     *
     * @see    has()
     *
     * @param  string $name
     *
     * @return boolean
     */
    public function __isset( $name ) {
        return $this->has( $name );
    }
}
