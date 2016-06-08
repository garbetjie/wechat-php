<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

abstract class Paginated
{
    /**
     * @var string
     */
    protected $mediaID;

    /**
     * Paginated constructor.
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
    public function getMediaID ()
    {
        return $this->mediaID;
    }
}
