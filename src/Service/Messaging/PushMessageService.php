<?php

namespace Garbetjie\WeChatClient\Service\Messaging;

use Garbetjie\WeChatClient\Exception\APIErrorException;
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
     */
    public function sendToUser (MessageTypeInterface $message, $recipient)
    {
        $json = (new PushMessageFormatter())->format($message, $recipient);
        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/message/custom/send", [], $json);
        $this->client->send($request);
    }
}
