<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Messaging\Type\AbstractMediaType;
use Garbetjie\WeChatClient\Messaging\Type\Music;
use Garbetjie\WeChatClient\Messaging\Type\Text;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;

class BroadcastMessageFormatter
{
    /**
     * @param TypeInterface $type
     *
     * @return array
     */
    public function format (TypeInterface $type)
    {
        $json = ['msgtype' => $type->type()];

        $method = 'format' . ucfirst($type->type());
        if (method_exists($this, $method)) {
            $json[$type->type()] = $this->$method($type);
        } elseif ($type instanceof AbstractMediaType) {
            $json[$type->type()] = ['media_id' => $type->id];
        }

        return $json;
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
}
