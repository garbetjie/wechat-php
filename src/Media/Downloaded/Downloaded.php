<?php

namespace Garbetjie\WeChatClient\Media\Downloaded;

abstract class Downloaded
{
    /**
     * @var string
     */
    private $mediaID;
    
    /**
     * @var resource
     */
    protected $stream;

    /**
     * Downloaded constructor.
     *
     * @param string $mediaID
     * @param resource $stream
     */
    public function __construct ($mediaID, $stream)
    {
        $this->mediaID = $mediaID;
        $this->stream = $stream;
    }

    /**
     * @return resource
     */
    public function getStream ()
    {
        return $this->stream;
    }

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }
}
