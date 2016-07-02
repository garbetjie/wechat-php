<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Audio extends Input
{
    /**
     * @var string
     */
    private $mediaID;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $recognition;

    public function __construct ($sender, $recipient, $messageID, $createdDate, $mediaID, $format, $recognizedSpeech)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->recognition = $recognizedSpeech;
        $this->format = $format;
        $this->mediaID = $mediaID;
    }


    /**
     * Returns the media ID of the audio item.
     *
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * Returns the format of the audio item.
     *
     * @return string
     */
    public function getFormat ()
    {
        return $this->format;
    }

    /**
     * If speech recognition is enabled & was successful, the extract text will be returned.
     *
     * @return string
     */
    public function getParsedText ()
    {
        return $this->recognition;
    }

    /**
     * @return string
     */
    public function getEmittedType ()
    {
        return Type::AUDIO;
    }
}
