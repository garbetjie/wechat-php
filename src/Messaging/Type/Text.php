<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Text implements TypeInterface
{
    /**
     * The contents of the text message.
     *
     * @var string
     */
    private $content;

    /**
     * @param string $content
     */
    public function __construct ($content)
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

    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'text';
    }


}
