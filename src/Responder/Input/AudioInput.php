<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class AudioInput extends AbstractInput
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

    /**
     * AudioMediaType constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        parent::__construct($xml);
        
        $this->mediaID = (string)$xml->MediaId;
        $this->format = (string)$xml->Format;
        $this->recognition = (string)$xml->Recognition;
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
    public function getEmittedEvent ()
    {
        return 'audio';
    }
}
