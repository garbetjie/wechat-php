<?php

namespace Garbetjie\WeChatClient\Urls;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Garbetjie\WeChatClient\Client;

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
     * Returns a shorter representation of the given URL. Similar to URL shortening services like bit.ly, etc.
     * 
     * @param string $url
     *
     * @return string
     * @throws Exception
     */
    public function shorten ( $url )
    {
        try {
            $json = json_encode( [
                'action' => 'long2short',
                'long_url' => $url,
            ] );
            
            $request = new Request( 'POST', 'https://api.wechat.com/cgi-bin/shorturl', [], $json );
            $response = $this->client->send( $request );
            $json = json_decode( (string) $response->getBody(), true );
            
            return $json[ 'short_url' ];
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot shorten URL. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Expands the given URL, and will return the long version of the provided shortened URL.
     * 
     * Returns the long destination URL on successful expansion, and throws an instance of `WeChat\Urls\Exception` if
     * an HTTP error occurs.
     * 
     * @param string $url
     *
     * @return string|null
     */
    public function expand ( $url )
    {
        $client = clone $this->client;
        $client->useToken( null );
        
        $destination = null;

        try {
            $request = new Request( "HEAD", $url );
            $client->send( $request, [
                RequestOptions::ALLOW_REDIRECTS => [
                    'max' => 5,
                    'strict' => true,
                    'on_redirect' => function ( RequestInterface $request, ResponseInterface $response, UriInterface $uri ) use ( &$destination ) {
                        $destination = (string) $uri;
                    }
                ]
            ] );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot expand URL. HTTP error occurred.", null, $e );
        }

        return $destination;
    }
}
