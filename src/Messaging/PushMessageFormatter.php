<?php

namespace Garbetjie\WeChat\Messaging;

use WeChat\Messaging\Type\Audio;
use WeChat\Messaging\Type\Image;
use WeChat\Messaging\Type\Music;
use WeChat\Messaging\Type\RichMedia;
use WeChat\Messaging\Type\Text;
use WeChat\Messaging\Type\TypeInterface;
use WeChat\Messaging\Type\Video;

class PushMessageFormatter
{
    /**
     * Formats the provided message for sending as a push message to the specified recipient.
     *
     * @param TypeInterface $message   The message to send.
     * @param string        $recipient The user id of the message recipient.
     *
     * @return string
     */
    public function format ( TypeInterface $message, $recipient )
    {
        $json = [ ];
        $json[ 'touser' ] = $recipient;
        $json[ 'msgtype' ] = $message->getType();

        $methodName = 'format' . ucfirst( $message->getType() ) . 'Message';
        if ( method_exists( $this, $methodName ) ) {
            $json[ $message->getType() ] = call_user_func( [ $this, $methodName ], $message );
        }

        return json_encode( $json );
    }

    /**
     * @param Image $message
     *
     * @return array
     */
    protected function formatImageMessage ( Image $message )
    {
        return [ 'media_id' => $message->getMediaId() ];
    }

    /**
     * @param Audio $message
     *
     * @return array
     */
    protected function formatVoiceMessage ( Audio $message )
    {
        return [ 'media_id' => $message->getMediaId() ];
    }

    /**
     * @param Text $message
     *
     * @return array
     */
    protected function formatTextMessage ( Text $message )
    {
        return [ 'content' => $message->getContent() ];
    }

    /**
     * @param Music $message
     *
     * @return array
     */
    protected function formatMusicMessage ( Music $message )
    {
        $out = [ ];
        $out[ 'musicurl' ] = $message->getUrl();
        $out[ 'hqmusicurl' ] = $message->getHighQualityUrl();
        $out[ 'thumb_media_id' ] = $message->getThumbnail();

        if ( $message->getTitle() !== null ) {
            $out[ 'title' ] = $message->getTitle();
        }

        if ( $message->getDescription() !== null ) {
            $out[ 'description' ] = $message->getDescription();
        }

        return $out;
    }

    /**
     * @param Video $message
     *
     * @return array
     */
    protected function formatVideoMessage ( Video $message )
    {
        return [
            'media_id'       => $message->getMediaId(),
            'thumb_media_id' => $message->getThumbnailId(),
        ];
    }

    /**
     * @param RichMedia $message
     *
     * @return array
     */
    protected function formatNewsMessage ( RichMedia $message )
    {
        $articles = [ ];

        foreach ( $message->getItems() as $item ) {
            $articles[] = [
                'title'       => $item[ 'title' ],
                'description' => $item[ 'description' ],
                'url'         => $item[ 'url' ],
                'picurl'      => $item[ 'image' ],
            ];
        }

        return [ 'articles' => $articles ];
    }
}
