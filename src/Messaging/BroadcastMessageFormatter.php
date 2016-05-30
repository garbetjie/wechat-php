<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMediaMessageType;
use Garbetjie\WeChatClient\Messaging\Type\MusicMessageType;
use Garbetjie\WeChatClient\Messaging\Type\TextMessageType;
use Garbetjie\WeChatClient\Messaging\Type\MessageTypeInterface;

class BroadcastMessageFormatter
{
    /**
     * @param MessageTypeInterface $type
     *
     * @return array
     */
    public function format (MessageTypeInterface $type)
    {
        $json = ['msgtype' => $type->getType()];

        $method = 'format' . ucfirst($type->getType());
        if (method_exists($this, $method)) {
            $json[$type->getType()] = $this->$method($type);
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
