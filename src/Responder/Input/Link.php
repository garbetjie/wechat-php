<?php

namespace WeChat\Responder\Input;

use SimpleXMLElement;

class Link extends AbstractInput
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
     * Link constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        $this->title = (string) $xml->Title;
        $this->description = (string) $xml->Description;
        $this->url = (string) $xml->Url;
    }

    /**
     * Title given to the link.
     * 
     * @return string
     */
    public function title ()
    {
        return $this->title;
    }

    /**
     * Custom description given to the link.
     * 
     * @return string
     */
    public function description ()
    {
        return $this->description;
    }

    /**
     * The URL of the link.
     * 
     * @return string
     */
    public function url ()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function emits ()
    {
        return 'link';
    }
}
