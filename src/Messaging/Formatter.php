<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Messaging\Type\Uploaded;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Messaging\Type\Music;
use Garbetjie\WeChatClient\Messaging\Type\News;
use Garbetjie\WeChatClient\Messaging\Type\Text;
use Garbetjie\WeChatClient\Messaging\Type\Video;

class Formatter
{

    /**
     * @param TypeInterface $type
     *
     * @return array
     */
    public function formatMessageForBroadcast (TypeInterface $type)
    {
        $json = ['msgtype' => $type->getType()];

        $method = 'format' . ucfirst($type->getType()) . 'ForBroadcast';
        if (method_exists($this, $method)) {
            $json[$type->getType()] = $this->$method($type);
        } elseif ($type instanceof Uploaded) {
            $json[$type->getType()] = ['media_id' => $type->getMediaID()];
        }

        return $json;
    }

    /**
     * @param Text $message
     *
     * @return array
     */
    private function formatTextForBroadcast (Text $message)
    {
        return ['content' => $message->getContent()];
    }

    /**
     * @param Music $message
     *
     * @return array
     */
    private function formatMusicForBroadcast (Music $message)
    {
        $out = [];
        $out['musicurl'] = $message->getSourceURL();
        $out['hqmusicurl'] = $message->getHighQualitySourceURL();
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
     * Formats the provided message for sending as a push message to the specified recipient.
     *
     * @param TypeInterface $message The message to send.
     * @param string        $openID  The user id of the message recipient.
     *
     * @return array
     */
    public function formatMessageForPush (TypeInterface $message, $openID)
    {

        $json = [];
        $json['touser'] = $openID;
        $json['msgtype'] = $message->getType();

        $methodName = 'format' . ucfirst($message->getType()) . 'ForPush';
        if (method_exists($this, $methodName)) {
            $json[$message->getType()] = $this->$methodName($message);
        } elseif ($message instanceof Uploaded) {   
            $json[$message->getType()] = ['media_id' => $message->getMediaID()];
        }

        return $json;
    }

    /**
     * @param Text $message
     *
     * @return array
     */
    private function formatTextForPush (Text $message)
    {
        return ['content' => $message->getContent()];
    }

    /**
     * @param Music $message
     *
     * @return array
     */
    private function formatMusicForPush (Music $message)
    {
        $out = [];
        $out['musicurl'] = $message->getSourceURL();
        $out['hqmusicurl'] = $message->getHighQualitySourceURL();
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
     * @param Video $message
     *
     * @return array
     */
    private function formatVideoForPush (Video $message)
    {
        return [
            'media_id'       => $message->getMediaID(),
            'thumb_media_id' => $message->getThumbnailID(),
        ];
    }

    /**
     * @param News $message
     *
     * @return array
     */
    private function formatNewsForPush (News $message)
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



    /**
     * Formats the given data to be used in a template message. Returns an array that can be sent to the API.
     *
     * @param string $templateID
     * @param string $openID
     * @param string $url
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    public function formatTemplateMessage ($templateID, $openID, $url, array $data, array $options = [])
    {
        $json = [
            'touser'      => (string)$openID,
            'template_id' => (string)$templateID,
            'url'         => (string)$url,
            'data'        => [],
        ];

        if (isset($options['color'])) {
            $json['topcolor'] = '#' . strtoupper(ltrim($options['color'], '#'));
        }

        foreach ($data as $fieldName => $fieldValue) {
            if (! is_array($fieldValue)) {
                $json['data'][$fieldName] = ['value' => $fieldValue];
            } else {
                $json['data'][$fieldName] = [
                    'value' => $fieldValue['value'],
                ];

                if (isset($fieldValue['color'])) {
                    $json['data'][$fieldName]['color'] = '#' . strtoupper(ltrim($fieldValue['color'], '#'));
                }
            }
        }

        return $json;
    }
}
