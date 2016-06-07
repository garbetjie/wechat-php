<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class PaginatedThumbnail extends Paginated
{
    /**
     * @return Thumbnail[]
     */
    public function getItems ()
    {
        return parent::getItems();
    }

    /**
     * @param \stdClass $item
     *
     * @return Thumbnail
     */
    protected function expand ($item)
    {
        return new Thumbnail($item->media_id);
    }

}
