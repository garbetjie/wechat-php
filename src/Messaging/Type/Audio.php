<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Audio extends Uploaded
{
    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'voice';
    }
}
