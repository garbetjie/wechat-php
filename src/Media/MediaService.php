<?php

namespace Garbetjie\WeChatClient\Media;

use DateTime;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

class MediaService extends Service
{
    /**
     * Downloads a temporary image.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Image
     */
    public function fetchTemporaryImage ($mediaID, $into = null)
    {
        return new Downloaded\Image($mediaID, $this->doTemporaryFetchToStream($mediaID, $into));
    }

    /**
     * Downloads a permanent image.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Image
     */
    public function fetchPermanentImage ($mediaID, $into = null)
    {
        return new Downloaded\Image($mediaID, $this->doPermanentFetchToStream($mediaID, $into));
    }

    /**
     * Downloads a temporary audio item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Audio
     */
    public function fetchTemporaryAudio ($mediaID, $into = null)
    {
        return new Downloaded\Audio($mediaID, $this->doTemporaryFetchToStream($mediaID, $into));
    }

    /**
     * Downloads a permanent audio item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Audio
     */
    public function fetchPermanentAudio ($mediaID, $into = null)
    {
        return new Downloaded\Audio($mediaID, $this->doPermanentFetchToStream($mediaID, $into));
    }

    /**
     * Downloads a temporary thumbnail item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Thumbnail
     */
    public function fetchTemporaryThumbnail ($mediaID, $into = null)
    {
        return new Downloaded\Thumbnail($mediaID, $this->doTemporaryFetchToStream($mediaID, $into));
    }

    /**
     * Downloads a permanent thumbnail item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Thumbnail
     */
    public function fetchPermanentThumbnail ($mediaID, $into = null)
    {
        return new Downloaded\Thumbnail($mediaID, $this->doPermanentFetchToStream($mediaID, $into));
    }

    /**
     * Downloads the temporary news item, and returns an object representation of it.
     *
     * @param string $mediaID
     *
     * @return Downloaded\News
     */
    public function fetchTemporaryNews ($mediaID)
    {
        $stream = $this->doTemporaryFetchToStream($mediaID, null);
        $json = json_decode(stream_get_contents($stream));

        return $this->expandNews($json->news_item, new Downloaded\News($mediaID));
    }

    /**
     * Downloads the specified permanent news item, and returns an object representation of it.
     *
     * @param string $mediaID
     *
     * @return Downloaded\News
     */
    public function fetchPermanentNews ($mediaID)
    {
        $stream = $this->doPermanentFetchToStream($mediaID, null);
        $json = json_decode(stream_get_contents($stream));

        return $this->expandNews($json->news_item, new Downloaded\News($mediaID));
    }

    /**
     * Downloads a temporary video.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Video
     */
    public function fetchTemporaryVideo ($mediaID, $into = null)
    {
        return new Downloaded\Video($mediaID, $this->doTemporaryFetchToStream($mediaID, $into));
    }

    /**
     * Downloads the given video into the specified file.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Downloaded\Video
     */
    public function fetchPermanentVideo ($mediaID, $into = null)
    {
        $stream = $this->doPermanentFetchToStream($mediaID, null);
        $json = json_decode(stream_get_contents($stream));

        $body = $this->client->send(
            new Request(
                'GET',
                $json->down_url
            ),
            [
                RequestOptions::SINK => $this->createWritableStream($into),
            ]
        )->getBody();

        if ($body->isSeekable()) {
            $body->seek(0);
        }

        return (new Downloaded\Video($mediaID, $body->detach()))
            ->withTitle($json->title)
            ->withDescription($json->description);
    }
    
    /**
     * Upload a news item for permanent storage.
     *
     * @param News $news
     *
     * @return Uploaded\News
     */
    public function storePermanentNews (News $news)
    {
        $json = $this->storeNews('https://api.weixin.qq.com/cgi-bin/material/add_news', $news);

        return new Uploaded\News($json->media_id);
    }

    /**
     * Upload a news item for temporary storage. It will expire after 3 days, but we'll set the expiry to 2 days, to be
     * safe when it comes to time zones.
     *
     * @param News $news
     *
     * @return Uploaded\News
     */
    public function storeTemporaryNews (News $news)
    {
        $json = $this->storeNews('https://api.weixin.qq.com/cgi-bin/media/uploadnews', $news);

        return (new Uploaded\News($json->media_id))->withExpiresDate($this->createExpiryDate($json->created_at));
    }

    /**
     * Uploads a video for permanent storage to the WeChat servers. The video supplied must have had the title and
     * description populated.
     *
     * @param Video $video
     *
     * @return Uploaded\Video
     * @throws InvalidArgumentException
     */
    public function storePermanentVideo (Video $video)
    {
        if ($video->getTitle() === null || $video->getDescription() === null) {
            throw new InvalidArgumentException("permanent videos must have a title and description");
        }

        return $this->storeVideo(true, $video);
    }

    /**
     * Uploads a video item for temporary storage on the WeChat servers.
     *
     * @param Video $video
     *
     * @return Uploaded\Video
     */
    public function storeTemporaryVideo (Video $video)
    {
        return $this
            ->storeVideo(false, $video)
            ->withExpiresDate(new \DateTime('@' . (time() + 172800))); // @todo: change to use the JSON's created_at property.
    }

    /**
     * Uploads an image for permanent storage on the WeChat servers.
     *
     * @param Image $image
     *
     * @return Uploaded\Image
     */
    public function storePermanentImage (Image $image)
    {
        $json = $this->storeGenericMedia(MediaType::IMAGE, true, $image);

        return (new Uploaded\Image($json->media_id))->withURL($json->url);
    }

    /**
     * Uploads an image for temporary storage on the WeChat servers.
     *
     * @param Image $image
     *
     * @return Uploaded\Image
     */
    public function storeTemporaryImage (Image $image)
    {
        $json = $this->storeGenericMedia(MediaType::IMAGE, false, $image);

        return (new Uploaded\Image($json->media_id))
            ->withExpiresDate($this->createExpiryDate($json->created_at));
    }

    /**
     * Uploads  a thumbnail for permanent storage on the WeChat servers.
     *
     * @param Thumbnail $thumbnail
     *
     * @return Uploaded\Thumbnail
     */
    public function storePermanentThumbnail (Thumbnail $thumbnail)
    {
        $json = $this->storeGenericMedia(MediaType::THUMBNAIL, true, $thumbnail);

        return new Uploaded\Thumbnail($json->media_id);
    }

    /**
     * Uploads a thumbnail for temporary storage on the WeChat servers.
     *
     * @param Thumbnail $thumbnail
     *
     * @return Uploaded\Thumbnail
     */
    public function storeTemporaryThumbnail (Thumbnail $thumbnail)
    {
        $json = $this->storeGenericMedia(MediaType::THUMBNAIL, false, $thumbnail);

        return (new Uploaded\Thumbnail($json->thumb_media_id))
            ->withExpiresDate($this->createExpiryDate($json->created_at));
    }

    /**
     * Uploads an audio item for permanent storage on the WeChat servers.
     *
     * @param Audio $audio
     *
     * @return Uploaded\Audio
     */
    public function storePermanentAudio (Audio $audio)
    {
        $json = $this->storeGenericMedia(MediaType::AUDIO, true, $audio);

        return new Uploaded\Audio($json->media_id);
    }

    /**
     * Uploads an audio item for temporary storage on the WeChat servers.
     *
     * @param Audio $audio
     *
     * @return Uploaded\Audio
     */
    public function storeTemporaryAudio (Audio $audio)
    {
        $json = $this->storeGenericMedia(MediaType::AUDIO, false, $audio);

        return (new Uploaded\Audio($json->media_id))
            ->withExpiresDate($this->createExpiryDate($json->created_at));
    }

    /**
     * Expands the given news media, with its items into an object.
     *
     * @param array       $newsItems
     * @param Downloaded\News $news
     *
     * @return Downloaded\News
     */
    private function expandNews (array $newsItems, Downloaded\News $news)
    {
        foreach ($newsItems as $newsItem) {
            $news = $news->withItem(
                (new Downloaded\NewsItem(
                    $newsItem->title,
                    $newsItem->content,
                    $newsItem->thumb_media_id
                ))
                    ->withAuthor($newsItem->author)
                    ->withURL($newsItem->content_source_url)
                    ->withSummary($newsItem->digest)
                    ->withImageShowing($newsItem->show_cover_pic)
                    ->withDisplayURL($newsItem->url)
            );
        }

        return $news;
    }

    /**
     * Paginates through images stored on the WeChat servers.
     * 
     * @param int $offset - The offset from which to start showing items.
     * @param int $count - The number of items to show.
     *
     * @return Remote\PaginatedImage
     * @throws MediaException
     */
    public function paginateImages ($offset = 0, $count = 20)
    {
        $json = $this->paginate(MediaType::IMAGE, $offset, $count);
print_r("=====  paginated images =====\n");
print_r($json);
print_r("=====  /paginated images =====\n");
        return new Remote\PaginatedImage($json->total_count, $json->item);
    }

    /**
     * Paginates through videos stored on the WeChat servers.
     *
     * @param int $offset - The offset from which to start showing items.
     * @param int $count - The number of items to show.
     *
     * @return Remote\PaginatedVideo
     * @throws MediaException
     */
    public function paginateVideos ($offset = 0, $count = 20)
    {
        $json = $this->paginate(MediaType::VIDEO, $offset, $count);
        print_r("=====  paginated videos =====\n");
        print_r($json);
        print_r("=====  /paginated videos =====\n");
        return new Remote\PaginatedVideo($json->total_count, $json->item);
    }

    /**
     * Paginates through audio items stored on the WeChat servers.
     *
     * @param int $offset - The offset from which to start showing items.
     * @param int $count - The number of items to show.
     *
     * @return Remote\PaginatedAudio
     * @throws MediaException
     */
    public function paginateAudio ($offset = 0, $count = 20)
    {
        $json = $this->paginate(MediaType::AUDIO, $offset, $count);
        print_r("=====  paginated audio =====\n");
        print_r($json);
        print_r("=====  /paginated audio =====\n");
        return new Remote\PaginatedAudio($json->total_count, $json->item);
    }

    /**
     * Paginates through news items stored on the WeChat servers.
     *
     * @param int $offset - The offset from which to start showing items.
     * @param int $count - The number of items to show.
     *
     * @return Remote\PaginatedNews
     * @throws MediaException
     */
    public function paginateNews ($offset = 0, $count = 20)
    {
        $json = $this->paginate(MediaType::ARTICLE, $offset, $count);
        print_r("=====  paginated news =====\n");
        print_r($json);
        print_r("=====  /paginated news =====\n");
        return new Remote\PaginatedNews($json->total_count, $json->item);
    }

    /**
     * Performs the actual pagination request for the given item type.
     * 
     * @param string $type - The type of media item to paginate.
     * @param int    $offset - The offset from which to start showing items.
     * @param int    $limit - The number of items to show.
     *
     * @return \stdClass
     * @throws MediaException
     */
    private function paginate ($type, $offset, $limit)
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
        if (! isset($json->total_count, $json->item)) {
            throw new MediaException("bad response: expecting properties `total_count`, `item_count`, `item`");
        }
        
        return $json;
    }

    /**
     * Downloads the generic media item into the given destination.
     *
     * @param string               $mediaID
     * @param string|null|resource $into
     *
     * @return resource
     */
    private function doTemporaryFetchToStream ($mediaID, $into)
    {
        $body = $this->client->send(
            new Request(
                'GET',
                "http://api.weixin.qq.com/cgi-bin/media/get?media_id={$mediaID}"
            ),
            [
                RequestOptions::SINK => $this->createWritableStream($into),
            ]
        )->getBody();

        if ($body->isSeekable()) {
            $body->seek(0);
        }

        return $body->detach();
    }

    /**
     * Downloads the generic permanent media item into the given destination.
     *
     * @param string               $mediaID
     * @param string|null|resource $into
     *
     * @return resource
     */
    private function doPermanentFetchToStream ($mediaID, $into)
    {
        $body = $this->client->send(
            new Request(
                'POST',
                'https://api.weixin.qq.com/cgi-bin/material/get_material',
                [],
                json_encode([
                    'media_id' => $mediaID,
                ])
            ),
            [
                RequestOptions::SINK => $this->createWritableStream($into),
            ]
        )->getBody();

        if ($body->isSeekable()) {
            $body->seek(0);
        }

        return $body->detach();
    }

    /**
     * Creates a writable stream that downloaded contents can be stored in.
     *
     * @param string|null $filePath
     *
     * @return resource
     */
    private function createWritableStream ($filePath)
    {
        if (is_resource($filePath)) {
            $stream = $filePath;
        } elseif (is_string($filePath)) {
            $stream = fopen($filePath, 'wb');
            if (! $stream) {
                throw new InvalidArgumentException("Can't open file `{$filePath}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        return $stream;
    }

    /**
     * @param string $path
     *
     * @return resource
     */
    private function createReadableStream ($path)
    {
        if ($path === null) {
            throw new InvalidArgumentException("path not set when uploading media item. cannot upload.");
        }

        $stream = fopen($path, 'rb');
        if (! $stream) {
            throw new InvalidArgumentException("unable to open `{$path}` for reading.");
        }

        return $stream;
    }

    /**
     * Returns a `\DateTime` instance, representing the date & time at which the media item should be considered
     * expired.
     *
     * @param int $createdAt
     *
     * @return \DateTime
     */
    private function createExpiryDate ($createdAt)
    {
        return new \DateTime('@' . ($createdAt + 172800));
    }

    /**
     * @param string    $type
     * @param bool      $isPermanent
     * @param FileMedia $item
     *
     * @return \stdClass
     */
    private function storeGenericMedia ($type, $isPermanent, FileMedia $item)
    {
        $endpoint = 'http://api.weixin.qq.com/cgi-bin/media/upload';
        if ($isPermanent) {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/material/add_material';
        }

        // Shortcut to uploading video.
        return $this->doMultipartUpload(
            $endpoint,
            $type,
            $this->createReadableStream($item->getPath())
        );
    }

    /**
     * @param Video $video
     *
     * @return Uploaded\Video
     */
    private function storeVideo ($isPermanent, Video $video)
    {
        $endpoint = 'https://api.weixin.qq.com/cgi-bin/media/upload';
        if ($isPermanent) {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/material/add_material';
        }

        $json = $this->doMultipartUpload(
            $endpoint,
            MediaType::VIDEO,
            $this->createReadableStream($video->getPath()),
            [
                [
                    'name'     => 'description',
                    'contents' => json_encode([
                        'title'        => $video->getTitle(),
                        'introduction' => $video->getDescription(),
                    ]),
                ],
            ]
        );

        return new Uploaded\Video($json->media_id);
    }

    /**
     * Handles the uploading of a news item to the WeChat servers.
     *
     * @param string $endpoint
     * @param News   $news
     *
     * @return \stdClass
     */
    private function storeNews ($endpoint, News $news)
    {
        $jsonBody = ['articles' => []];

        foreach ($news->getItems() as $newsArticle) {
            $jsonArticle = [
                'title'          => $newsArticle->getTitle(),
                'content'        => $newsArticle->getContent(),
                'thumb_media_id' => $newsArticle->getThumbnailMediaID(),
            ];

            if ($newsArticle->getAuthor() !== null) {
                $jsonArticle['author'] = $newsArticle->getAuthor();
            }

            if ($newsArticle->getURL() !== null) {
                $jsonArticle['content_source_url'] = $newsArticle->getURL();
            }

            if ($newsArticle->getSummary() !== null) {
                $jsonArticle['digest'] = $newsArticle->getSummary();
            }

            $jsonArticle['show_cover_pic'] = $newsArticle->isImageShowing() ? true : false;

            $jsonBody['articles'][] = $jsonArticle;
        }

        return $this->doUpload(
            $endpoint,
            MediaType::ARTICLE,
            json_encode($jsonBody)
        );
    }

    /**
     * Uploads a simple string body to the WeChat servers.
     *
     * @param string $endpoint
     * @param string $type
     * @param string $body
     *
     * @return \stdClass
     */
    private function doUpload ($endpoint, $type, $body)
    {
        return json_decode(
            $this->client->send(
                new Request(
                    'POST',
                    Uri::withQueryValue(new Uri($endpoint), 'type', $type),
                    [],
                    $body
                )
            )->getBody()
        );
    }

    /**
     * Performs a multipart upload to the WeChat API. Additional multipart fields can be added in if required.
     *
     * @param string   $endpoint
     * @param string   $type
     * @param resource $stream
     * @param array    $extra
     *
     * @return \stdClass
     */
    private function doMultipartUpload ($endpoint, $type, $stream, array $extra = [])
    {
        return json_decode(
            $this->client->send(
                new Request(
                    'POST',
                    Uri::withQueryValue(new Uri($endpoint), 'type', $type),
                    [],
                    new MultipartStream(array_merge([
                        [
                            'name'     => 'media',
                            'contents' => $stream,
                        ],
                    ], $extra))
                )
            )->getBody()
        );
    }
}
