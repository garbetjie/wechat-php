<?php

namespace Garbetjie\WeChatClient\Media;

class Image extends FileMedia
{
    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'image';
    }
}
