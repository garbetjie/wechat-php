<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class UploadedNews extends Uploaded implements TypeInterface
{
    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'mpnews';
    }
}
