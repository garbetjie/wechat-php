<?php

namespace Garbetjie\WeChatClient\Service\Groups;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Service\Groups\Exception\BadGroupsResponseFormatException;
use Garbetjie\WeChatClient\Service\Groups\Group;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Garbetjie\WeChatClient\Client;

class GroupsService extends Service
{
    /**
     * Create a new group.
     *
     * @param string $name
     *
     * @return Group
     *
     * @throws GuzzleException
     * @throws BadResponseFormatException
     * @throws APIErrorException
     */
    public function createGroup ($name)
    {
        $json = json_encode([
            'group' => [
                'name' => $name,
            ],
        ]);

        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/groups/create", [], $json);
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());
        
        if (isset($json->group->id, $json->group->name)) {
            return new Group($json->group->id, $json->group->name);
        } else {
            throw new BadGroupsResponseFormatException("expected properties :`id`, `name`", $response);
        }
    }

    /**
     * Retrieves a list of all the groups that have been created in this OA.
     *
     * @return Group[]
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function getAllGroups ()
    {
        $request = new Request("GET", "https://api.weixin.qq.com/cgi-bin/groups/get");
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());
        $groups = [];

        if (isset($json->groups)) {
            foreach ($json->groups as $group) {
                $groups[] = new Group($group->id, $group->name, $group->count);
            }
        } else {
            throw new BadGroupsResponseFormatException("expected property: `groups`", $response);
        }

        return $groups;
    }

    /**
     * Allows the updating of a group.
     *
     * @param Group $group
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function updateGroup (Group $group)
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
