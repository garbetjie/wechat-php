<?php

namespace Garbetjie\WeChatClient\Media\Downloaded;

use Garbetjie\WeChatClient\Media\NewsItem as SourceNewsItem;

class NewsItem extends SourceNewsItem
{
    /**
     * @var string
     */
    private $displayURL;

    /**
     * @return string
     */
    public function getDisplayURL ()
    {
        return $this->displayURL;
    }

    /**
     * @param $url
     *
     * @return $this
     */
    public function withDisplayURL ($url)
    {
        $new = clone $this;
        $new->displayURL = $url;

        return $new;
    }
}
