<?php

namespace Garbetjie\WeChatClient\Media;

use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;

class MediaUploadService extends Service
{
    /**
     * Upload a news item for permanent storage.
     *
     * @param News $news
     *
     * @return Uploaded\News
     */
    public function uploadPermanentNews (News $news)
    {
        $json = $this->uploadNews('https://api.weixin.qq.com/cgi-bin/material/add_news', $news);
        
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
    public function uploadTemporaryNews (News $news)
    {
        $json = $this->uploadNews('https://api.weixin.qq.com/cgi-bin/media/uploadnews', $news);
        
        return (new Uploaded\News($json->media_id))->withExpiresDate($this->getExpiryDate($json->created_at));
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
    public function uploadPermanentVideo (Video $video)
    {
        if ($video->getTitle() === null || $video->getDescription() === null) {
            throw new InvalidArgumentException("permanent videos must have a title and description");
        }

        return $this->uploadVideo(true, $video);
    }

    /**
     * Uploads a video item for temporary storage on the WeChat servers.
     *
     * @param Video $video
     *
     * @return Uploaded\Video
     */
    public function uploadTemporaryVideo (Video $video)
    {
        return $this
            ->uploadVideo(false, $video)
            ->withExpiresDate(new \DateTime('@' . (time() + 172800))); // @todo: change to use the JSON's created_at property.
    }

    /**
     * Uploads an image for permanent storage on the WeChat servers.
     *
     * @param Image $image
     *
     * @return Uploaded\Image
     */
    public function uploadPermanentImage (Image $image)
    {
        $json = $this->uploadGenericMedia(true, $image);

        return new Uploaded\Image($json->media_id, $json->url);
    }

    /**
     * Uploads an image for temporary storage on the WeChat servers.
     *
     * @param Image $image
     *
     * @return Uploaded\Image
     */
    public function uploadTemporaryImage (Image $image)
    {
        $json = $this->uploadGenericMedia(false, $image);

        return (new Uploaded\Image($json->media_id, $json->url))
            ->withExpiresDate($this->getExpiryDate($json->created_at));
    }

    /**
     * Uploads  a thumbnail for permanent storage on the WeChat servers.
     *
     * @param Thumbnail $thumbnail
     *
     * @return Uploaded\Thumbnail
     */
    public function uploadPermanentThumbnail (Thumbnail $thumbnail)
    {
        $json = $this->uploadGenericMedia(true, $thumbnail);

        return new Uploaded\Thumbnail($json->thumb_media_id);
    }

    /**
     * Uploads a thumbnail for temporary storage on the WeChat servers.
     *
     * @param Thumbnail $thumbnail
     *
     * @return Uploaded\Thumbnail
     */
    public function uploadTemporaryThumbnail (Thumbnail $thumbnail)
    {
        $json = $this->uploadGenericMedia(false, $thumbnail);

        return (new Uploaded\Thumbnail($json->media_id))->withExpiresDate($this->getExpiryDate($json->created_at));
    }

    /**
     * Uploads an audio item for permanent storage on the WeChat servers.
     *
     * @param Audio $audio
     *
     * @return Uploaded\Audio
     */
    public function uploadPermanentAudio (Audio $audio)
    {
        $json = $this->uploadGenericMedia(true, $audio);

        return new Uploaded\Audio($json->media_id);
    }

    /**
     * Uploads an audio item for temporary storage on the WeChat servers.
     *
     * @param Audio $audio
     *
     * @return Uploaded\Audio
     */
    public function uploadTemporaryAudio (Audio $audio)
    {
        $json = $this->uploadGenericMedia(false, $audio);

        return (new Uploaded\Audio($json->media_id))->withExpiresDate($this->getExpiryDate($json->created_at));
    }

    /**
     * Returns a `\DateTime` instance, representing the date & time at which the media item should be considered
     * expired.
     *
     * @param int $createdAt
     *
     * @return \DateTime
     */
    private function getExpiryDate ($createdAt)
    {
        return new \DateTime('@' . ($createdAt + 172800));
    }

    /**
     * @param bool      $isPermanent
     * @param FileMedia $item
     *
     * @return \stdClass
     */
    private function uploadGenericMedia ($isPermanent, FileMedia $item)
    {
        $endpoint = 'http://api.weixin.qq.com/cgi-bin/media/upload';
        if ($isPermanent) {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/material/add_material';
        }

        // Shortcut to uploading video.
        return $this->doMultipartUpload(
            $endpoint,
            $item->getType(),
            $this->createReadableStream($item->getPath())
        );
    }

    /**
     * @param Video $video
     *
     * @return Uploaded\Video
     */
    private function uploadVideo ($isPermanent, Video $video)
    {
        $endpoint = 'https://api.weixin.qq.com/cgi-bin/media/upload';
        if ($isPermanent) {
            $endpoint = 'https://api.weixin.qq.com/cgi-bin/material/add_material';
        }

        $json = $this->doMultipartUpload(
            $endpoint,
            $video->getType(),
            $this->createReadableStream($video->getPath()),
            [
                [
                    'name'    => 'description',
                    'content' => json_encode([
                        'title'        => $video->getTitle(),
                        'introduction' => $video->getDescription(),
                    ]),
                ],
            ]
        );

        return new Uploaded\Video($json->media_id, $json->url);
    }

    /**
     * Handles the uploading of a news item to the WeChat servers.
     *
     * @param string $endpoint
     * @param News   $news
     *
     * @return \stdClass
     */
    private function uploadNews ($endpoint, News $news)
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
            $news->getType(),
            json_encode($jsonBody)
        );
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
                    new MultipartStream (array_merge([
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
