<?php

namespace Garbetjie\WeChatClient\Groups;

class Group
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $userCount;

    /**
     * Group constructor.
     *
     * @param int    $id
     * @param string $name
     * @param int    $userCount
     */
    public function __construct ($id, $name, $userCount = 0)
    {
        // Convert from default Chinese group names to English.
        if ($id == 0 && $name == '未分组') {
            $name = 'Ungrouped';
        } elseif ($id == 1 && $name == '黑名单') {
            $name = 'Blacklisted';
        } elseif ($id == 2 && $name == '星标组') {
            $name = 'Starred';
        }

        $this->id = (int)$id;
        $this->name = (string)$name;
        $this->userCount = (int)$userCount;

        if ($this->userCount < 0) {
            $this->userCount = 0;
        }
    }

    /**
     * @return int
     */
    public function getID ()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Modifies the group's name. Returns a new instance of the modified group.
     * 
     * @param string $name
     *
     * @return Group
     */
    public function withName ($name)
    {
        $cloned = clone $this;
        $cloned->name = $name;
        
        return $cloned;
    }

    /**
     * @return int
     */
    public function getUserCount ()
    {
        return $this->userCount;
    }
}
