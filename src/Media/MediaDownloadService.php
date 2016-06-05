<?php

namespace Garbetjie\WeChatClient\Media;

use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

class MediaDownloadService extends Service
{
    /**
     * Downloads a temporary image.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Image
     */
    public function downloadTemporaryImage ($mediaID, $into = null)
    {
        return new Remote\Image($mediaID, $this->doTemporaryDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads a permanent image.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Image
     */
    public function downloadPermanentImage ($mediaID, $into = null)
    {
        return new Remote\Image($mediaID, $this->doPermanentDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads a temporary audio item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Audio
     */
    public function downloadTemporaryAudio ($mediaID, $into = null)
    {
        return new Remote\Audio($mediaID, $this->doTemporaryDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads a permanent audio item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Audio
     */
    public function downloadPermanentAudio ($mediaID, $into = null)
    {
        return new Remote\Audio($mediaID, $this->doPermanentDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads a temporary thumbnail item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Thumbnail
     */
    public function downloadTemporaryThumbnail ($mediaID, $into = null)
    {
        return new Remote\Thumbnail($mediaID, $this->doTemporaryDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads a permanent thumbnail item.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Thumbnail
     */
    public function downloadPermanentThumbnail ($mediaID, $into = null)
    {
        return new Remote\Thumbnail($mediaID, $this->doPermanentDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads the temporary news item, and returns an object representation of it.
     *
     * @param string $mediaID
     *
     * @return Remote\News
     */
    public function downloadTemporaryNews ($mediaID)
    {
        $stream = $this->doTemporaryDownloadToStream($mediaID, null);
        $json = json_decode(stream_get_contents($stream));

        return $this->expandNews($json->news_item, new Remote\News($mediaID));
    }

    /**
     * Downloads the specified permanent news item, and returns an object representation of it.
     *
     * @param string $mediaID
     *
     * @return Remote\News
     */
    public function downloadPermanentNews ($mediaID)
    {
        $stream = $this->doPermanentDownloadToStream($mediaID, null);
        $json = json_decode(stream_get_contents($stream));

        return $this->expandNews($json->news_item, new Remote\News($mediaID));
    }

    /**
     * Downloads a temporary video.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Video
     */
    public function downloadTemporaryVideo ($mediaID, $into = null)
    {
        return new Remote\Video($mediaID, $this->doTemporaryDownloadToStream($mediaID, $into));
    }

    /**
     * Downloads the given video into the specified file.
     *
     * @param string               $mediaID
     * @param null|string|resource $into
     *
     * @return Remote\Video
     */
    public function downloadPermanentVideo ($mediaID, $into = null)
    {
        $stream = $this->doPermanentDownloadToStream($mediaID, null);
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

        return (new Remote\Video($mediaID, $body->detach()))
            ->withTitle($json->title)
            ->withDescription($json->description);
    }

    /**
     * Expands the given news media, with its items into an object.
     *
     * @param array       $newsItems
     * @param Remote\News $news
     *
     * @return Remote\News
     */
    private function expandNews (array $newsItems, Remote\News $news)
    {
        foreach ($newsItems as $newsItem) {
            $news = $news->withItem(
                (new Remote\NewsItem(
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
     * Downloads the generic media item into the given destination.
     *
     * @param string               $mediaID
     * @param string|null|resource $into
     *
     * @return resource
     */
    private function doTemporaryDownloadToStream ($mediaID, $into)
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
    private function doPermanentDownloadToStream ($mediaID, $into)
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
}
