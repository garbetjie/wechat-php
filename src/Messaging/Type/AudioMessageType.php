<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMediaMessageType;

class AudioMessageType extends AbstractMediaMessageType
{
    protected $type = 'voice';
}
