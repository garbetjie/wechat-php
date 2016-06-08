<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

class Image extends Paginated
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
