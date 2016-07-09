<?php

namespace Garbetjie\WeChatClient\Responder\Input;

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
    private $thumbnailMediaID;

    public function __construct ($sender, $recipient, $messageID, $createdDate, $mediaID, $thumbnailMediaID, $shortVideo = false)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->sight = $shortVideo;
        $this->mediaID = $mediaID;
        $this->thumbnailMediaID = $thumbnailMediaID;
    }

    /**
     * The media ID for this video.
     * 
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * The media ID for this video's thumbnail image.
     * 
     * @return string
     */
    public function getThumbnailMediaID ()
    {
        return $this->thumbnailMediaID;
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
