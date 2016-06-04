<?php

namespace Garbetjie\WeChatClient\Media;

class RemoteArticleMediaItem extends ArticleMediaItem
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
