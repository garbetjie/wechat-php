<?php

namespace Garbetjie\WeChatClient\URL;

use Garbetjie\WeChatClient\Service as BaseService;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class Service extends BaseService
{
    /**
     * Shortens all the given URLs.
     *
     * If a callback is given, it needs to have the following signature:
     * `function (RequestException $error = null, $short = '', $long = '')`
     *
     * If an error occurs attempting to shorten a URL, the $error parameter will be populated with an instance of
     * `GuzzleHttp\Exception\RequestException`.
     *
     * If no callback is given, an array containing a long => short URL mapping is returned.
     *
     * @param array    $urls
     * @param callable $callback
     *
     * @return array
     */
    public function shortenAll (array $urls, callable $callback = null)
    {
        // Create output.
        $urls = array_values($urls);
        $shortened = [];

        // Create request builder.
        $requests = function ($urls) {
            foreach ($urls as $url) {
                yield new Request(
                    'POST',
                    'https://api.weixin.qq.com/cgi-bin/shorturl',
                    [],
                    json_encode([
                        'action'   => 'long2short',
                        'long_url' => $url,
                    ])
                );
            }
        };

        if (! is_callable($callback)) {
            $shortened = array_combine($urls, array_pad([], count($urls), null));
            $callback = function ($error, $short, $long) use (&$shortened) {
                if ($error === null) {
                    $shortened[$long] = $short;
                }
            };
        }

        // Send off requests.
        (new Pool(
            $this->client,
            $requests($urls),
            [
                'fulfilled' => function (ResponseInterface $response, $index) use ($urls, $callback) {
                    $json = json_decode((string)$response->getBody());
                    $long = $urls[$index];
                    $short = isset($json->short_url) ? $json->short_url : null;

                    call_user_func($callback, null, $short, $long);
                },
                'rejected'  => function (RequestException $reason) use (&$failed, $callback) {
                    call_user_func($callback, $reason, null, null);
                },
            ]
        ))->promise()->wait();

        // Return the shortened URLs.
        return $shortened;
    }
    
    /**
     * Returns a shorter representation of the given URL. Similar to URL shortening services like bit.ly, etc.
     *
     * @param string $url
     *
     * @return string|null
     */
    public function shorten ($url)
    {
        $shortened = null;
        
        $this->shortenAll([$url], function ($error, $short, $long) use (&$shortened) {
            $shortened = $short;
        });
        
        return $shortened;
    }

    /**
     * Expands the given URLs into their long version. This is done dirtily, by issuing a HEAD request to the given
     * short URL, and using the contents of the `Location` header.
     *
     * If a callback is given, it needs to have the following signature:
     * `function ($long = '', $short = '')`
     *
     * If a callback isn't specified, an array containing a mapping of short => long URLs is returned.
     *
     * @param array         $urls
     * @param callable|null $callback
     *
     * @return array
     */
    public function expandAll (array $urls, callable $callback = null)
    {
        $urls = array_values($urls);
        $expanded = [];

        // Request builder.
        $requests = function ($urls) {
            foreach ($urls as $url) {
                yield new Request('HEAD', $url);
            }
        };

        if (! is_callable($callback)) {
            $expanded = array_combine($urls, array_pad([], count($urls), null));
            $callback = function ($error, $long, $short) use (&$expanded) {
                if (! $error) {
                    $expanded[$short] = $long;
                }
            };
        }

        $client = clone $this->client;
        $client->setAccessToken(null);

        (new Pool(
            $client,
            $requests($urls),
            [
                'fulfilled' => function (ResponseInterface $response, $index) use ($urls, $callback) {
                    $short = $urls[$index];
                    $long = $response->hasHeader('Location') ? $response->getHeaderLine('Location') : null;

                    call_user_func($callback, null, $long, $short);
                },
                'rejected'  => function (RequestException $reason) use ($callback) {
                    call_user_func($callback, $reason, null, null);
                },
            ]
        ))->promise()->wait();

        return $expanded;
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
        $expanded = null;
        
        $this->expandAll([$url], function ($error, $long) use (&$expanded) {
            $expanded = $long;
        });
        
        return $expanded;
    }
}
