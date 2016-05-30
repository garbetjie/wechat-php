<?php

namespace Garbetjie\WeChatClient\Service\Messaging;

use Garbetjie\WeChatClient\Service\Messaging\Type\AbstractMediaMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\MusicMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\RichMediaMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\TextMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\MessageTypeInterface;
use Garbetjie\WeChatClient\Service\Messaging\Type\VideoMessageMessageType;

class PushMessageFormatter
{
    /**
     * Formats the provided message for sending as a push message to the specified recipient.
     *
     * @param MessageTypeInterface $message   The message to send.
     * @param string               $recipient The user id of the message recipient.
     *
     * @return string
     */
    public function format (MessageTypeInterface $message, $recipient)
    {
        
        $json = [];
        $json['touser'] = $recipient;
        $json['msgtype'] = $message->getType();

        $methodName = 'format' . ucfirst($message->getType());
        if (method_exists($this, $methodName)) {
            $json[$message->getType()] = $this->$methodName($message);
        } elseif ($message instanceof AbstractMediaMessageType) {
            $json[$message->type()] = ['media_id' => $message->id];
        }

        return json_encode($json);
    }

    /**
     * @param TextMessageType $message
     *
     * @return array
     */
    private function formatText (TextMessageType $message)
    {
        return ['content' => $message->content];
    }

    /**
     * @param MusicMessageType $message
     *
     * @return array
     */
    private function formatMusic (MusicMessageType $message)
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
     * @param VideoMessageMessageType $message
     *
     * @return array
     */
    private function formatVideo (VideoMessageMessageType $message)
    {
        return [
            'media_id'       => $message->id,
            'thumb_media_id' => $message->thumbnailID,
        ];
    }

    /**
     * @param RichMediaMessageType $message
     *
     * @return array
     */
    private function formatNews (RichMediaMessageType $message)
    {
        $articles = [];

        foreach ($message->getItems() as $item) {
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
