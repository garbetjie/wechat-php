<?php

namespace Garbetjie\WeChatClient\Media\Remote;

abstract class Remote
{
    /**
     * @var string
     */
    protected $mediaID;

    /**
     * Remote constructor.
     *
     * @param string $mediaID
     */
    public function __construct ($mediaID)
    {
        $this->mediaID = $mediaID;
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
}
