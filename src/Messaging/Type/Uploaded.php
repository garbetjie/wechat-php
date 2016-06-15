<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

abstract class Uploaded
{
    /**
     * The ID of the media item to be sent (as given when uploading to WeChat).
     *
     * @var string
     */
    private $mediaID;

    /**
     * @param string $id
     */
    public function __construct ($id)
    {
        $this->mediaID = $id;
    }

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }
}
