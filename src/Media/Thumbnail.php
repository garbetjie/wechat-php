<?php

namespace Garbetjie\WeChatClient\Media;

class Thumbnail extends FileMedia
{
    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'thumbnail';
    }
}
