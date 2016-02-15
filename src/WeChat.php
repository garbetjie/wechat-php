<?php

namespace WeChat;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use WeChat\Auth\AccessToken;
use WeChat\Auth\Storage\File;
use WeChat\Auth\Storage\StorageInterface;
use WeChat\Auth\Exception as AuthException;

class WeChat
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param Client|null $client
     */
    public function __construct ( Client $client = null )
    {
        $this->client = $client ?: new Client();
    }

    /**
     * @return QR\Service
     */
    public function qr ()
    {
        return new QR\Service( $this->client );
    }

    /**
     * @return Menu\Service
     */
    public function menu ()
    {
        return new Menu\Service( $this->client );
    }

    /**
     * @return Media\Service
     */
    public function media ()
    {
        return new Media\Service( $this->client );
    }

    /**
     * @return Messaging\Service
     */
    public function messaging ()
    {
        return new Messaging\Service( $this->client );
    }

    /**
     * @return Groups\Service
     */
    public function groups ()
    {
        return new Groups\Service( $this->client );
    }

    /**
     * @return Users\Service
     */
    public function users ()
    {
        return new Users\Service( $this->client );
    }

    /**
     * @return Urls\Service
     */
    public function urls ()
    {
        return new Urls\Service( $this->client );
    }

    /**
     * Setter/getter for setting or retrieving the client to use.
     *
     * If a client is supplied, this instance of `WeChat\WeChat` is returned for chaining. Otherwise, the client
     * instance is returned.
     *
     * @param Client|null $client
     *
     * @return Client|WeChat
     */
    public function client ( Client $client = null )
    {
        if ( $client === null ) {
            return $this->client;
        } else {
            $this->client = $client;

            return $this;
        }
    }

    /**
     * @param string                $appId     The application ID
     * @param string                $secretKey The application's secret key.
     * @param StorageInterface|null $storage   The optional storage interface to use when persisting access tokens.
     *                                         Defaults to storing them in the system's temporary directory (retrieved
     *                                         using `sys_get_temp_dir()`).
     *
     * @return AccessToken
     * @throws AuthException
     */
    public function authenticate ( $appId, $secretKey, StorageInterface $storage = null )
    {
        // Default to file storage.
        if ( ! $storage ) {
            $storage = new File( sys_get_temp_dir() );
        }

        $hash = $storage->hash( $appId, $secretKey );
        $cached = $storage->retrieve( $hash );

        // Cached access token is still valid. Return it.
        if ( $cached instanceof AccessToken && $cached->valid() ) {
            $this->client->useToken( $cached );

            return $cached;
        }

        try {
            $request = new Request( 'GET', "https://api.wechat.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secretKey}" );
            $response = $this->client->send( $request );
            $json = json_decode( (string) $response->getBody(), true );

            if ( isset( $json[ 'access_token' ], $json[ 'expires_in' ] ) ) {
                $token = new AccessToken( $json[ 'access_token' ], DateTime::createFromFormat( 'U', time() + $json[ 'expires_in' ] ) );
                $storage->store( $hash, $token );
                $this->client->useToken( $token );

                return $token;
            }

            throw new AuthException( "Cannot authenticate. Unexpected JSON response." );
        } catch ( GuzzleException $e ) {
            throw new AuthException( "Cannot authenticate. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Retrieve a list of IP addresses that are used by WeChat.
     *
     * @return array
     */
    public function ipList ()
    {
        try {
            $request = new Request( 'GET', 'https://api.wechat.com/cgi-bin/getcallbackip' );
            $response = $this->client->send( $request );
            $json = json_decode( $response->getBody(), true );

            return isset( $json[ 'ip_list' ] ) ? $json[ 'ip_list' ] : [ ];
        } catch ( GuzzleException $e ) {
            return [ ];
        }
    }
}
