<?php

namespace Garbetjie\WeChatClient\Media\Remote;

use Garbetjie\WeChatClient\Media\NewsItem as SourceNewsItem;

class NewsItem extends SourceNewsItem
{
    /**
     * @var string
     */
    private $displayURL;

    /**
     * @var string
     */
    private $thumbnailURL;

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

    /**
     * @return string
     */
    public function getThumbnailURL ()
    {
        return $this->thumbnailURL;
    }

    /**
     * @param string $url
     *
     * @return NewsItem
     */
    public function withThumbnailURL ($url)
    {
        $new = clone $this;
        $new->thumbnailURL = $url;
        
        return $new;
    }
}
