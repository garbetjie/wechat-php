<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChat\Client\Exception\ApiErrorException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Garbetjie\WeChatClient\Client;

class TemplatesService
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
     * Converts the given short template id into a longer version that can be used when sending templated messages.
     *
     * Returns the long version of the template ID.
     *
     * @param string $short The short ID of the template to convert.
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws ApiErrorException
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
     * @param       $template
     * @param       $recipient
     * @param       $url
     * @param array $data
     * @param array $options
     *
     * @return mixed
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     * @throws InvalidArgumentException
     */
    public function send ($template, $recipient, $url, array $data, array $options = [])
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('$url must be a valid URL.');
        }

        $json = [
            'touser'      => (string)$recipient,
            'template_id' => (string)$template,
            'url'         => (string)$url,
            'data'        => [],
        ];

        if (isset($options['color']) && static::validateColor($options['color'])) {
            $json['topcolor'] = '#' . strtoupper(ltrim($options['color'], '#'));
        }

        foreach ($data as $fieldName => $fieldValue) {
            if (! is_array($fieldValue)) {
                $json['data'][$fieldName] = ['value' => $fieldValue];
            } else {
                $json['data'][$fieldName] = [
                    'value' => $fieldValue['value'],
                ];

                if (isset($fieldValue['color']) && static::validateColor($fieldValue['color'])) {
                    $json['data'][$fieldName]['color'] = '#' . strtoupper(ltrim($fieldValue['color'], '#'));
                }
            }
        }

        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/message/template/send", [],
            json_encode($json));
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        return $json['msgid'];
    }

    static protected function validateColor ($color)
    {
        return preg_match('/^(#)?([a-f0-9]{3}|[a-f0-9]{6})$/i', $color);
    }
}
