<?php

namespace WeChat\Groups;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use WeChat\Client;

class Service
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Service constructor.
     *
     * @param Client $client
     */
    public function __construct ( Client $client )
    {
        $this->client = $client;
    }

    /**
     * Create a new group.
     *
     * @param string $name
     *
     * @return Group
     */
    public function create ( $name )
    {
        try {
            $json = json_encode( [
                'group' => [
                    'name' => $name,
                ],
            ] );

            $request = new Request( "POST", "https://api.wechat.com/cgi-bin/groups/create", [ ], $json );
            $response = $this->client->send( $request );
            $json = json_decode( $response->getBody(), true );

            if ( isset( $json[ 'group' ][ 'id' ], $json[ 'group' ][ 'name' ] ) ) {
                return new Group( $json[ 'group' ][ 'id' ], $json[ 'group' ][ 'name' ] );
            } else {
                throw new Exception( "Cannot create group. JSON response in unexpected format." );
            }
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot create group. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Retrieves a list of all the groups that have been created in this OA.
     *
     * @return Group[]
     */
    public function all ()
    {
        try {
            $request = new Request( "GET", "https://api.wechat.com/cgi-bin/groups/get" );
            $response = $this->client->send( $request );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot fetch groups. HTTP error occurred.", null, $e );
        }

        $groups = [ ];
        $json = json_decode( $response->getBody(), true );

        if ( isset( $json[ 'groups' ] ) ) {
            foreach ( $json[ 'groups' ] as $group ) {
                $groups[] = new Group( $group[ 'id' ], $group[ 'name' ], $group[ 'count' ] );
            }
        }

        return $groups;
    }

    /**
     * Allows the updating of a group.
     *
     * @param Group $group
     *
     * @throws Exception
     */
    public function update ( Group $group )
    {
        try {
            $json = [
                'group' => [
                    'id'   => $group->id(),
                    'name' => $group->name(),
                ],
            ];

            $request = new Request( "POST", "https://api.wechat.com/cgi-bin/groups/update", [ ], json_encode( $json ) );
            $this->client->send( $request );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot update group. HTTP error occurred.", null, $e );
        }
    }
}
