<?php

namespace Garbetjie\WeChatClient\Users;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Users\User;
use Garbetjie\WeChatClient\Users\Exception\BadUserResponseFormatException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class UserService extends Service
{
    /**
     * Changes the group for the specified user to the specified group's ID.
     *
     * @param string $userOpenID - The user's WeChat ID.
     * @param int    $groupID    - The ID of the group to move the user to.
     */
    public function changeGroup ($userOpenID, $groupID)
    {
        $json = json_encode([
            'openid'     => $userOpenID,
            'to_groupid' => $groupID,
        ]);

        $request = new Request('POST', 'https://api.weixin.qq.com/cgi-bin/groups/members/update', [], $json);
        $this->client->send($request);
    }

    /**
     * Retrieves the group ID of the specified user.
     *
     * @param int $userOpenID The WeChat ID of the user to fetch the group ID for.
     *
     * @return int
     */
    public function getGroupID ($userOpenID)
    {
        $request = new Request(
            'POST',
            'https://api.weixin.qq.com/cgi-bin/groups/getid',
            [],
            json_encode(['openid' => $userOpenID])
        );

        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody());

        if (! isset($json->groupid)) {
            throw new BadUserResponseFormatException("expected property: `groupid`", $response);
        } else {
            return $json->groupid;
        }
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
     * @throws APIErrorException
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
    public function countAllUsers ()
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
     * @param string $next Optional ID of the next user to paginate from.
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws APIErrorException
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
        $json = json_decode($response->getBody());

        if (! isset($json->total, $json->data) || ! property_exists($json, 'next_openid')) {
            throw new BadUserResponseFormatException("expected properties: `total`, `data`, `next_openid`", $response);
        }

        // Calculate total pages.
        $pages = ceil($json->total / 10000);
        if ($pages < 1) {
            $pages = 1;
        }

        return [
            'next'  => $json->next_openid ?: null,
            'users' => isset($json->data->openid) ? $json->data->openid : [],
            'total' => $json->total,
            'pages' => $pages,
        ];
    }
}
