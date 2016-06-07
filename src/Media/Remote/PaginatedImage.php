<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class PaginatedImage extends Paginated
{
    /**
     * @return Image[]
     */
    public function getItems ()
    {
        return parent::getItems();
    }

    /**
     * @param \stdClass $item
     *
     * @return Image
     */
    protected function expand ($item)
    {
        return (new Image($item->media_id))->withURL($item->url);
    }
}
