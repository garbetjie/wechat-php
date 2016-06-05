<?php

namespace Garbetjie\WeChatClient\Media;

class Audio extends FileMedia
{
    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'voice';
    }

}
