<?php

namespace Garbetjie\WeChatClient\Media;

use DateTime;
use Garbetjie\WeChatClient\Exception\ApiErrorException;
use Garbetjie\WeChatClient\Media\Type\AbstractType;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Media\Type\Article;
use Garbetjie\WeChatClient\Media\Type\TypeInterface;

class Service
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Service constructor.
     *
     * @param Client $client
     */
    public function __construct (Client $client)
    {
        $this->client = $client;
    }

    /**
     * Uploads the supplied media item to the WeChat API.
     *
     * This method assumes that the item has not been uploaded previously, and so will ignore any previously created
     * date and media id that has been set. The supplied media item will be modified.
     *
     * @param TypeInterface $media
     *
     * @throws Exception
     */
    public function upload (TypeInterface $media)
    {
        if ($media->type() === 'news') {
            $this->uploadArticle($media);
        } else {
            $this->uploadFile($media);
        }
    }

    /**
     * Uploads the given media file to the WeChat content servers, and populates the item's ID and created date.
     * 
     * @param TypeInterface $media
     * 
     * @throws GuzzleException
     * @throws ApiErrorException
     * @throws Exception
     */
    protected function uploadFile (TypeInterface $media)
    {
        /* @var AbstractType $media */

        if (! property_exists($media, 'path')) {
            throw new Exception("Property `path` not found on media item. Cannot upload.");
        }

        $stream = fopen($media->path, 'rb');
        if (! $stream) {
            throw new Exception("Unable to open `{$media->path}` for reading.");
        }

        $request = new Request(
            'POST',
            "http://api.weixin.qq.com/cgi-bin/media/upload?type={$media->type()}",
            [],
            new MultipartStream ([
                [
                    'name'     => 'media',
                    'contents' => $stream,
                ],
            ])
        );

        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody(), true);

        $media->id = $json[$media->type() === 'thumb' ? 'thumb_media_id' : 'media_id']; 
        $media->created = DateTime::createFromFormat('U', $json['created_at']);
    }

    /**
     * Uploads the given news article items to the WeChat content servers, and populates the media item's ID and created
     * date.
     * 
     * @param Article $media
     * 
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    protected function uploadArticle (Article $media)
    {
        $body = ['articles' => []];

        foreach ($media->items() as $item) {
            $article = [
                'title'          => $item['title'],
                'content'        => $item['content'],
                'thumb_media_id' => $item['thumbnail'],
            ];

            foreach ([
                         'author'  => 'author',
                         'url'     => 'content_source_url',
                         'summary' => 'digest',
                         'image'   => 'show_cover_pic',
                     ] as $src => $dest) {
                if (isset($item[$src])) {
                    $article[$dest] = $item[$src];
                }
            }

            $body['articles'][] = $article;
        }

        $request = new Request('POST', "https://api.weixin.qq.com/cgi-bin/media/uploadnews", [], json_encode($body));
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        $media->id = $json['media_id'];
        $media->created = DateTime::createFromFormat('U', $json['created_at']);
    }

    /**
     * Downloads the given media item from the WeChat API.
     *
     * The media item must have been uploaded previously, and so must have its ID set.
     *
     * If no file is specified using the `$into` parameter, then a temporary file resource is created using the
     * `tmpfile()` function.
     *
     * @param TypeInterface   $media The media item to download.
     * @param resource|string $into  Optional file or file resource to download the media item into.
     *
     * @return resource
     * 
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function download (TypeInterface $media, $into = null)
    {
        /* @var AbstractType $media */
        
        // Must have a media id.
        if (!property_exists($media, 'id') || ! $media->id) {
            throw new Exception("No media id found for downloading.");
        }

        // Open file for writing.
        if (is_resource($into)) {
            $stream = $into;
        } elseif (is_string($into)) {
            $stream = fopen($into, 'wb');
            if (! $stream) {
                throw new Exception("Can't open file `{$into}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        $request = new Request('GET', "http://api.weixin.qq.com/cgi-bin/media/get?media_id={$media->id}");
        $response = $this->client->send($request, [RequestOptions::SINK => $stream]);
        $stream = $response->getBody()->detach();

        return $stream;
    }
}
