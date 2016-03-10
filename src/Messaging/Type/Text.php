<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Text extends AbstractType
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
