<?php

namespace Garbetjie\WeChatClient\Service\Media\Type;

use DateTime;
use Garbetjie\WeChatClient\Service\Media\Type\MediaTypeInterface;

abstract class AbstractMediaType implements MediaTypeInterface
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * The path to where this media item is stored on disk.
     *
     * @var null|string
     */
    public $path;

    /**
     * The DateTime at which this media item was created. Is NULL if it hasn't been uploaded/created yet.
     *
     * @var DateTime|null
     */
    public $created;

    /**
     * The media ID of this item.
     *
     * @var null|string
     */
    public $id;

    /**
     * Returns the type of this media item for use in the WeChat API.
     *
     * @return string
     */
    public function type ()
    {
        return $this->type;
    }
}
