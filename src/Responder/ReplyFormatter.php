<?php

namespace Garbetjie\WeChatClient\Responder;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use Garbetjie\WeChatClient\Messaging\Type\Audio;
use Garbetjie\WeChatClient\Messaging\Type\Image;
use Garbetjie\WeChatClient\Messaging\Type\Music;
use Garbetjie\WeChatClient\Messaging\Type\RichMedia;
use Garbetjie\WeChatClient\Messaging\Type\Text;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Messaging\Type\Video;
use Garbetjie\WeChatClient\Responder\Input\InputInterface;

class ReplyFormatter
{
    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @param InputInterface $input
     * @param TypeInterface  $message
     *
     * @return string
     */
    public function format (InputInterface $input, TypeInterface $message)
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
     * @param DOMElement $root
     * @param Text       $message
     */
    protected function fillTextMessage (DOMElement $root, Text $message)
    {
        $this->fill($root, ['Content' => $message->content]);
    }

    /**
     * Fill an image message.
     *
     * @param DOMElement $root
     * @param Image      $message
     */
    protected function fillImageMessage (DOMElement $root, Image $message)
    {
        $this->fill(
            $root,
            [
                'Image' => [
                    'MediaId' => $message->id,
                ],
            ]
        );
    }

    /**
     * Fill an audio message.
     *
     * @param DOMElement $root
     * @param Audio      $message
     */
    protected function fillVoiceMessage (DOMElement $root, Audio $message)
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
     * @param DOMElement $root
     * @param Video      $message
     */
    protected function fillVideoMessage (DOMElement $root, Video $message)
    {
        $this->fill($root,
            [
                'Video' => [
                    'MediaId'      => $message->id,
                    'ThumbMediaId' => $message->thumbnailID,
                ],
            ]
        );
    }

    /**
     * Fill a music message.
     *
     * @param DOMElement $root
     * @param Music      $message
     */
    protected function fillMusicMessage (DOMElement $root, Music $message)
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

        $this->fill($root, ['Music' => $append]);
    }

    /**
     * Fill a rich media message.
     *
     * @param DOMElement $root
     * @param RichMedia  $message
     */
    protected function fillNewsMessage (DOMElement $root, RichMedia $message)
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
