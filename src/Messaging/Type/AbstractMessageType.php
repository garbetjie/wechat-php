<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use Garbetjie\WeChatClient\Messaging\Type\MessageTypeInterface;

abstract class AbstractMessageType implements MessageTypeInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @return string
     */
    public function getType ()
    {
        return $this->type;
    }
}
