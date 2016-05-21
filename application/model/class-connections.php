<?php

namespace PeerRaiser\Model;

/**
 * PeerRaiser currency model.
 */
class Connections {

    /**
     * Contains all supported currencies.
     *
     * @var    array
     */
    protected $connections = array(
        array(
            'name' => 'campaign_to_fundraiser',
            'from' => 'pr_campaign',
            'to' => 'fundraiser',
            'cardinality' => 'one-to-many',
        ),
        array(
            'name' => 'campaign_to_participant',
            'from' => 'pr_campaign',
            'to' => 'user',
            'cardinality' => 'many-to-many',
        ),
        array(
            'name' => 'campaigns_to_teams',
            'from' => 'pr_campaign',
            'to' => 'pr_team',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'fundraiser_to_participant',
            'from' => 'fundraiser',
            'to' => 'user',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'fundraiser_to_team',
            'from' => 'fundraiser',
            'to' => 'pr_team',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'team_to_participants',
            'from' => 'pr_team',
            'to' => 'user',
            'cardinality' => 'many-to-many',
        ),
        array(
            'name' => 'teams_to_captains',
            'from' => 'pr_team',
            'to' => 'user',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'donation_to_participant',
            'from' => 'pr_donation',
            'to' => 'user',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'donation_to_team',
            'from' => 'pr_donation',
            'to' => 'pr_team',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'donation_to_fundraiser',
            'from' => 'pr_donation',
            'to' => 'fundraiser',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'donation_to_campaign',
            'from' => 'pr_donation',
            'to' => 'pr_campaign',
            'cardinality' => 'many-to-one',
        ),
        array(
            'name' => 'donation_to_donor',
            'from' => 'pr_donation',
            'to' => 'pr_donor',
            'cardinality' => 'many-to-one',
        ),
    );


    /**
     * Constructor
     *
     * @return \PeerRaiser\Model\Currency
     */
    function __construct() { }


    /**
     * Get currencies.
     *
     * @return    array    currencies
     */
    public function get_connections() {
        return $this->connections;
    }


    /**
     * Get connection by connection name
     *
     * @param    string    $connection_name    The name of the connection to retrieve
     *
     * @return    array|null    $connection_found    The connection if found or null if not found
     */
    public function get_connection( $connection_name ) {
        $connection_found = null;

        foreach ( $this->connections as $connection ) {
            if ( (string) $connection['name'] === (string) $connection_name ) {
                $connection_found = $connection;
                break;
            }
        }

        return $connection_found;
    }


    /**
     * Update existing connection
     *
     * @since     1.0.0
     * @param     array     $connection_data    The connection info
     *
     * @return    bool                          True if connection updated, false if not
     */
    public function update_connection( $connection_data = array() ) {
        $connection_updated = false;

        foreach ( $this->connections as $connection ) {
            if ( (string) $connection['name'] === (string) $connection_data['name'] ) {
                $connection = $connection_data;
                $connection_found = true;
                break;
            }
        }

        return $connection_updated;
    }


    /**
     * Add a connection
     *
     * @since    1.0.0
     * @param    array    $connection    Connection data
     *
     * @return   array|false             Returns all connections if successful or false if not
     */
    public function add_connection( $connection ) {
        if ( !is_array($connection) )
            return false;

        array_push($this->connections, $connection);

        return $this->connections;
    }

}
