<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Text extends AbstractType
{
    /**
     * The type of the message, as used with the WeChat API.
     */
    protected $type = 'text';

    /**
     * @var string
     */
    protected $content = '';

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
