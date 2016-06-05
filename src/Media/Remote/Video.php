<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class Video extends Remote
{
    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'video';
    }
}
