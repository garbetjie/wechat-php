<?php

namespace WeChat\Media;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use WeChat\Client;
use WeChat\Media\Type\Article;
use WeChat\Media\Type\TypeInterface;

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
    public function __construct ( Client $client )
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
    public function upload ( TypeInterface $media )
    {
        try {

            if ( $media instanceof Article ) {
                $this->uploadArticle( $media );
            } else {
                $this->uploadFile( $media );
            }
        } catch ( GuzzleException $e ) {
            throw new Exception( "Unable to upload media. HTTP error occurred.", null, $e );
        }
    }

    protected function uploadFile ( TypeInterface $media )
    {
        $path = method_exists( $media, 'getPath' ) ? $media->getPath() : null;
        if ( $path === null ) {
            throw new Exception( "Path not set." );
        }

        $stream = fopen( $path, 'rb' );
        if ( ! $stream ) {
            throw new Exception( "Unable to open '{$path}' for reading." );
        }

        $request = new Request(
            'POST',
            "http://file.api.wechat.com/cgi-bin/media/upload?type={$media->getType()}",
            [ ],
            new MultipartStream ( [
                [
                    'name'     => 'media',
                    'contents' => $stream,
                ],
            ] )
        );

        $response = $this->client->send( $request );
        $json = json_decode( (string) $response->getBody(), true );

        $media->setId( $json[ 'media_id' ] );
        $media->setCreated( DateTime::createFromFormat( 'U', $json[ 'created_at' ] ) );
    }

    protected function uploadArticle ( Article $media )
    {
        $body = [ 'articles' => [ ] ];

        foreach ( $media->getItems() as $item ) {
            $article = [
                'title'          => $item[ 'title' ],
                'content'        => $item[ 'content' ],
                'thumb_media_id' => $item[ 'thumbnail' ],
            ];

            foreach ( [ 'author' => 'author', 'url' => 'content_source_url', 'summary' => 'digest', 'image' => 'show_cover_pic' ] as $src => $dest ) {
                if ( isset( $item[ $src ] ) ) {
                    $article[ $dest ] = $item[ $src ];
                }
            }

            $body[ 'articles' ][] = $article;
        }

        $request = new Request( 'POST', "https://api.weixin.qq.com/cgi-bin/media/uploadnews", [ ], json_encode( $body ) );
        $response = $this->client->send( $request );
        $json = json_decode( $response->getBody(), true );

        $media->setId( $json[ 'media_id' ] );
        $media->setCreated( DateTime::createFromFormat( 'U', $json[ 'created_at' ] ) );
    }

    /**
     * Downloads the given media item from the WeChat API.
     *
     * The media item must have been uploaded previously, and so must have its ID set.
     *
     * If no file is specified using the `$into` parameter, then a temporary file resource is created using the
     * `tmpfile()` function.
     *
     * @param TypeInterface $media The media item to download.
     * @param string        $into  Optional file to download the media item into.
     *
     * @return resource
     */
    public function download ( TypeInterface $media, $into = null )
    {
        // Must have a media id.
        if ( ! $media->getId() ) {
            throw new Exception( "No media id found for downloading." );
        }

        // Open file for writing.
        if ( is_string( $into ) ) {
            $stream = fopen( $into, 'wb' );

            if ( ! $stream ) {
                throw new Exception( "Unable to open file '{$into}' for writing." );
            }
        } else {
            $stream = tmpfile();
        }

        try {
            $request = new Request( 'GET', "http://file.api.wechat.com/cgi-bin/media/get?media_id={$media->getId()}" );
            $response = $this->client->send( $request, [ RequestOptions::SINK => $stream ] );
            $stream = $response->getBody()->detach();

            return $stream;
        } catch ( GuzzleException $e ) {
            throw new Exception( "Unable to download media. HTTP error occurred.", null, $e );
        }
    }
}
