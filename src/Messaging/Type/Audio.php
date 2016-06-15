<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Audio extends Uploaded implements TypeInterface
{
    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'voice';
    }
}
