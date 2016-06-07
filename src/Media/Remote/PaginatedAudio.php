<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class PaginatedAudio extends Paginated
{
    /**
     * @return Audio
     */
    protected function expand ($item)
    {
        return new Audio($item->media_id);
    }

    /**
     * @return Audio
     */
    public function getItems ()
    {
        return parent::getItems();
    }
}
