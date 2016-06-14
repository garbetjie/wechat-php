<?php

namespace Garbetjie\WeChatClient\Media\Type;

class News
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Adds a new item to the article.
     *
     * @param NewsItem $item
     *
     * @return static
     */
    public function withItem (NewsItem $item)
    {
        $new = clone $this;
        $new->items[] = $item;
        
        return $new;
    }

    /**
     * @return NewsItem[]
     */
    public function getItems ()
    {
        return $this->items;
    }
}
