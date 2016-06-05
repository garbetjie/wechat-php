<?php

namespace Garbetjie\WeChatClient\Media\Remote;

use Garbetjie\WeChatClient\Media\News as LocalNews;

class News extends LocalNews 
{
    /**
     * @var string
     */
    private $mediaID;

    /**
     * News constructor.
     *
     * @param string $mediaID
     */
    public function __construct ($mediaID)
    {
        $this->mediaID = $mediaID;
    }

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * @inheritDoc
     */
    public function withItem (NewsItem $item)
    {
        return parent::withItem($item);
    }

    /**
     * @inheritDoc
     * 
     * @return NewsItem[]
     */
    public function getItems ()
    {
        return parent::getItems();
    }
}
