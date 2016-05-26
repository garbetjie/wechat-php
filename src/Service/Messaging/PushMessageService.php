<?php

namespace Garbetjie\WeChatClient\Service\Messaging;

use Garbetjie\WeChatClient\Exception\ApiErrorException;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Service\Messaging\BroadcastMessageService;
use Garbetjie\WeChatClient\Service\Messaging\PushMessageFormatter;
use Garbetjie\WeChatClient\Service\Messaging\TemplateMessageService;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Service\Messaging\Type\MessageTypeInterface;

class PushMessageService extends Service
{
    /**
     * Send a push message to the specified recipient.
     *
     * @param MessageTypeInterface $message
     * @param string               $recipient
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function push (MessageTypeInterface $message, $recipient)
    {
        $json = (new PushMessageFormatter())->format($message, $recipient);
        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/message/custom/send", [], $json);
        $this->client->send($request);
    }

    /**
     * Returns a new instance of the broadcast facade, providing capability to send broadcast messages.
     *
     * @return BroadcastMessageService
     */
    public function broadcast ()
    {
        return new BroadcastMessageService($this->client);
    }

    /**
     * @return TemplateMessageService
     */
    public function templates ()
    {
        return new TemplateMessageService($this->client);
    }
}
