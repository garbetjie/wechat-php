<?php

namespace Garbetjie\WeChatClient\Media;

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

    /**
     * @inheritdoc
     */
    public function withItem (RemoteArticleMediaItem $item)
    {
        return parent::withItem($item);
    }

    /**
     * @inheritdoc
     * 
     * @return RemoteArticleMediaItem[]
     */
    public function getItems ()
    {
        return parent::getItems();
    }
}
