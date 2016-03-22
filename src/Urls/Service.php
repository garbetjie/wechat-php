<?php

namespace Garbetjie\WeChatClient\Urls;

use Garbetjie\WeChatClient\Exception\ApiFormatException;
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
    public function __construct (Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return BulkService
     */
    public function bulk ()
    {
        return new BulkService($this->client);
    }

    /**
     * Returns a shorter representation of the given URL. Similar to URL shortening services like bit.ly, etc.
     *
     * @param string $url
     *
     * @return string
     *
     * @throws GuzzleException
     * @throws ApiFormatException
     */
    public function shorten ($url)
    {
        $json = json_encode([
            'action'   => 'long2short',
            'long_url' => $url,
        ]);

        $request = new Request('POST', 'https://api.weixin.qq.com/cgi-bin/shorturl', [], $json);
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody(), true);

        return $json['short_url'];
    }

    /**
     * Expands the given URL, and will return the long version of the provided shortened URL.
     *
     * @param string $url
     *
     * @return string|null
     * 
     * @throws GuzzleException
     */
    public function expand ($url)
    {
        $client = clone $this->client;
        $client->useToken(null);

        $destination = null;
        $request = new Request("HEAD", $url);
        
        $client->send($request, [
            RequestOptions::ALLOW_REDIRECTS => [
                'max'         => 5,
                'strict'      => true,
                'on_redirect' => function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    UriInterface $uri
                ) use (&$destination) {
                    $destination = (string)$uri;
                },
            ],
        ]);

        return $destination;
    }
}
