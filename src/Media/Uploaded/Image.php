<?php

namespace Garbetjie\WeChatClient\Media\Uploaded;

class Image extends Uploaded
{
    /**
     * @var string
     */
    private $url;

    /**
     * Image constructor.
     *
     * @param string $mediaID
     * @param string $url
     */
    public function __construct ($mediaID, $url)
    {
        parent::__construct($mediaID);

        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getURL ()
    {
        return $this->url;
    }
}
