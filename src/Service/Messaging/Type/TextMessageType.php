<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

use Garbetjie\WeChatClient\Service\Messaging\Type\AbstractMessageType;

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
    public $content;

    /**
     * @param string $content
     */
    public function __construct ( $content )
    {
        $this->content = $content;
    }
}
