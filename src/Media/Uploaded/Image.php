<?php

namespace Garbetjie\WeChatClient\Media\Uploaded;

class Image extends Uploaded
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
     * @return Image
     */
    public function withURL ($url)
    {
        $new = clone $this;
        $new->url = $url;
        
        return $new;
    }
}
