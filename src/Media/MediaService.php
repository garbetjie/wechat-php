<?php

namespace Garbetjie\WeChatClient\Media;

use DateTime;
use Garbetjie\WeChatClient\Media\MediaException;
use Garbetjie\WeChatClient\Media\News;
use Garbetjie\WeChatClient\Media\NewsItem;
use Garbetjie\WeChatClient\Media\FileMedia;
use Garbetjie\WeChatClient\Media\RemoteNews;
use Garbetjie\WeChatClient\Media\RemoteNewsArticle;
use Garbetjie\WeChatClient\Media\RemoteFileMedia;
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
     * @param FileMedia|News $mediaItem
     *
     * @return RemoteFileMedia
     *
     * @throws InvalidArgumentException
     * @throws MediaException
     */
    public function uploadTemporaryItem ($mediaItem)
    {
        // Must be a local media item or an article media item.
        $this->ensureMediaItem($mediaItem);

        if ($mediaItem instanceof News) {
            return $this->uploadArticle(false, $mediaItem);
        } else {
            return $this->uploadFile(false, $mediaItem);
        }
    }

    /**
     * Ensures the given media item is an instance of one of the media item classes. Throws an exception if the given
     * media item is not one of them.
     * 
     * @param FileMedia|News $mediaItem
     * 
     * @throws InvalidArgumentException
     */
    private function ensureMediaItem ($mediaItem)
    {
        if (! ($mediaItem instanceof FileMedia || $mediaItem instanceof News)) {
            throw new InvalidArgumentException(
                sprintf(
                    "unexpected value %s%s given as media item for upload.",
                    gettype($mediaItem),
                    is_object($mediaItem) ? " (" . get_class($mediaItem) . ")" : ''
                )
            );
        }
    }

    /**
     * Uploads the given media item for permanent storage on the WeChat servers.
     * 
     * @param FileMedia|News $mediaItem
     *
     * @return RemoteFileMedia
     * @throws MediaException
     * @throws InvalidArgumentException
     */
    public function uploadPermanentItem ($mediaItem)
    {
        // Must be a local media item or an article media item.
        $this->ensureMediaItem($mediaItem);

        if ($mediaItem instanceof News) {
            return $this->uploadArticle(true, $mediaItem);
        } else {
            return $this->uploadFile(true, $mediaItem);
        }
    }

    /**
     * Uploads the given media file to the WeChat content servers, and populates the item's ID and created date.
     *
     * @param FileMedia $media
     *
     * @return RemoteFileMedia
     *
     * @throws InvalidArgumentException
     */
    protected function uploadFile ($isPermanent, FileMedia $media)
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

        $remoteMedia = new RemoteFileMedia(
            $media->getType(),
            isset($json->thumb_media_id) ? $json->thumb_media_id : $json->media_id
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
     * @param News $media
     *
     * @return RemoteFileMedia
     *
     * @throws MediaException
     */
    protected function uploadArticle ($isPermanent, News $media)
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

        if (! isset($json->media_id)) {
            throw new MediaException("bad response: expected property `media_id`");
        }

        $remoteMedia = new RemoteFileMedia($media->getType(), $json->media_id);

        if (isset($json->created_at)) {
            $remoteMedia = $remoteMedia->withLastModifiedDate(new DateTime("@{$json->created_at}"));
        }

        return $remoteMedia;
    }

    private function createStream ($file)
    {
        if (is_resource($file)) {
            $stream = $file;
        } elseif (is_string($file)) {
            $stream = fopen($file, 'wb');
            if (! $stream) {
                throw new InvalidArgumentException("Can't open file `{$file}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        return $stream;
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
    public function downloadTemporary ($mediaID, $into = null)
    {
        return $this->client->send(
            new Request(
                'GET',
                "http://api.weixin.qq.com/cgi-bin/media/get?media_id={$mediaID}"
            ),
            [
                RequestOptions::SINK => $this->createStream($into),
            ]
        )->getBody()
         ->detach();
    }

    /**
     * Downloads the given permanent media item. The contents are returned as a stream.
     *
     * If the media item downloaded is a news article, then the stream will contain the JSON representation of it.
     * Otherwise, it will contain the raw data for the image or video that is downloaded.
     *
     * @param string               $mediaID - The ID of the media item.
     * @param string|resource|null $into    - Where to download the item into.
     *
     * @return resource
     * @throws InvalidArgumentException
     */
    public function downloadPermanent ($mediaID, $into = null)
    {
        $response = $this->client->send(
            new Request(
                'POST',
                'https://api.weixin.qq.com/cgi-bin/material/get_material',
                [],
                json_encode([
                    'media_id' => $mediaID,
                ])
            ),
            [
                RequestOptions::SINK => $this->createStream($into),
            ]
        );

        return $response->getBody()->detach();
    }

    /**
     * Paginates all the media items that have been uploaded to the WeChat API.
     *
     * @param string $type   - One the MediaType::* constants, indicating the type of media to download.
     * @param int    $offset - The offset index (starts at 0).
     * @param int    $limit  - The maximum number of items to return (max: 20)
     *
     * @return RemoteFileMedia[]|RemoteArticleMedia[]
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
                    'POST',
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
                $articleItem = (new RemoteArticleMedia($remoteItem->media_id));
                
                foreach ($remoteItem->content->news_item as $newsItem) {
                    $articleItem = $articleItem->withItem(
                        (new RemoteNewsArticle(
                            $newsItem->title,
                            $newsItem->content,
                            $newsItem->thumb_media_id
                        ))->withAuthor($newsItem->author)
                          ->withURL($newsItem->url)
                          ->withSummary($newsItem->digest)
                          ->withImageShowing($newsItem->show_cover_pic)
                          ->withDisplayURL($newsItem->url)
                    );
                }
                
                $items[] = $articleItem;
            } // Any other item type.
            else {
                $items[] = (new RemoteFileMedia(
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
