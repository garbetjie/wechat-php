<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Image extends Uploaded implements TypeInterface
{
    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'image';
    }
}
