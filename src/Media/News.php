<?php

namespace Garbetjie\WeChatClient\Media;

class News
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Adds a new item to the article.
     *
     * @param NewsItem $item
     *
     * @return static
     */
    public function withItem (NewsItem $item)
    {
        $new = clone $this;
        $new->items[] = $item;
        
        return $new;
    }

    /**
     * @return NewsItem[]
     */
    public function getItems ()
    {
        return $this->items;
    }

    /**
     * Creates a new news item from the given one.
     * 
     * @param News|Downloaded\News|Remote\News $news
     * 
     * @return News
     * @throws \InvalidArgumentException
     */
    static public final function createFrom ($news)
    {
        if (!($news instanceof News || $news instanceof Downloaded\News || $news instanceof Remote\News)) {
            throw new \InvalidArgumentException('$news must be a `News` instance');
        }
        
        $obj = new self();
        
        foreach ($news->getItems() as $newsItem) {
            $objItem = new NewsItem($newsItem->getTitle(), $newsItem->getContent(), $newsItem->getThumbnailMediaID());
            
            if ($newsItem->getAuthor() !== null) {
                $objItem = $objItem->withAuthor($newsItem->getAuthor());
            }
            
            if ($newsItem->getSummary() !== null) {
                $objItem = $objItem->withSummary($newsItem->getSummary());
            }
            
            if ($newsItem->getURL() !== null) {
                $objItem = $objItem->withURL($newsItem->getURL());
            }
            
            $objItem = $objItem->withImageShowing($newsItem->isImageShowing());
            
            $obj = $obj->withItem($objItem);
        }
        
        return $obj;
    }
}
