<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

use Garbetjie\WeChatClient\Service\Messaging\Type\MessageTypeInterface;

abstract class AbstractMessageType implements MessageTypeInterface
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
