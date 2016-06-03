<?php

namespace Garbetjie\WeChatClient\Media;

use DateTime;
use Garbetjie\WeChatClient\Media\Exception\MediaException;
use Garbetjie\WeChatClient\Media\ItemType\ArticleMedia;
use Garbetjie\WeChatClient\Media\ItemType\ArticleMediaItem;
use Garbetjie\WeChatClient\Media\ItemType\LocalMedia;
use Garbetjie\WeChatClient\Media\ItemType\RemoteMedia;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

class MediaService extends Service
{
    /**
     * Uploads the supplied media item to the WeChat API.
     *
     * This method assumes that the item has not been uploaded previously, and so will ignore any previously created
     * date and media id that has been set. The supplied media item will be modified.
     *
     * @param LocalMedia|ArticleMedia $mediaItem
     *
     * @return RemoteMedia
     *
     * @throws InvalidArgumentException
     * @throws MediaException
     */
    public function uploadTemporaryItem ($mediaItem)
    {
        // Must be a local media item or an article media item.
        $this->ensureMediaItem($mediaItem);

        if ($mediaItem instanceof ArticleMedia) {
            return $this->uploadArticle(false, $mediaItem);
        } else {
            return $this->uploadFile(false, $mediaItem);
        }
    }
    
    private function ensureMediaItem ($mediaItem)
    {
        if (! ($mediaItem instanceof LocalMedia || $mediaItem instanceof ArticleMedia)) {
            throw new InvalidArgumentException(
                sprintf(
                    "unexpected value %s%s given as media item for upload.",
                    gettype($mediaItem),
                    is_object($mediaItem) ? " (" . get_class($mediaItem) . ")" : ''
                )
            );
        }
    }
    
    public function uploadPermanentItem ($mediaItem)
    {
        // Must be a local media item or an article media item.
        $this->ensureMediaItem($mediaItem);

        if ($mediaItem instanceof ArticleMedia) {
            return $this->uploadArticle(true, $mediaItem);
        } else {
            return $this->uploadFile(true, $mediaItem);
        }
    }

    /**
     * Uploads the given media file to the WeChat content servers, and populates the item's ID and created date.
     *
     * @param LocalMedia $media
     *
     * @return RemoteMedia
     *
     * @throws InvalidArgumentException
     */
    protected function uploadFile ($isPermanent, LocalMedia $media)
    {
        if ($media->getPath() === null) {
            throw new InvalidArgumentException("path not set when uploading media item. cannot upload.");
        }

        $stream = fopen($media->getPath(), 'rb');
        if (! $stream) {
            throw new InvalidArgumentException("unable to open `{$media->getPath()}` for reading.");
        }
        
        if ($isPermanent) {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/material/add_material';
        } else {
            $endpoint = 'http://api.weixin.qq.com/cgi-bin/media/upload';
        }

        $json = json_decode(
            $this->client->send(
                new Request(
                    'POST',
                    Uri::withQueryValue(new Uri($endpoint), 'type', $media->getType()),
                    [],
                    new MultipartStream ([
                        [
                            'name'     => 'media',
                            'contents' => $stream,
                        ],
                    ])
                )
            )->getBody()
        );

        $remoteMedia = new RemoteMedia(
            $media->getType(),
            $media->getType() == MediaType::THUMBNAIL ? $json->thumb_media_id : $json->media_id
        );
        
        if (isset($json->created_at)) {
            $remoteMedia = $remoteMedia->withLastModifiedDate(DateTime::createFromFormat('U', $json->created_at));
        }
        
        return $remoteMedia;
    }

    /**
     * Uploads the given news article items to the WeChat content servers, and populates the media item's ID and created
     * date.
     *
     * @param ArticleMedia $media
     *
     * @return RemoteMedia
     *
     * @throws MediaException
     */
    protected function uploadArticle ($isPermanent, ArticleMedia $media)
    {
        $jsonBody = [
            'articles' => [],
        ];

        foreach ($media->getItems() as $item) {
            $article = [
                'title'          => $item->getTitle(),
                'content'        => $item->getContent(),
                'thumb_media_id' => $item->getThumbnailMediaID(),
            ];

            if ($item->getAuthor() !== null) {
                $article['author'] = $item->getAuthor();
            }

            if ($item->getURL() !== null) {
                $article['content_source_url'] = $item->getURL();
            }

            if ($item->getSummary() !== null) {
                $article['digest'] = $item->getSummary();
            }

            $article['show_cover_pic'] = $item->isImageShowing() ? true : false;

            $jsonBody['articles'][] = $article;
        }
        
        if ($isPermanent) {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/material/add_news';
        } else {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews';
        }

        $json = json_decode(
            $this->client->send(
                new Request(
                    'POST',
                    $endpoint,
                    [],
                    json_encode($jsonBody)
                )
            )->getBody()
        );
        
        if (isset($json->media_id, $json->created_at)) {
            return new RemoteMedia(MediaType::ARTICLE, $json->media_id, new DateTime("@{$json->created_at}"));
        } else {
            throw new MediaException("bad response: expected properties `media_id`, `created_at`");
        }
    }

    /**
     * Downloads the given media item from the WeChat API.
     *
     * The media item must have been uploaded previously, and so must have its ID set.
     *
     * If no file is specified using the `$into` parameter, then a temporary file resource is created using the
     * `tmpfile()` function.
     *
     * @param string          $mediaID The media item to download.
     * @param resource|string $into    Optional file or file resource to download the media item into.
     *
     * @return resource
     *
     * @throws InvalidArgumentException
     */
    public function download ($mediaID, $into = null)
    {
        // Open file for writing.
        if (is_resource($into)) {
            $stream = $into;
        } elseif (is_string($into)) {
            $stream = fopen($into, 'wb');
            if (! $stream) {
                throw new InvalidArgumentException("Can't open file `{$into}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        $request = new Request('GET', "http://api.weixin.qq.com/cgi-bin/media/get?media_id={$mediaID}");
        $response = $this->client->send($request, [RequestOptions::SINK => $stream]);

        return $response->getBody()->detach();
    }

    /**
     * Paginates all the media items that have been uploaded to the WeChat API.
     *
     * @param string $type   - One the MediaType::* constants, indicating the type of media to download.
     * @param int    $offset - The offset index (starts at 0).
     * @param int    $limit  - The maximum number of items to return (max: 20)
     *
     * @return array
     * @throws MediaException
     */
    public function paginate ($type, $offset = 0, $limit = 20)
    {
        // Ensure the limit is within range.
        if ($limit < 1) {
            $limit = 1;
        } elseif ($limit > 20) {
            $limit = 20;
        }

        // Send query.
        $json = json_decode(
            $this->client->send(
                new Request(
                    'GET',
                    'https://api.weixin.qq.com/cgi-bin/material/batchget_material',
                    [],
                    json_encode([
                        'type'   => $type,
                        'offset' => $offset,
                        'count'  => $limit,
                    ])
                )
            )->getBody()
        );

        // Ensure response formatting.
        if (! isset($json->total_count, $json->item_count, $json->item)) {
            throw new MediaException("bad response: expecting properties `total_count`, `item_count`, `item`");
        }

        $items = [];

        // Parse items into their object representations.
        foreach ($json->item as $remoteItem) {
            // We're paginating articles.
            if ($type === MediaType::ARTICLE) {
                foreach ($remoteItem->content->news_item as $newsItem) {
                    $items[] = (new ArticleMediaItem(
                        $newsItem->title,
                        $newsItem->content,
                        $newsItem->thumb_media_id
                    ))->withAuthor($newsItem->author)
                        ->withURL($newsItem->content_source_url)
                        ->withSummary($newsItem->digest)
                        ->withImageShowing($newsItem->show_cover_pic);
                }
            } // Any other item type.
            else {
                $items[] = (new RemoteMedia(
                    $type,
                    $remoteItem->media_id
                ))->withLastModifiedDate(new DateTime("@{$remoteItem->update_time}"))
                  ->withURL($remoteItem->url);
            }
        }

        // Return the paginated results.
        return [
            'offset' => $offset,
            'total'  => $json->total_count,
            'items'  => $items,
        ];
    }
}
