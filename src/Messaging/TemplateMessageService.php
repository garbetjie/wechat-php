<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Messaging\Exception\MessagingException;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;

class TemplateMessageService extends Service
{
    /**
     * Converts the given short template id into a longer version that can be used when sending templated messages.
     *
     * Returns the long version of the template ID.
     *
     * @param string $short The short ID of the template to convert.
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function convert ($short)
    {
        $json = json_encode(["template_id_short" => $short]);
        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/template/api_add_template", [], $json);
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        return $json['template_id'];
    }

    /**
     * Sends the templated message with the specified template id to to specified recipient.
     *
     * Returns a message ID that can be used to query the send status of the message at a later stage.
     *
     * @param string $templateID
     * @param string $recipientOpenID
     * @param string $url
     * @param array  $data
     * @param array  $options
     *
     * @return string
     *
     * @throws MessagingException
     * @throws InvalidArgumentException
     */
    public function send ($templateID, $recipientOpenID, $url, array $data, array $options = [])
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('$url must be a valid URL.');
        }

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/template/send",
            [],
            json_encode(
                (new TemplateMessageFormatter())->format(
                    $templateID,
                    $recipientOpenID,
                    $url,
                    $data,
                    $options
                )
            )
        );
        
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());

        if (! isset($json->msgid)) {
            throw new MessagingException("bad response: expected property `msgid`");
        }

        return $json->msgid;
    }
}
