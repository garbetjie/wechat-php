<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class Audio extends Remote
{

    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'voice';
    }
}
