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
    private $path;

    /**
     * The DateTime at which this media item was created. Is NULL if it hasn't been uploaded/created yet.
     *
     * @var DateTime|null
     */
    private $uploaded;

    /**
     * The media ID of this item.
     *
     * @var null|string
     */
    private $id;

    /**
     * AbstractMediaType constructor.
     *
     * @param string $path - The path to the media item on the local file system.
     */
    public function __construct ($path)
    {
        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getID ()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getPath ()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function getUploadDate ()
    {
        return $this->uploaded;
    }

    /**
     * @inheritdoc
     */
    public function setID ($id)
    {
        $cloned = clone $this;
        $cloned->id = $id;
        
        return $cloned;
    }

    /**
     * @inheritdoc
     */
    public function setPath ($path)
    {
        $cloned = clone $this;
        $cloned->path = $path;
        
        return $cloned;
    }

    /**
     * @inheritdoc
     */
    public function setUploadDate (\DateTime $uploaded)
    {
        $cloned = clone $this;
        $cloned->uploaded = clone $uploaded;
        
        return $cloned;
    }
}
