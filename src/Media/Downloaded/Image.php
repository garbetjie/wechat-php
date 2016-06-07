<?php

namespace Garbetjie\WeChatClient\Media\Downloaded;

class Image extends Downloaded
{
    /**
     * @var string
     */
    private $url;

    /**
     * @return string
     */
    public function getURL ()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return static
     */
    public function withURL ($url)
    {
        $new = clone $this;
        $new->url = $url;

        return $new;
    }
}
