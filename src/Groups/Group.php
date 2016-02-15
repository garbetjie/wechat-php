<?php

namespace Garbetjie\WeChat\Groups;

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
    public function __construct ( $id, $name, $userCount = 0 )
    {
        // Convert from default Chinese group names to English.
        if ( $id == 0 && $name == '未分组' ) {
            $name = 'Ungrouped';
        } else if ( $id == 1 && $name == '黑名单' ) {
            $name = 'Blacklisted';
        } else if ( $id == 2 && $name == '星标组' ) {
            $name = 'Starred';
        }

        $this->id = (int) $id;
        $this->name = (string) $name;
        $this->userCount = (int) $userCount;

        if ( $this->userCount < 0 ) {
            $this->userCount = 0;
        }
    }

    /**
     * @return int
     */
    public function id ()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name ()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function users ()
    {
        return $this->userCount;
    }
}
