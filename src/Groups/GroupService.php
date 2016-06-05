<?php

namespace Garbetjie\WeChatClient\Groups;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Groups\GroupsException;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class GroupService extends Service
{
    /**
     * Create a new group.
     *
     * @param string $name
     *
     * @return Group
     *
     * @throws GroupsException
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
            throw new GroupsException("bad response: expected properties `id`, `name`");
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
            throw new GroupsException("bad response: expected property `groups`");
        }

        return $groups;
    }

    /**
     * Returns the details for a single group. If no group with the given ID is found, null will be returned.
     * 
     * @param int $groupID
     *
     * @return Group|null
     * @throws GroupsException
     */
    public function getGroup ($groupID)
    {
        $groups = $this->getAllGroups();
        
        foreach ($groups as $group) {
            if ($group->getID() == $groupID) {
                return $group;
            }
        }
        
        return null;
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
                'id'   => $group->getID(),
                'name' => $group->getName(),
            ],
        ];

        $request = new Request("POST", "https://api.weixin.qq.com/cgi-bin/groups/update", [], json_encode($json));
        $this->client->send($request);
    }

    /**
     * Deletes the supplied group from the WeChat API. All users in this group will be moved to the default group
     * (which is probably the "Ungrouped" group).
     * 
     * @param Group $group
     */
    public function deleteGroup (Group $group)
    {
        $this->client->send(
            new Request(
                'POST',
                'https://api.weixin.qq.com/cgi-bin/groups/delete',
                [],
                json_encode([
                    'group' => [
                        'id' => $group->getID(),
                    ],
                ])
            )
        );
    }
}
