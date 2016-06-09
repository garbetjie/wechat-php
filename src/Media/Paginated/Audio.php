<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

class Audio extends Paginated
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     *
     * @return Audio
     */
    public function withName ($name)
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }
}
