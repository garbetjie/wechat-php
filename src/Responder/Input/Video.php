<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Video extends Input
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

    public function __construct ($sender, $recipient, $messageID, $createdDate, $mediaID, $thumbnailMediaID, $shortVideo)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->sight = $shortVideo;
        $this->mediaID = $mediaID;
        $this->thumbnailID = $thumbnailMediaID;
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
    public function getEmittedType ()
    {
        return Type::VIDEO;
    }
}
