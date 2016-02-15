<?php

namespace WeChat;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WeChat\Auth\AccessToken;

class Client extends GuzzleClient
{
    /**
     * @var AccessToken
     */
    protected $token;

    /**
     * @var bool
     */
    protected $developerMode = false;

    /**
     * {@inheritdoc}
     */
    public function __construct ( array $config = [ ] )
    {
        // Enable developer mode.
        if ( isset( $config[ 'developerMode' ] ) ) {
            $this->developerMode = ! ! $config[ 'developerMode' ];
            unset( $config[ 'developerMode' ] );
        }

        if ( ! isset( $config[ 'handler' ] ) ) {
            $config[ 'handler' ] = HandlerStack::create();
        }

        // Set the response filter.
        $config[ 'handler' ]->remove( 'wechat.response' );
        $config[ 'handler' ]->push( $this->createResponseHandler(), 'wechat.response' );

        // Set the authentication.
        $config[ 'handler' ]->remove( 'wechat.authentication' );
        $config[ 'handler' ]->unshift( $this->createAuthenticationHandler(), 'wechat.authentication' );

        // Set the WeChat developer middleware.
        $config[ 'handler' ]->remove( 'wechat.developerMode' );
        $config[ 'handler' ]->unshift( $this->createDeveloperModelHandler(), 'wechat.developerMode' );

        // Call the parent's constructor.
        parent::__construct( $config );
    }

    /**
     * Creates the middleware required for validating API responses.
     *
     * @return \Closure
     */
    private function createResponseHandler ()
    {
        return function ( callable $handler ) {
            return function ( RequestInterface $request, array $options = [ ] ) use ( $handler ) {
                return $handler( $request, $options )->then(
                    function ( ResponseInterface $response ) use ( $request ) {
                        if ( $response->getStatusCode() >= 300 ) {
                            return $response;
                        }

                        $body = (string) $response->getBody();
                        $json = json_decode( $body, true );
                        $errorCode = json_last_error();

                        if ( $errorCode !== JSON_ERROR_NONE ) {
                            $previous = new \RuntimeException( json_last_error_msg(), json_last_error() );

                            throw new BadResponseException( "Invalid JSON.", $request, $response, $previous );
                        }

                        if ( isset( $json[ 'errcode' ] ) && $json[ 'errcode' ] != 0 ) {
                            $message = isset( $json[ 'errmsg' ] ) ? $json[ 'errmsg' ] : null;
                            $code = $json[ 'errcode' ];
                            $previous = new \RuntimeException( $message, $code );

                            throw new BadResponseException( "API error: {$message}", $request, $response, $previous );
                        }

                        return $response;
                    }
                );
            };
        };
    }

    /**
     * Returns a callback function that is used as middleware in the Guzzle request.
     *
     * @return \Closure
     */
    private function createAuthenticationHandler ()
    {
        $token = &$this->token;

        return function ( callable $handler ) use ( &$token ) {
            return function ( RequestInterface $request, array $options = [ ] ) use ( $handler, &$token ) {
                if ( $token ) {
                    $newUri = Uri::withQueryValue( $request->getUri(), 'access_token', (string) $token );
                    $request = $request->withUri( $newUri );
                }

                return $handler( $request, $options );
            };
        };
    }

    /**
     * Returns a callback function that is used to alter the destination host, depending on whether developer mode is
     * being used or not.
     *
     * @return \Closure
     */
    private function createDeveloperModelHandler ()
    {
        $developerMode = &$this->developerMode;

        return function ( callable $handler ) use ( &$developerMode ) {
            return function ( RequestInterface $request, array $options = [ ] ) use ( $handler, &$developerMode ) {
                if ( $developerMode ) {
                    $mapping = [
                        'api.wechat.com'      => 'api.devcentral.co.za',
                        'file.api.wechat.com' => 'file.api.devcentral.co.za',
                    ];

                    $uri = $request->getUri();

                    if ( isset( $mapping[ $uri->getHost() ] ) ) {
                        $uri = $uri->withHost( $mapping[ $uri->getHost() ] );
                    }

                    $request = $request->withUri( $uri );
                }

                return $handler ( $request, $options );
            };
        };
    }

    /**
     * Sets the authentication token to be used in subsequent requests.
     *
     * Setting the token value to NULL will clear the token being used.
     *
     * @param AccessToken $token
     */
    public function useToken ( AccessToken $token = null )
    {
        $this->token = $token;
    }

    /**
     * Indicate whether or not we're using the WeChat developer portal as a proxy.
     *
     * @param bool $enabled
     */
    public function useDeveloperMode ()
    {
        $this->developerMode = true;
    }
}
