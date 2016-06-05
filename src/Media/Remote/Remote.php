<?php

namespace Garbetjie\WeChatClient\Media\Remote;

abstract class Remote
{
    /**
     * @var string
     */
    protected $mediaID;

    /**
     * @var resource
     */
    protected $stream;

    /**
     * Remote constructor.
     *
     * @param string $mediaID
     */
    public function __construct ($mediaID, $stream)
    {
        $this->mediaID = $mediaID;
        $this->stream = $stream;
    }
    
    /**
     * @return string
     */
    abstract public function getType ();

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * @return resource
     */
    public function getStream ()
    {
        return $this->stream;
    }
}
