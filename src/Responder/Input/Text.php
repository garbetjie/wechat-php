<?php

namespace WeChat\Responder\Input;

use SimpleXMLElement;

class Text extends AbstractInput
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
     * @param                  $menu
     */
    public function __construct (SimpleXMLElement $xml, $menu)
    {
        $this->content = (string)$xml->Content;
        $this->menu = (bool)$menu;
    }

    /**
     * The text content sent in.
     * 
     * @return string
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Indicates whether this was text sent via a button on the menu.
     * 
     * @return bool
     */
    public function menu()
    {
        return $this->menu;
    }

    /**
     * @return string
     */
    public function emits ()
    {
        return 'text';
    }
}
