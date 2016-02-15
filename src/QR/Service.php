<?php

namespace Garbetjie\WeChat\QR;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
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
     * Creates a temporary Service code that is only valid for the limited duration given. The expiry given
     * cannot be longer than 1,800 seconds (30 minutes), or less than 1 second.
     *
     * Expiry times of less than 1 second will cause and `InvalidArgumentException` to be thrown.
     *
     * @param int|string   $value   The value of the Service code.
     * @param int|DateTime $expires The duration of the Service code, or the `DateTime` instance at which the
     *                              code should expire.
     *
     * @return TemporaryCode
     */
    public function temporary ( $value, $expires = 1800 )
    {
        if ( $expires instanceof DateTime ) {
            $expires = $expires->getTimestamp() - time();
        }

        if ( $expires > 1800 ) {
            $expires = 1800;
        } else if ( $expires < 1 ) {
            throw new InvalidArgumentException( "Code expiry can not be less than 1 second." );
        }

        $args = $this->createCode( [
            'expire_seconds' => $expires,
            'action_name'    => 'QR_SCENE',
            'action_info'    => [
                'scene' => [
                    ( is_int( $value ) ? 'scene_id' : 'scene_str' ) => $value,
                ],
            ],
        ] );

        return new TemporaryCode( $args[ 0 ], $args[ 1 ], $args[ 2 ] );
    }

    /**
     * Creates and returns an instance of a new permanent Service code. This code can be used to download the
     * code contents.
     *
     * @param string|int $value
     *
     * @return PermanentCode
     * @throws Exception
     */
    public function permanent ( $value )
    {
        $args = $this->createCode( [
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' => [
                'scene' => [
                    is_int( $value ) ? 'scene_id' : 'scene_str' => $value,
                ],
            ],
        ] );

        return new PermanentCode( $args[ 0 ], $args[ 1 ] );
    }

    /**
     * Download the given QR code into the specified file. If no file is supplied, a temporary one will be created using
     * the `tmpfile()` function.
     *
     * @param CodeInterface $code The QR code to download.
     * @param string        $into The path in which to save the QR code.
     *
     * @return resource
     */
    public function download ( CodeInterface $code, $into = null )
    {
        if ( is_string( $into ) ) {
            $stream = fopen( $into, 'wb' );

            if ( ! $stream ) {
                throw new Exception( "Can't open file '{$into}' for writing." );
            }
        } else {
            $stream = tmpfile();
        }

        try {
            $request = new Request( 'GET', "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$code->ticket()}" );
            $response = $this->client->send( $request, [ RequestOptions::SINK => $stream ] );
            $stream = $response->getBody()->detach();

            return $stream;
        } catch ( GuzzleException $e ) {
            throw new Exception( "Can't download Builder code. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Method responsible for the actual interaction with the WeChat API, and returns the arguments used for creating
     * a Service code.
     *
     * @param array $body
     *
     * @return array
     * @throws Exception
     */
    protected function createCode ( array $body )
    {
        try {
            $request = new Request( 'POST', 'https://api.wechat.com/cgi-bin/qrcode/create', [ ], json_encode( $body ) );
            $response = $this->client->send( $request );


            $json = json_decode( $response->getBody(), true );
            $ticket = $json[ 'ticket' ];
            $url = $json[ 'url' ];

            if ( $body[ 'action_name' ] === 'QR_LIMIT_SCENE' ) {
                return [ $ticket, $url ];
            } else {
                return [ $ticket, $url, DateTime::createFromFormat( 'U', time() + $json[ 'expire_seconds' ] ) ];
            }
        } catch ( GuzzleException $e ) {
            throw new Exception( "Unable to create Builder code. HTTP error occurred.", null, $e );
        }
    }
}
