<?php

namespace Garbetjie\WeChatClient\Service\Messaging;

use Garbetjie\WeChatClient\Service\Messaging\Type\AbstractMediaMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\MusicMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\TextMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\MessageTypeInterface;

class BroadcastMessageFormatter
{
    /**
     * @param MessageTypeInterface $type
     *
     * @return array
     */
    public function format (MessageTypeInterface $type)
    {
        $json = ['msgtype' => $type->type()];

        $method = 'format' . ucfirst($type->type());
        if (method_exists($this, $method)) {
            $json[$type->type()] = $this->$method($type);
        } elseif ($type instanceof AbstractMediaMessageType) {
            $json[$type->type()] = ['media_id' => $type->id];
        }

        return $json;
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
}
