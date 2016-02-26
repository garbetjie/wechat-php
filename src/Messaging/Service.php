<?php

namespace Garbetjie\WeChatClient\Messaging;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;

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
     * Send a push message to the specified recipient.
     *
     * @param TypeInterface $message
     * @param string        $recipient
     */
    public function push ( TypeInterface $message, $recipient )
    {
        $json = ( new PushMessageFormatter() )->format( $message, $recipient );

        try {
            $request = new Request( "POST", "https://api.wechat.com/cgi-bin/message/custom/send", [ ], $json );
            $this->client->send( $request );
        } catch ( GuzzleException $e ) {
            throw new Exception( "Cannot send push message. HTTP error occurred.", null, $e );
        }
    }

    /**
     * Returns a new instance of the broadcast facade, providing capability to send broadcast messages.
     *
     * @return BroadcastService
     */
    public function broadcast ()
    {
        return new BroadcastService( $this->client );
    }

    public function templates ()
    {
        return new TemplatesService( $this->client );
    }
}
