<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

class NewsResultSet extends ResultSet
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
            $newsItem = new NewsItem(
                $rawNewsItem->title,
                $rawNewsItem->content,
                $rawNewsItem->thumb_media_id
            );

            if (strlen($rawNewsItem->author) > 0) {
                $newsItem = $newsItem->withAuthor($rawNewsItem->author);
            }

            if (strlen($rawNewsItem->content_source_url) > 0) {
                $newsItem = $newsItem->withURL($rawNewsItem->content_source_url);
            }

            if (strlen($rawNewsItem->digest) > 0) {
                $newsItem = $newsItem->withSummary($rawNewsItem->digest);
            }

            if (strlen($rawNewsItem->url) > 0) {
                $newsItem = $newsItem->withDisplayURL($rawNewsItem->url);
            }
            
            $newsItem = $newsItem
                ->withImageShowing(!! $rawNewsItem->show_cover_pic)
                ->withThumbnailURL($rawNewsItem->thumb_url);
                
            $news = $news->withItem($newsItem);
        }
        
        return $news;
    }


}
