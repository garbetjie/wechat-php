<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class Thumbnail extends Remote
{
    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'thumbnail';
    }
}
