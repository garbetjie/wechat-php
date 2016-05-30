<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class LinkInput extends AbstractInput
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $url;

    /**
     * LinkInput constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->title = (string)$xml->Title;
        $this->description = (string)$xml->Description;
        $this->url = (string)$xml->Url;
    }

    /**
     * Title given to the link.
     *
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * Custom description given to the link.
     *
     * @return string
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * The URL of the link.
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
    public function getEmittedEvent ()
    {
        return 'link';
    }
}
