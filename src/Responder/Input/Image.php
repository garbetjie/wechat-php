<?php

namespace Garbetjie\WeChatClient\Responder\Input;

class Image extends Input
{
    /**
     * @var string
     */
    private $mediaID;

    /**
     * @var string
     */
    private $url;

    public function __construct ($sender, $recipient, $messageID, $createdDate, $mediaID, $url)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->mediaID = (string)$mediaID;
        $this->url = (string)$url;
    }

    /**
     * ImageMediaType's media ID.
     *
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * URL from which the image can be downloaded.
     *
     * @return string
     */
    public function getURL ()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getEmittedType ()
    {
        return Type::IMAGE;
    }
}
