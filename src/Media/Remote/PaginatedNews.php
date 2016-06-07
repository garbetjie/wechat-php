<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class PaginatedNews extends Paginated
{
    /**
     * @return News
     */
    public function getItems ()
    {
        return parent::getItems();
    }

    /**
     * @inheritDoc
     */
    protected function expand ($item)
    {
        $news = new News($item->media_id);
        
        foreach ($item->content->news_item as $rawNewsItem) {
            $newsItem = (new NewsItem(
                $rawNewsItem->title,
                $rawNewsItem->content,
                $rawNewsItem->thumb_media_id
            ))
                ->withAuthor($rawNewsItem->author)
                ->withSummary($rawNewsItem->digest)
                ->withURL($rawNewsItem->content_source_url)
                ->withImageShowing($rawNewsItem->show_cover_pic)
                ->withDisplayURL($rawNewsItem->url)
                ->withThumbnailURL($rawNewsItem->thumb_url);
                
            $news = $news->withItem($newsItem);
        }
        
        return $news;
    }


}
