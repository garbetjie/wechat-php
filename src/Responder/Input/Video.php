<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Video extends AbstractInput
{
    /**
     * @var bool
     */
    private $sight = false;

    /**
     * @var string
     */
    private $mediaID;

    /**
     * @var string
     */
    private $thumbnailID;

    /**
     * VideoMediaType constructor.
     *
     * @param SimpleXMLElement $xml
     * @param bool             $sight
     */
    public function __construct (SimpleXMLElement $xml, $sight)
    {
        $this->sight = (bool)$sight;
        $this->mediaID = (string)$xml->MediaId;
        $this->thumbnailID = (string)$xml->ThumbnailId;
    }

    /**
     * The media ID for this video.
     * 
     * @return string
     */
    public function mediaID ()
    {
        return $this->mediaID;
    }

    /**
     * The media ID for this video's thumbnail image.
     * 
     * @return string
     */
    public function thumbnailID ()
    {
        return $this->thumbnailID;
    }

    /**
     * Returns boolean value indicating whether this is a short "sight" video.
     * 
     * @return bool
     */
    public function isSight ()
    {
        return $this->sight;
    }

    /**
     * @return string
     */
    public function emits ()
    {
        return 'video';
    }
}
