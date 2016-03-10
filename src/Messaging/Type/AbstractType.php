<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @return string
     */
    public function type ()
    {
        return $this->type;
    }
}
