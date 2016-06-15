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
            $args = [];

            foreach (['title', 'description', 'url', 'image'] as $key) {
                $args[] = isset($item[$key]) ? $item[$key] : null;
            }

            $this->items[] = call_user_func_array([$this, 'validateItem'], $args);
        }
    }

    /**
     * Validates and returns the formatted news item.
     * 
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $image
     *
     * @return array
     */
    private function validateItem ($title, $description, $url, $image)
    {
        // Basic validation.
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL for \'$url\'');
        }

        if (filter_var($image, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL for \'$image\'');
        }
        
        return compact('title', 'description', 'url', 'image');
    }

    /**
     * Adds a new item to the rich media message. Messaging type is immutable, so a cloned instance will be returned.
     *
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $image
     *
     * @return News
     */
    public function withItem ($title, $description, $url, $image)
    {
        $item = $this->validateItem($title, $description, $url, $image);

        $cloned = clone $this;
        $cloned->items[] = $item;

        return $cloned;
    }

    /**
     * @return array
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
