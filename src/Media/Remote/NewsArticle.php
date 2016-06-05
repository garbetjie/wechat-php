<?php

namespace Garbetjie\WeChatClient\Media\Remote;

use Garbetjie\WeChatClient\Media\NewsArticle as LocalNewsArticle;

class NewsArticle extends LocalNewsArticle
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
