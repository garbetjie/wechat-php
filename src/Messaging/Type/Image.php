<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Image extends Uploaded
{
    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'image';
    }
}
