<?php

namespace PeerRaiser\Core;

/**
 * PeerRaiser core capabilities.
 */
class Capability {

    protected $allowed_capabilities = array(
        'peerraiser_create_campaign',
        'peerraiser_modify_campaign',
        'peerraiser_has_full_access',
    );


    /**
     * Create roles
     *
     * @return void
     */
    public function populate_roles() {
        $roles = array( 'administrator', 'editor' );
        foreach ( $roles as $role ) {
            $role = get_role( $role );
            if ( empty( $role ) ) {
                continue;
            }
            $role->add_cap( 'peerraiser_create_campaign' );
            $role->add_cap( 'peerraiser_modify_campaign' );
            $role->add_cap( 'peerraiser_has_full_access' );
        }

        $roles = array( 'author', 'contributor' );
        foreach ( $roles as $role ) {
            $role = get_role( $role );
            if ( empty( $role ) ) {
                continue;
            }
            $role->add_cap( 'peerraiser_create_campaign' );
            $role->add_cap( 'peerraiser_modify_campaign' );
        }

        $role = get_role( 'author' );
        if ( ! empty( $role ) ) {
            $role->add_cap( 'peerraiser_create_campaign' );
        }
    }

    /**
     * Update roles.
     *
     * @param     array    $roles
     *
     * @return    void
     */
    public function update_roles( array $roles ) {
        foreach ( $roles as $role => $capabilities ) {
            $role = get_role( $role );
            if ( empty( $role ) ) {
                continue;
            }
            if ( is_array( $capabilities ) && isset( $capabilities['add'] ) ) {
                $collection = (array) $capabilities['add'];
                foreach ( $collection as $capability ) {
                    if ( ! $role->has_cap( $capability ) && in_array( $capability, $this->allowed_capabilities ) ) {
                        $role->add_cap( $capability );
                    }
                }
            }
            if ( is_array( $capabilities ) && isset( $capabilities['remove'] ) ) {
                $collection = (array) $capabilities['remove'];
                foreach ( $collection as $capability ) {
                    if ( $role->has_cap( $capability ) && in_array( $capability, $this->allowed_capabilities ) ) {
                        $role->remove_cap( $capability );
                    }
                }
            }
        }
    }

}
