<?php

namespace Garbetjie\WeChatClient\Media\Type;

abstract class FileMedia
{
    /**
     * @var string
     */
    protected $path;

    /**
     * FileMedia constructor.
     *
     * @param string $path
     */
    public function __construct ($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath ()
    {
        return $this->path;
    }
}
