<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class TextInput extends AbstractInput
{
    /**
     * @var string
     */
    private $content = '';

    /**
     * @var bool
     */
    private $menu = false;

    /**
     * Text constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->content = (string)$xml->Content;
    }

    /**
     * The text content sent in.
     *
     * @return string
     */
    public function getContent ()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getEmittedEvent ()
    {
        return 'text';
    }
}
