<?php

namespace Garbetjie\WeChatClient\Urls;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Garbetjie\WeChatClient\Auth\AccessToken;
use Garbetjie\WeChatClient\Client;

class BulkService
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
     * Shortens all the given URLs.
     *
     * @param array    $urls
     * @param callable $callback
     *
     * @return array
     */
    public function shorten ( array $urls, callable $callback = null )
    {
        // Create output.
        $urls = array_values( $urls );
        $shortened = [ ];

        // Create request builder.
        $requests = function ( $urls ) {
            foreach ( $urls as $url ) {
                yield new Request(
                    'POST',
                    'https://api.weixin.qq.com/cgi-bin/shorturl',
                    [ ],
                    json_encode( [
                        'action'   => 'long2short',
                        'long_url' => $url,
                    ] )
                );
            }
        };

        if ( ! isset( $callback ) ) {
            $shortened = array_combine( $urls, array_pad( [ ], count( $urls ), null ) );
            $callback = function ( $short, $long ) use ( &$shortened ) {
                $shortened[ $long ] = $short;
            };
        }

        // Send off requests.
        ( new Pool(
            $this->client,
            $requests( $urls ),
            [
                'fulfilled' => function ( ResponseInterface $response, $index ) use ( $urls, $callback ) {
                    $json = json_decode( (string) $response->getBody(), true );
                    $long = $urls[ $index ];
                    call_user_func( $callback, $json[ 'short_url' ], $long );
                },
            ]
        ) )->promise()->wait();

        // Return the shortened URLs.
        return $shortened;
    }

    public function expand ( array $urls, callable $callback = null )
    {
        $urls = array_values( $urls );
        $expanded = [ ];

        // Request builder.
        $requests = function ( $urls ) {
            foreach ( $urls as $url ) {
                yield new Request( 'HEAD', $url );
            }
        };

        if ( ! isset( $callback ) ) {
            $expanded = array_combine( $urls, array_pad( [ ], count( $urls ), null ) );
            $callback = function ( $long, $short ) use ( &$expanded ) {
                $expanded[ $short ] = $long;
            };
        }
        
        $client = clone $this->client;
        $client->useToken( null );

        ( new Pool(
            $client,
            $requests( $urls ),
            [
                'options'   => [
                    RequestOptions::ALLOW_REDIRECTS => [
                        'max'         => 5,
                        'strict'      => true,
                        'on_redirect' => function ( RequestInterface $request, ResponseInterface $response, UriInterface $uri ) use ( $callback ) {
                            $source = (string) $request->getUri();
                            $destination = (string) $uri;

                            call_user_func( $callback, $destination, $source );
                        },
                    ],
                ],
            ]
        ) )->promise()->wait();

        return $expanded;
    }
}
