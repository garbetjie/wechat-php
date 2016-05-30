<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMessageType;

class TextMessageType extends AbstractMessageType
{
    /**
     * The type of the message, as used with the WeChat API.
     */
    protected $type = 'text';

    /**
     * The contents of the text message.
     * 
     * @var string
     */
    private $content;

    /**
     * @param string $content
     */
    public function __construct ( $content )
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent ()
    {
        return $this->content;
    }
}
