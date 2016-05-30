<?php

namespace Garbetjie\WeChatClient\Responder;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use Garbetjie\WeChatClient\Service\Messaging\Type\AudioMessageMessageType;
use Garbetjie\WeChatClient\Messaging\Type\AudioMessageType;
use Garbetjie\WeChatClient\Messaging\Type\ImageMessageType;
use Garbetjie\WeChatClient\Messaging\Type\MusicMessageType;
use Garbetjie\WeChatClient\Messaging\Type\RichMediaMessageType;
use Garbetjie\WeChatClient\Messaging\Type\TextMessageType;
use Garbetjie\WeChatClient\Messaging\Type\MessageTypeInterface;
use Garbetjie\WeChatClient\Messaging\Type\VideoMessageType;
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
            'FromUserName' => $input->getRecipient(),
            'ToUserName'   => $input->getSender(),
            'CreateTime'   => time(),
            'MsgType'      => $message->getType(),
        ]);

        $method = 'fill' . ucfirst($message->getType()) . 'Message';
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
        $this->fill($root, ['Content' => $message->getContent()]);
    }

    /**
     * Fill an image message.
     *
     * @param DOMElement              $root
     * @param ImageMessageType $message
     */
    protected function fillImageMessage (DOMElement $root, ImageMessageType $message)
    {
        $this->fill(
            $root,
            [
                'ImageMediaType' => [
                    'MediaId' => $message->getID(),
                ],
            ]
        );
    }

    /**
     * Fill an audio message.
     *
     * @param DOMElement              $root
     * @param AudioMessageType $message
     */
    protected function fillVoiceMessage (DOMElement $root, AudioMessageType $message)
    {
        $this->fill($root,
            [
                'Voice' => [
                    'MediaId' => $message->getID(),
                ],
            ]
        );
    }

    /**
     * Fill a video message.
     *
     * @param DOMElement              $root
     * @param VideoMessageType $message
     */
    protected function fillVideoMessage (DOMElement $root, VideoMessageType $message)
    {
        $this->fill($root,
            [
                'VideoMediaType' => [
                    'MediaId'      => $message->getID(),
                    'ThumbMediaId' => $message->getThumbnailID(),
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
        $append['MusicUrl'] = $message->getUrl();
        $append['HQMusicUrl'] = $message->getHighQualityUrl();
        $append['ThumbMediaId'] = $message->getThumbnailID();

        if ($message->getTitle()) {
            $append['Title'] = $message->getTitle();
        }

        if ($message->getDescription()) {
            $append['Description'] = $message->getDescription();
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

        foreach ($message->getItems() as $item) {
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
