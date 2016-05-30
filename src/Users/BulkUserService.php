<?php

namespace Garbetjie\WeChatClient\Users;

use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Users\User;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class BulkUserService extends Service
{
    /**
     * Changes the group for the specified users.
     *
     * Returns an array containing the WeChat IDs of any moves that failed. This means that an empty array will be
     * returned on a successful move of all users.
     *
     * @param array $userOpenIDs The IDs of the users to move.
     * @param int   $group       The ID of the group to move the users to.
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function changeGroup (array $userOpenIDs, $group)
    {
        if (count($userOpenIDs) < 1) {
            throw new InvalidArgumentException("At least one user is required.");
        }

        // Build requests.
        $requests = function ($users) use ($group) {
            foreach (array_chunk($users, 50) as $chunk) {
                yield new Request(
                    "POST",
                    "https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate",
                    [],
                    json_encode([
                        'openid_list' => $chunk,
                        'to_groupid'  => $group,
                    ])
                );
            }
        };

        $failed = [];

        (new Pool(
            $this->client,
            $requests($userOpenIDs),
            [
                'rejected' => function (RequestException $reason) use (&$failed) {
                    $json = json_decode((string)$reason->getRequest()->getBody());

                    if (isset($json->openid_list)) {
                        $failed = array_merge($failed, $json->openid_list);
                    }
                },
            ]
        ))->promise()->wait();

        return $failed;
    }

    /**
     * Retrieves the profiles of all the specified WeChat user IDs.
     *
     * If no callback is supplied, then an array containing the relevant user objects (if the profile request was
     * successful), or NULL values (if the profile request failed) indexed by the specified profile ID is returned.
     *
     * <pre>
     * $returned[ 'user id' ] = new User(); // Successful.
     * $returned[ 'user id' ] = null; // Failed.
     * </pre>
     *
     * If a callback is supplied, then instead of populating an array, the callback will be called on each successful
     * or failed profile retrieval. The signature of the callback is given below.
     *
     * For failed profile retrievals, the User object will be NULL.
     *
     * <pre>
     * $callback = function (RequestException $error = null, User $user = null) { };
     * </pre>
     *
     * @param array    $userOpenIDs The IDs of the users to fetch profiles for.
     * @param callable $callback    Optional callback to execute on each profile retrieval.
     * @param string   $lang        The language to retrieve the user's details in.
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function get (array $userOpenIDs, callable $callback = null, $lang = 'en')
    {
        if (count($userOpenIDs) < 1) {
            throw new InvalidArgumentException("At least one user is required.");
        } else {
            $userOpenIDs = array_unique(array_values($userOpenIDs));
        }

        // Build requests.
        $requestBuilder = function ($openIDs) use ($lang) {
            foreach ($openIDs as $user) {
                yield new Request('POST', "https://api.weixin.qq.com/cgi-bin/user/info?openid={$user}&lang={$lang}");
            }
        };

        $profiles = [];

        // Set default callback.
        if (! isset($callback)) {
            $profiles = array_combine($userOpenIDs, array_pad([], count($userOpenIDs), null));
            $callback = function (RequestException $error = null, User $user = null) use (&$profiles) {
                if ($error !== null) {
                    $profiles[$user->getOpenID()] = $user;
                }
            };
        }

        // Send requests.
        (new Pool(
            $this->client,
            $requestBuilder($userOpenIDs),
            [
                'fulfilled' => function (ResponseInterface $response, $index) use ($callback) {
                    $json = json_decode($response->getBody(), true);
                    $user = new User($json);

                    call_user_func($callback, null, $user);
                },
                'rejected'  => function (RequestException $reason, $index) use ($callback) {
                    call_user_func($callback, $reason, null);
                },
            ]
        ))->promise()->wait();

        return $profiles;
    }
}
