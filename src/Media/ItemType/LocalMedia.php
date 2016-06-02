<?php

namespace Garbetjie\WeChatClient\Media\ItemType;

final class LocalMedia
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $path;

    /**
     * LocalMedia constructor.
     *
     * @param string $path
     */
    public function __construct ($type, $path)
    {
        $this->type = $type;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath ()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getType ()
    {
        return $this->type;
    }
}
