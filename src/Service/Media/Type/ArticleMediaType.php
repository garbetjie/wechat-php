<?php

namespace Garbetjie\WeChatClient\Service\Media\Type;

use Garbetjie\WeChatClient\Service\Media\Type\AbstractMediaType;
use InvalidArgumentException;

class ArticleMediaType extends AbstractMediaType
{
    /**
     * @var string
     */
    protected $type = 'news';

    /**
     * @var array
     */
    private $items = [];

    /**
     * Adds a new item to the article.
     *
     * @param array $item
     */
    public function addItem (array $item)
    {
        $formatted = [];

        // Check required keys.
        foreach (['title', 'content', 'thumbnail'] as $key) {
            if (! isset($item[$key])) {
                throw new InvalidArgumentException("MenuItem key '{$key}' is required.");
            } else {
                $formatted[$key] = $item[$key];
            }
        }

        // Add additional keys.
        foreach (['author', 'url', 'summary', 'cover'] as $key) {
            $formatted[$key] = isset($item[$key]) ? $item[$key] : null;
        }

        $this->items[] = $formatted;
    }

    /**
     * @return array
     */
    public function items ()
    {
        return $this->items;
    }
}
