<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use InvalidArgumentException;

class News implements TypeInterface
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @param array $items
     */
    public function __construct (array $items = [])
    {
        // Populate items if given.
        foreach ($items as $item) {
            if (! ($item instanceof NewsItem)) {
                throw new InvalidArgumentException('news item must be instance of ' . NewsItem::class);
            }
            
            $this->items[] = $item;
        }
    }

    /**
     * Adds a new item to the rich media message. Messaging type is immutable, so a cloned instance will be returned.
     *
     * @param NewsItem $newsItem - The news item to add to the news message.
     *
     * @return News
     */
    public function withItem (NewsItem $newsItem)
    {
        $cloned = clone $this;
        $cloned->items[] = $newsItem;

        return $cloned;
    }

    /**
     * @return NewsItem[]
     */
    public function getItems ()
    {
        return $this->items;
    }
    
    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'news';
    }
}
