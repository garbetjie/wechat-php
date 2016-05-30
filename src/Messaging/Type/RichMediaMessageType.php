<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMessageType;
use InvalidArgumentException;

class RichMediaMessageType extends AbstractMessageType
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

            $self = call_user_func_array([$this, 'withItem'], $item);
        }

        // Set items.
        if (isset($self)) {
            $this->items = $self->items;
        }
    }

    /**
     * Adds a new item to the rich media message. Messaging type is immutable, so a cloned instance will be returned.
     *
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $image
     *
     * @return RichMediaMessageType
     */
    public function withItem ($title, $description, $url, $image)
    {
        // Basic validation.
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL for \'$url\'');
        }

        if (filter_var($image, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL for \'$image\'');
        }

        $cloned = clone $this;
        $cloned->items[] = compact('title', 'description', 'url', 'image');

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
