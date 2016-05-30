<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

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
