<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

class AudioResultSet extends ResultSet
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
