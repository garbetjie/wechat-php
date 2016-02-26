<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Groups\Group;
use Garbetjie\WeChatClient\Messaging\Type\Article;
use Garbetjie\WeChatClient\Messaging\Type\Audio;
use Garbetjie\WeChatClient\Messaging\Type\Image;
use Garbetjie\WeChatClient\Messaging\Type\Music;
use Garbetjie\WeChatClient\Messaging\Type\RichMedia;
use Garbetjie\WeChatClient\Messaging\Type\Text;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Messaging\Type\Video;

class BroadcastMessageFormatter
{
    /**
     * @param TypeInterface      $type
     * @param Group|array|string $recipient
     *
     * @return array
     */
    public function format ( TypeInterface $type )
    {
        $json = [ 'msgtype' => $type->getType() ];

        $method = 'format' . ucfirst( $type->getType() ) . 'Message';
        if ( method_exists( $this, $method ) ) {
            $json[ $type->getType() ] = $this->$method( $type );
        }

        return $json;
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
        return [ 'media_id' => $message->getMediaId() ];
    }

    /**
     * @param RichMedia $message
     *
     * @return array
     */
    protected function formatMpnewsMessage ( Article $message )
    {
        return [ 'media_id' => $message->getMediaId() ];
    }
}
