<?php

namespace Garbetjie\WeChatClient\Media;

use DateTime;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Media\Exception\IOException;
use Garbetjie\WeChatClient\Media\Exception\BadMediaResponseFormatException;
use Garbetjie\WeChatClient\Media\Exception\BadMediaItemException;
use Garbetjie\WeChatClient\Media\Type\AbstractMediaType;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Garbetjie\WeChatClient\Media\Type\ArticleMediaType;
use Garbetjie\WeChatClient\Media\Type\MediaTypeInterface;

class MediaService extends Service
{
    /**
     * Uploads the supplied media item to the WeChat API.
     *
     * This method assumes that the item has not been uploaded previously, and so will ignore any previously created
     * date and media id that has been set. The supplied media item will be modified.
     *
     * @param MediaTypeInterface $media
     * 
     * @return MediaTypeInterface
     * 
     * @throws BadMediaItemException
     * @throws BadMediaResponseFormatException
     */
    public function upload (MediaTypeInterface $media)
    {
        if ($media->getType() === 'news') {
            return $this->uploadArticle($media);
        } else {
            return $this->uploadFile($media);
        }
    }

    /**
     * Uploads the given media file to the WeChat content servers, and populates the item's ID and created date.
     * 
     * @param MediaTypeInterface $media
     * 
     * @return MediaTypeInterface
     * 
     * @throws BadMediaItemException
     */
    protected function uploadFile (MediaTypeInterface $media)
    {
        /* @var AbstractMediaType $media */

        if ($media->getPath() === null) {
            throw new BadMediaItemException("Path not set when uploading media item. Cannot upload.");
        }

        $stream = fopen($media->getPath(), 'rb');
        if (! $stream) {
            throw new BadMediaItemException("Unable to open `{$media->getPath()}` for reading.");
        }

        $request = new Request(
            'POST',
            "http://api.weixin.qq.com/cgi-bin/media/upload?type={$media->getType()}",
            [],
            new MultipartStream ([
                [
                    'name'     => 'media',
                    'contents' => $stream,
                ],
            ])
        );

        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody());
        
        return $media
            ->withID($media->getType() == 'thumb' ? $json->thumb_media_id : $json->media_id)
            ->withUploadDate(DateTime::createFromFormat('U', $json->created_at));
    }

    /**
     * Uploads the given news article items to the WeChat content servers, and populates the media item's ID and created
     * date.
     * 
     * @param ArticleMediaType $media
     * 
     * @return ArticleMediaType
     * 
     * @throws BadMediaResponseFormatException
     */
    protected function uploadArticle (ArticleMediaType $media)
    {
        $body = ['articles' => []];

        foreach ($media->getItems() as $item) {
            $article = [
                'title'          => $item['title'],
                'content'        => $item['content'],
                'thumb_media_id' => $item['thumbnail'],
            ];

            foreach ([
                         'author'    => 'author',
                         'url'       => 'content_source_url',
                         'summary'   => 'digest',
                         'showImage' => 'show_cover_pic',
                     ] as $src => $dest) {
                if (isset($item[$src])) {
                    $article[$dest] = $item[$src];
                }
            }

            $body['articles'][] = $article;
        }

        $request = new Request('POST', "https://api.weixin.qq.com/cgi-bin/media/uploadnews", [], json_encode($body));
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());

        if (isset($json->media_id, $json->created_at)) {
            return $media
                ->withID($json->media_id)
                ->withUploadDate(new DateTime("@{$json->created_at}"));
        } else {
            throw new BadMediaResponseFormatException("expected properties: `media_id`, `created_at`", $response);
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
     * @param MediaTypeInterface $media The media item to download.
     * @param resource|string    $into  Optional file or file resource to download the media item into.
     *
     * @return resource
     * 
     * @throws BadMediaItemException
     * @throws IOException
     */
    public function download (MediaTypeInterface $media, $into = null)
    {
        /* @var AbstractMediaType $media */
        
        // Must have a media id.
        if ($media->getID() === null) {
            throw new BadMediaItemException("ID not set when downloading media item. Cannot download.");
        }

        // Open file for writing.
        if (is_resource($into)) {
            $stream = $into;
        } elseif (is_string($into)) {
            $stream = fopen($into, 'wb');
            if (! $stream) {
                throw new IOException("Can't open file `{$into}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        $request = new Request('GET', "http://api.weixin.qq.com/cgi-bin/media/get?media_id={$media->getID()}");
        $response = $this->client->send($request, [RequestOptions::SINK => $stream]);
        
        return $response->getBody()->detach();
    }
}
