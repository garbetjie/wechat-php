<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class PaginatedVideo extends Paginated
{
    /**
     * @return Video[]
     */
    public function getItems ()
    {
        return parent::getItems();
    }
    
    /**
     * @param \stdClass $item
     *
     * @return Video
     */
    protected function expand ($item)
    {
        return (new Video($item->media_id));
    }

}
