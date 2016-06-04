<?php

namespace Garbetjie\WeChatClient\Media;

class ArticleMedia
{
    /**
     * @var array
     */
    protected $items = [];
    
    /**
     * @return string
     */
    public function getType ()
    {
        return 'news';
    }

    /**
     * Adds a new item to the article.
     *
     * @param ArticleMediaItem $item
     *
     * @return static
     */
    public function withItem (ArticleMediaItem $item)
    {
        $new = clone $this;
        $new->items[] = $item;
        
        return $new;
    }

    /**
     * @return ArticleMediaItem[]
     */
    public function getItems ()
    {
        return $this->items;
    }
}
