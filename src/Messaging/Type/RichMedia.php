<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use InvalidArgumentException;

class RichMedia extends AbstractType
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

            call_user_func_array([$this, 'addItem'], $item);
        }
    }

    /**
     * Adds a new item to the rich media message.
     *
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $image
     */
    public function addItem ($title, $description, $url, $image)
    {
        // Basic validation.
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL for \'$url\'');
        }

        if (filter_Var($image, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL for \'$image\'');
        }

        $this->items[] = compact('title', 'description', 'url', 'image');
    }

    /**
     * @return array
     */
    public function items ()
    {
        return $this->items;
    }
}
