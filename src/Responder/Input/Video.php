<?php

namespace Garbetjie\WeChat\Responder\Input;

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
     * Video constructor.
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
     * The media ID for this video's thumbnai image.
     * 
     * @return string
     */
    public function thumbnailID ()
    {
        return $this->thumbnailID;
    }

    /**
     * @return string
     */
    public function emits ()
    {
        return 'video';
    }
}
