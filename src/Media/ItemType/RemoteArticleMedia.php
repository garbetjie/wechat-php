<?php

namespace Garbetjie\WeChatClient\Media\ItemType;

class RemoteArticleMedia extends ArticleMedia
{
    /**
     * @var string
     */
    private $mediaID;

    /**
     * RemoteArticleMedia constructor.
     *
     * @param string $id
     */
    public function __construct ($id)
    {
        $this->mediaID = $id;
    }

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }
}
