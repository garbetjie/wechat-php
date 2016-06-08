<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

class VideoResultSet extends ResultSet
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
