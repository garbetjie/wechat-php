<?php

namespace Garbetjie\WeChat\Users;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use WeChat\Client;

class BulkService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * BulkService constructor.
     *
     * @param Client $client
     */
    public function __construct ( Client $client )
    {
        $this->client = $client;
    }

    /**
     * Changes the group for the specified users.
     *
     * Returns an array containing the WeChat IDs of any moves that failed. This means that an empty array will be
     * returned on a successful move of all users.
     *
     * @param array $users The IDs of the users to move.
     * @param int   $group The ID of the group to move the users to.
     *
     * @return array
     */
    public function changeGroup ( array $users, $group )
    {
        if ( count( $users ) < 1 ) {
            throw new InvalidArgumentException( "At least one user is required." );
        }

        // Build requests.
        $requests = function ( $users ) use ( $group ) {
            foreach ( array_chunk( $users, 50 ) as $chunk ) {
                yield new Request(
                    "POST",
                    "https://api.wechat.com/cgi-bin/groups/members/batchupdate",
                    [ ],
                    json_encode( [
                        'openid_list' => $chunk,
                        'to_groupid'  => $group,
                    ] )
                );
            }
        };

        $failed = [ ];

        ( new Pool(
            $this->client,
            $requests( $users ),
            [
                'rejected' => function ( RequestException $reason ) use ( &$failed ) {
                    $json = json_decode( (string) $reason->getRequest()->getBody(), true );
                    $failed = array_merge( $failed, $json[ 'openid_list' ] );
                },
            ]
        ) )->promise()->wait();

        return $failed;
    }

    /**
     * Retrieves the profiles of all the specified WeChat user IDs.
     *
     * If no callback is supplied, then an array containing the relevant user objects (if the profile request was successful), or NULL values (if
     * the profile request failed) indexed by the specified profile ID is returned.
     *
     * <pre>
     * $returned[ 'user id' ] = new User(); // Successful.
     * $returned[ 'user id' ] = null; // Failed.
     * </pre>
     * 
     * If a callback is supplied, then instead of populating an array, the callback will be called on each successful
     * or failed profile retrieval. The signature of the callback is given below.
     * 
     * For failed profile retrievals, the User object will be NULL.
     *
     * <pre>
     * $callback = function ( User $user, string $userId ) { };
     * </pre>
     *
     * @param array $users The IDs of the users to fetch profiles for.
     * @param callable $callback Optional callback to execute on each profile retrieval.                     
     *
     * @return array
     */
    public function get ( array $users, callable $callback = null )
    {
        if ( count( $users ) < 1 ) {
            throw new InvalidArgumentException( "At least one user is required." );
        } else {
            $users = array_unique( $users );
        }

        // Build requests.
        $requests = function ( $users ) {
            foreach ( $users as $user ) {
                yield new Request( 'POST', "https://api.wechat.com/cgi-bin/user/info?openid={$user}" );
            }
        };
        
        $profiles = [ ];
        
        // Set default callback.
        if ( ! isset( $callback ) ) {
            $callback = function ( User $user = null, $id ) use ( &$profiles ) {
                $profiles[ $id ] = $user;
            };
        }

        // Send requests.
        ( new Pool(
            $this->client,
            $requests( $users ),
            [
                'fulfilled' => function ( ResponseInterface $response, $index ) use ( &$profiles, $callback ) {
                    $json = json_decode( $response->getBody(), true );
                    $user = new User( $json );
                    
                    call_user_func( $callback, $user, $user->id() );
                },
                'rejected'  => function ( RequestException $reason, $index ) use ( &$profiles, $callback ) {
                    parse_str( $reason->getRequest()->getUri()->getQuery(), $query );
                    
                    call_user_func( $callback, null, $query[ 'openid' ] );
                },
            ]
        ) )->promise()->wait();

        return $profiles;
    }
}
