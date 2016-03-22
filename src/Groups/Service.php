<?php

namespace Garbetjie\WeChatClient\Groups;

use Garbetjie\WeChatClient\Exception\ApiErrorException;
use Garbetjie\WeChatClient\Exception\ApiFormatException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
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
     * Create a new group.
     *
     * @param string $name
     *
     * @return Group
     *
     * @throws GuzzleException
     * @throws ApiFormatException
     * @throws ApiErrorException
     */
    public function create ($name)
    {
        $json = json_encode([
            'group' => [
                'name' => $name,
            ],
        ]);

        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/groups/create", [], $json);
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        if (isset($json['group']['id'], $json['group']['name'])) {
            return new Group($json['group']['id'], $json['group']['name']);
        } else {
            throw new ApiFormatException("unexpected JSON response: " . json_encode($json));
        }
    }

    /**
     * Retrieves a list of all the groups that have been created in this OA.
     *
     * @return Group[]
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function all ()
    {
        $request = new Request("GET", "https://api.weixin.qq.com/cgi-bin/groups/get");
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);
        $groups = [];

        if (isset($json['groups'])) {
            foreach ($json['groups'] as $group) {
                $groups[] = new Group($group['id'], $group['name'], $group['count']);
            }
        }

        return $groups;
    }

    /**
     * Allows the updating of a group.
     *
     * @param Group $group
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function update (Group $group)
    {
        $json = [
            'group' => [
                'id'   => $group->id(),
                'name' => $group->name(),
            ],
        ];

        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/groups/update", [], json_encode($json));
        $this->client->send($request);
    }
}
