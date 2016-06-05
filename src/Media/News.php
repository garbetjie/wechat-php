<?php

namespace Garbetjie\WeChatClient\Media;

class News
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
     * @param NewsArticle $item
     *
     * @return static
     */
    public function withItem (NewsArticle $item)
    {
        $new = clone $this;
        $new->items[] = $item;
        
        return $new;
    }

    /**
     * @return NewsArticle[]
     */
    public function getItems ()
    {
        return $this->items;
    }
}
