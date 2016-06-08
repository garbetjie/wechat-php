<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

class ImageResultSet extends ResultSet
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
