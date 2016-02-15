<?php

namespace Garbetjie\WeChat\Users;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use WeChat\Client;
use WeChat\Groups\Group;

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
     * @return BulkService
     */
    public function bulk ()
    {
        return new BulkService( $this->client );
    }

    /**
     * Changes the group for the specified user to the specified group's ID.
     *
     * @param string $user  The user's WeChat ID.
     * @param int    $group The ID of the group to move the user to.
     *
     * @throws Exception
     */
    public function changeGroup ( $user, $group )
    {
        try {
            $json = json_encode( [
                'openid'     => $user,
                'to_groupid' => $group,
            ] );

            $request = new Request( 'POST', 'https://api.wechat.com/cgi-bin/groups/members/update', [ ], $json );
            $this->client->send( $request );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot change group. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Retrieves the group ID of the specified user.
     *
     * @param int $user The WeChat ID of the user to fetch the group ID for.
     *
     * @return int
     * @throws Exception
     */
    public function group ( $user )
    {
        try {
            $json = json_encode( [ 'openid' => $user ] );
            $request = new Request( 'POST', 'https://api.wechat.com/cgi-bin/groups/getid', [ ], $json );
            $response = $this->client->send( $request );
            $json = json_decode( (string) $response->getBody(), true );

            return $json[ 'groupid' ];
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot fetch group ID. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Retrieves the profile of the specified user, returning a `WeChat\Users\User` object upon successful fetching.
     *
     * Throws an exception if the profile cannot be fetched.
     *
     * @param string $user The WeChat ID of the user to fetch.
     *
     * @return User
     * @throws Exception
     */
    public function get ( $user )
    {
        try {
            $request = new Request( 'POST', "https://api.wechat.com/cgi-bin/user/info?openid={$user}" );
            $response = $this->client->send( $request );
            $json = json_decode( (string) $response->getBody(), true );

            return new User( $json );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot fetch user profile. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Returns a count of the number of followers for this OA.
     *
     * @return int
     */
    public function count ()
    {
        $followers = $this->paginate( null, 1 );

        return (int) $followers[ 'total' ];
    }

    /**
     * Allows pagination through the entire list of followers.
     *
     * Returns an array containing the IDs of the followers in this page, as well as the ID of the user to paginate from
     * in the next page request, as well as the total number of followers.
     *
     * Example of the return value:
     *
     * <pre>
     * [
     *     'next' => '',
     *     'users' => [ ],
     *     'total' => 0,
     *     'pages' => 0,
     * ]
     * </pre>
     *
     * @param string $next  Optional ID of the next user to paginate from.
     * @param int    $limit Optional limit on the number of users returned. Cannot be higher than 10,000.
     *
     * @return array
     */
    public function paginate ( $next = null, $limit = 500 )
    {
        // Ensure the limit is correct.
        if ( $limit < 1 ) {
            throw new InvalidArgumentException( "Limit cannot be less than 1." );
        } else if ( $limit > 10000 ) {
            throw new InvalidArgumentException( "Limit cannot be greater than 10,000." );
        }

        // Build the URI
        $uri = new Uri( 'https://api.wechat.com/cgi-bin/user/get' );
        if ( $next !== null ) {
            $uri = Uri::withQueryValue( $uri, 'next_openid', $next );
        }

        try {
            $request = new Request( 'GET', $uri );
            $response = $this->client->send( $request );
            $json = json_decode( $response->getBody(), true );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot fetch follower list. HTTP error occurred.", null, $e );
        }

        // Calculate total pages.
        $pages = ceil( $json[ 'total' ] / $limit );
        if ( $pages == 0 ) {
            $pages = 1;
        }

        return [
            'next'  => $json[ 'next_openid' ] ?: null,
            'users' => isset( $json[ 'data' ][ 'openid' ] ) ? $json[ 'data' ][ 'openid' ] : [ ],
            'total' => $json[ 'total' ],
            'pages' => $pages,
        ];
    }
}
