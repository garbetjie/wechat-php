<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

use Garbetjie\WeChatClient\Service\Messaging\Type\AbstractMediaMessageType;

class AudioMessageMessageType extends AbstractMediaMessageType
{
    protected $type = 'voice';
}
