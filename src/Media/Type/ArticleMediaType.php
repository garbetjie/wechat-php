<?php

namespace Garbetjie\WeChatClient\Media\Type;

use Garbetjie\WeChatClient\Media\Type\AbstractMediaType;
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
     * ArticleMediaType constructor.
     */
    public function __construct ()
    {
        parent::__construct(null);
    }

    /**
     * Adds a new item to the article.
     *
     * @param array $item
     * 
     * @return ArticleMediaType
     */
    public function withItem (array $item)
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

        $cloned = clone $this;
        $cloned->items[] = $formatted;
        
        return $cloned;
    }

    /**
     * @return array
     */
    public function getItems ()
    {
        return $this->items;
    }
}
