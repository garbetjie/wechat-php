<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChat\Client\Exception\ApiErrorException;
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
    public function __construct (Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send a push message to the specified recipient.
     *
     * @param TypeInterface $message
     * @param string        $recipient
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function push (TypeInterface $message, $recipient)
    {
        $json = (new PushMessageFormatter())->format($message, $recipient);
        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/message/custom/send", [], $json);
        $this->client->send($request);
    }

    /**
     * Returns a new instance of the broadcast facade, providing capability to send broadcast messages.
     *
     * @return BroadcastService
     */
    public function broadcast ()
    {
        return new BroadcastService($this->client);
    }

    /**
     * @return TemplatesService
     */
    public function templates ()
    {
        return new TemplatesService($this->client);
    }
}
