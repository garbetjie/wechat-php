<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMediaType;
use Garbetjie\WeChatClient\Messaging\Type\Music;
use Garbetjie\WeChatClient\Messaging\Type\RichMedia;
use Garbetjie\WeChatClient\Messaging\Type\Text;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Messaging\Type\Video;

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
    public function format (TypeInterface $message, $recipient)
    {
        
        $json = [];
        $json['touser'] = $recipient;
        $json['msgtype'] = $message->type();

        $methodName = 'format' . ucfirst($message->type());
        if (method_exists($this, $methodName)) {
            $json[$message->type()] = $this->$methodName($message);
        } elseif ($message instanceof AbstractMediaType) {
            $json[$message->type()] = ['media_id' => $message->id];
        }

        return json_encode($json);
    }

    /**
     * @param Text $message
     *
     * @return array
     */
    private function formatText (Text $message)
    {
        return ['content' => $message->content];
    }

    /**
     * @param Music $message
     *
     * @return array
     */
    private function formatMusic (Music $message)
    {
        $out = [];
        $out['musicurl'] = $message->url;
        $out['hqmusicurl'] = $message->highQualityUrl;
        $out['thumb_media_id'] = $message->thumbnailID;

        if ($message->title !== null) {
            $out['title'] = $message->title;
        }

        if ($message->description !== null) {
            $out['description'] = $message->description;
        }

        return $out;
    }

    /**
     * @param Video $message
     *
     * @return array
     */
    private function formatVideo (Video $message)
    {
        return [
            'media_id'       => $message->id,
            'thumb_media_id' => $message->thumbnailID,
        ];
    }

    /**
     * @param RichMedia $message
     *
     * @return array
     */
    private function formatNews (RichMedia $message)
    {
        $articles = [];

        foreach ($message->items() as $item) {
            $articles[] = [
                'title'       => $item['title'],
                'description' => $item['description'],
                'url'         => $item['url'],
                'picurl'      => $item['image'],
            ];
        }

        return ['articles' => $articles];
    }
}
