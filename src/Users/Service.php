<?php

namespace Garbetjie\WeChatClient\Users;

use Garbetjie\WeChatClient\Exception\ApiErrorException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
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
     * Changes the group for the specified user to the specified group's ID.
     *
     * @param string $user  The user's WeChat ID.
     * @param int    $group The ID of the group to move the user to.
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function changeGroup ($user, $group)
    {
        $json = json_encode([
            'openid'     => $user,
            'to_groupid' => $group,
        ]);

        $request = new Request('POST', 'https://api.weixin.qq.com/cgi-bin/groups/members/update', [], $json);
        $this->client->send($request);
    }

    /**
     * Retrieves the group ID of the specified user.
     *
     * @param int $user The WeChat ID of the user to fetch the group ID for.
     *
     * @return int
     * 
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function group ($user)
    {
        $request = new Request(
            'POST',
            'https://api.weixin.qq.com/cgi-bin/groups/getid',
            [],
            json_encode(['openid' => $user])
        );
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody(), true);

        return $json['groupid'];
    }

    /**
     * Retrieves the profile of the specified user, returning a `WeChat\Users\User` object upon successful fetching.
     *
     * Throws an exception if the profile cannot be fetched.
     *
     * @param string $user The WeChat ID of the user to fetch.
     * @param string $lang The language to retrieve city/province/country in. Defaults to "en" (English).
     *
     * @return User
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function get ($user, $lang = 'en')
    {
        $request = new Request('POST', "https://api.weixin.qq.com/cgi-bin/user/info?lang={$lang}&openid={$user}");
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody(), true);

        return new User($json);
    }

    /**
     * Returns a count of the number of followers for this OA.
     *
     * @return int
     */
    public function count ()
    {
        $followers = $this->paginate(null);

        return (int)$followers['total'];
    }

    /**
     * Allows pagination through the entire list of followers.
     *
     * Returns an array containing the IDs of the followers in this page, as well as the ID of the user to paginate from
     * in the next page request, as well as the total number of followers.
     *
     * Example of the return value:
     *
     * <pre>
     * [
     *     'next' => '',
     *     'users' => [ ],
     *     'total' => 0,
     *     'pages' => 0,
     * ]
     * </pre>
     *
     * @param string $next  Optional ID of the next user to paginate from.
     *
     * @return array
     * 
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function paginate ($next = null)
    {
        // Build the URI
        $uri = new Uri("https://api.weixin.qq.com/cgi-bin/user/get");
        if ($next !== null) {
            $uri = Uri::withQueryValue($uri, 'next_openid', $next);
        }

        $request = new Request('GET', $uri);
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);
        
        // Calculate total pages.
        $pages = ceil($json['total'] / 10000);
        if ($pages == 0) {
            $pages = 1;
        }

        return [
            'next'  => $json['next_openid'] ?: null,
            'users' => isset($json['data']['openid']) ? $json['data']['openid'] : [],
            'total' => $json['total'],
            'pages' => $pages,
        ];
    }
}
