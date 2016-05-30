<?php

namespace Garbetjie\WeChatClient\URL;

use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\URL\BulkURLService;
use Garbetjie\WeChatClient\URL\Exception\BadURLResponseFormatException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Garbetjie\WeChatClient\Client;

class URLService extends Service
{
    /**
     * Returns a shorter representation of the given URL. Similar to URL shortening services like bit.ly, etc.
     *
     * @param string $url
     *
     * @return string
     *
     * @throws BadURLResponseFormatException
     */
    public function shorten ($url)
    {
        $json = json_encode([
            'action'   => 'long2short',
            'long_url' => $url,
        ]);

        $request = new Request('POST', 'https://api.weixin.qq.com/cgi-bin/shorturl', [], $json);
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody());

        if (! isset($json->short_url)) {
            throw new BadURLResponseFormatException("expected property: `short_url`", $response);
        } else {
            return $json->short_url;
        }
    }

    /**
     * Expands the given URL, and will return the long version of the provided shortened URL. This is done by issuing
     * a HEAD request on the given URL.
     *
     * @param string $url
     *
     * @return string|null
     */
    public function expand ($url)
    {
        $client = clone $this->client;
        $client->setAccessToken(null);

        $destination = null;
        $request = new Request("HEAD", $url);

        $client->send($request, [
            RequestOptions::ALLOW_REDIRECTS => [
                'max'         => 10,
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
