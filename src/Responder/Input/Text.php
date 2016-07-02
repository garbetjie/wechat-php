<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Text extends Input
{
    /**
     * @var string
     */
    private $content = '';

    /**
     * Text constructor.
     *
     * @param string $content
     */
    public function __construct ($sender, $recipient, $messageID, $createdDate, $content)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->content = (string)$content;
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
    public function getEmittedType ()
    {
        return Type::TEXT;
    }
}
