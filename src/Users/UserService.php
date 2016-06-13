<?php

namespace Garbetjie\WeChatClient\Users;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Users\Exception\UserException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class UserService extends Service
{
    /**
     * Changes the group for the specified user to the specified group's ID.
     *
     * @param string $userOpenID - The user's WeChat ID.
     * @param int    $groupID    - The ID of the group to move the user to.
     *
     * @return bool
     */
    public function changeGroup ($userOpenID, $groupID)
    {
        $failed = $this->changeAllGroups([$userOpenID], $groupID);

        if (count($failed) <= 0) {
            return true;
        }

        return false;
    }

    /**
     * Changes the group for all the specified users. Returns an array containing the OpenID's of the users that failed
     * to have their group changed.
     *
     * @param array $userOpenIDs
     * @param int   $groupID
     *
     * @return array
     */
    public function changeAllGroups (array $userOpenIDs, $groupID)
    {
        if (count($userOpenIDs) < 1) {
            throw new InvalidArgumentException("At least one user is required.");
        }

        // Build requests.
        $requests = function ($users) use ($groupID) {
            foreach (array_chunk($users, 50) as $chunk) {
                yield new Request(
                    "POST",
                    "https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate",
                    [],
                    json_encode([
                        'openid_list' => $chunk,
                        'to_groupid'  => $groupID,
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
    public function getUser ($user, $lang = 'en')
    {
        return $this->getAllUsers([$user], null, $lang)[$user];
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
    public function getAllUsers (array $userOpenIDs, callable $callback = null, $lang = 'en')
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
                if ($user !== null) {
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
                    $json = json_decode($response->getBody());

                    call_user_func($callback, null, new User($json));
                },
                'rejected'  => function (RequestException $reason, $index) use ($callback) {
                    call_user_func($callback, $reason, null);
                },
            ]
        ))->promise()->wait();

        return $profiles;
    }

    /**
     * Returns a count of the number of followers for this OA.
     *
     * @return int
     */
    public function countAllUsers ()
    {
        return $this->paginateUsers(null)->getTotalCount();
    }

    /**
     * Allows pagination through the entire list of followers.
     *
     * @param string $nextOpenID - Optional ID of the next user to paginate from.
     *
     * @return PaginatedResultSet
     * @throws UserException
     */
    public function paginateUsers ($nextOpenID = null)
    {
        $json = json_decode(
            $this->client->send(
                new Request(
                    'GET',
                    Uri::withQueryValue(new Uri('https://api.weixin.qq.com/cgi-bin/user/get'), 'next_openid',
                        $nextOpenID)
                )
            )->getBody()
        );

        if (! isset($json->total)) {
            return new PaginatedResultSet(null, 0, []);
        } elseif (! isset($json->next_openid, $json->data->openid)) {
            return new PaginatedResultSet(null, $json->total, []);
        } else {
            return new PaginatedResultSet($json->next_openid ?: null, $json->total, $json->data->openid);
        }
    }
}
