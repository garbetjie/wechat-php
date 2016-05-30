<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMediaMessageType;
use Garbetjie\WeChatClient\Messaging\Type\MusicMessageType;
use Garbetjie\WeChatClient\Messaging\Type\RichMediaMessageType;
use Garbetjie\WeChatClient\Messaging\Type\TextMessageType;
use Garbetjie\WeChatClient\Messaging\Type\MessageTypeInterface;
use Garbetjie\WeChatClient\Messaging\Type\VideoMessageType;

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
            $json[$message->getType()] = ['media_id' => $message->getID()];
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
        return ['content' => $message->getContent()];
    }

    /**
     * @param MusicMessageType $message
     *
     * @return array
     */
    private function formatMusic (MusicMessageType $message)
    {
        $out = [];
        $out['musicurl'] = $message->getUrl();
        $out['hqmusicurl'] = $message->getHighQualityUrl();
        $out['thumb_media_id'] = $message->getThumbnailID();

        if ($message->getTitle() !== null) {
            $out['title'] = $message->getTitle();
        }

        if ($message->getDescription() !== null) {
            $out['description'] = $message->getDescription();
        }

        return $out;
    }

    /**
     * @param VideoMessageType $message
     *
     * @return array
     */
    private function formatVideo (VideoMessageType $message)
    {
        return [
            'media_id'       => $message->getID(),
            'thumb_media_id' => $message->getThumbnailID(),
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
