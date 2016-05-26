<?php

namespace Garbetjie\WeChatClient\Responder;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use Garbetjie\WeChatClient\Service\Messaging\Type\AudioMessageMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\ImageMessageMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\MusicMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\RichMediaMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\TextMessageType;
use Garbetjie\WeChatClient\Service\Messaging\Type\MessageTypeInterface;
use Garbetjie\WeChatClient\Service\Messaging\Type\VideoMessageMessageType;
use Garbetjie\WeChatClient\Responder\Input\InputInterface;

class ReplyFormatter
{
    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @param InputInterface       $input
     * @param MessageTypeInterface $message
     *
     * @return string
     */
    public function format (InputInterface $input, MessageTypeInterface $message)
    {
        $this->doc = new DOMDocument();
        $root = $this->doc->createElement('xml');
        $this->doc->appendChild($root);

        $this->fill($root, [
            'FromUserName' => $input->recipient(),
            'ToUserName'   => $input->sender(),
            'CreateTime'   => time(),
            'MsgType'      => $message->type(),
        ]);

        $method = 'fill' . ucfirst($message->type()) . 'Message';
        if (method_exists($this, $method) && is_callable([$this, $method])) {
            call_user_func([$this, $method], $root, $message);
        }

        return $this->doc->saveXML($this->doc->documentElement);
    }

    /**
     * Recursively fills the given DOM element with the supplied values.
     *
     * @param DOMElement $parent
     * @param array      $params
     */
    protected function fill (DOMElement $parent, array $params)
    {
        foreach ($params as $name => $value) {
            if ($value instanceof DOMElement) {
                $element = $value;
            } else {
                if (is_array($value)) {
                    $element = $this->doc->createElement($name);
                    $this->fill($element, $value);
                } else {
                    $element = $this->doc->createElement($name);
                    $element->appendChild(new DOMCdataSection($value));
                }
            }

            $parent->appendChild($element);
        }
    }

    /**
     * Fill a text message.
     *
     * @param DOMElement      $root
     * @param TextMessageType $message
     */
    protected function fillTextMessage (DOMElement $root, TextMessageType $message)
    {
        $this->fill($root, ['Content' => $message->content]);
    }

    /**
     * Fill an image message.
     *
     * @param DOMElement              $root
     * @param ImageMessageMessageType $message
     */
    protected function fillImageMessage (DOMElement $root, ImageMessageMessageType $message)
    {
        $this->fill(
            $root,
            [
                'ImageMediaType' => [
                    'MediaId' => $message->id,
                ],
            ]
        );
    }

    /**
     * Fill an audio message.
     *
     * @param DOMElement              $root
     * @param AudioMessageMessageType $message
     */
    protected function fillVoiceMessage (DOMElement $root, AudioMessageMessageType $message)
    {
        $this->fill($root,
            [
                'Voice' => [
                    'MediaId' => $message->id,
                ],
            ]
        );
    }

    /**
     * Fill a video message.
     *
     * @param DOMElement              $root
     * @param VideoMessageMessageType $message
     */
    protected function fillVideoMessage (DOMElement $root, VideoMessageMessageType $message)
    {
        $this->fill($root,
            [
                'VideoMediaType' => [
                    'MediaId'      => $message->id,
                    'ThumbMediaId' => $message->thumbnailID,
                ],
            ]
        );
    }

    /**
     * Fill a music message.
     *
     * @param DOMElement       $root
     * @param MusicMessageType $message
     */
    protected function fillMusicMessage (DOMElement $root, MusicMessageType $message)
    {
        $append = [];
        $append['MusicUrl'] = $message->url;
        $append['HQMusicUrl'] = $message->highQualityUrl;
        $append['ThumbMediaId'] = $message->thumbnailID;

        if ($message->title) {
            $append['Title'] = $message->title;
        }

        if ($message->description) {
            $append['Description'] = $message->description;
        }

        $this->fill($root, ['MusicMessageType' => $append]);
    }

    /**
     * Fill a rich media message.
     *
     * @param DOMElement           $root
     * @param RichMediaMessageType $message
     */
    protected function fillNewsMessage (DOMElement $root, RichMediaMessageType $message)
    {
        $articleElement = $this->doc->createElement('Articles');

        foreach ($message->items() as $item) {
            $itemElement = $this->doc->createElement('item');

            $this->fill($itemElement, [
                'Title'       => $item['title'],
                'Description' => $item['description'],
                'Url'         => $item['url'],
                'PicUrl'      => $item['image'],
            ]);

            $articleElement->appendChild($itemElement);
        }

        $root->appendChild($articleElement);
        $this->fill($root, ['ArticleCount' => $articleElement->childNodes->length]);
    }
}
