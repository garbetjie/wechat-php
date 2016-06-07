<?php

namespace Garbetjie\WeChatClient\Media\Downloaded;

class News
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @return array
     */
    public function getItems ()
    {
        return $this->items;
    }

    /**
     * Adds the item to the cloned object.
     *
     * @param mixed $item
     *
     * @return static
     */
    public function withItem ($item)
    {
        $new = clone $this;
        $new->items[] = $item;

        return $new;
    }
}
