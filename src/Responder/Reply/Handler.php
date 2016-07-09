<?php

namespace Garbetjie\WeChatClient\Responder\Reply;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use Garbetjie\WeChatClient\Messaging\Type;
use Garbetjie\WeChatClient\Responder\Exception;

abstract class Handler
{
    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $recipient;
    
    /**
     * Indicates whether the message has been sent or not.
     *
     * @var bool
     */
    private $sent = false;

    /**
     * @param $sender
     *
     * @return static
     */
    public final function withSender ($sender)
    {
        $new = clone $this;
        $new->sender = $sender;
        
        return $new;
    }

    /**
     * @param $recipient
     *
     * @return static
     */
    public final function withRecipient ($recipient)
    {
        $new = clone $this;
        $new->recipient = $recipient;
        
        return $new;
    }

    /**
     * Prints the given reply and headers.
     * 
     * @param string $reply - The reply to print.
     * @param array  $headers - Any additional headers to print out.
     *
     * @return void
     */
    abstract protected function printReply ($reply, array $headers = []);

    /**
     * Receives an array representation of the message, and converts it into its XML representation.
     * 
     * @param array $message
     *
     * @return string
     */
    protected function buildMessage (array $message)
    {
        $doc = new DOMDocument();
        $root = $doc->createElement('xml');
        $doc->appendChild($root);
        
        $message = array_merge(
            $message,
            ['FromUserName' => $this->sender, 'ToUserName' => $this->recipient, 'CreateTime' => time()]
        );
        
        $this->fillElement($doc, $root, $message);
        
        return $doc->saveXML($doc->documentElement);
    }

    /**
     * Recursively fills the given DOMElement parent with the given values in $filler. Used to populate the XML DOM
     * that will be saved to XML.
     * 
     * @param DOMDocument $doc
     * @param DOMElement  $parent
     * @param array       $filler
     */
    private function fillElement (DOMDocument $doc, DOMElement $parent, array $filler)
    {
        foreach ($filler as $name => $value) {
            if ($value instanceof DOMElement) {
                $element = $value;
            } else {
                $element = $doc->createElement($name);
                
                if (is_array($value)) {
                    $this->fillElement($doc, $element, $value);
                } else {
                    $element->appendChild(new DOMCdataSection($value));
                }
            }
            
            $parent->appendChild($element);
        }
    }

    /**
     * Handles the sending of the reply. Throws an exception if a reply has previously been sent.
     * 
     * @param string $reply
     * @param array  $headers
     *
     * @throws Exception\AlreadySentException
     */
    private function sendReply ($reply, array $headers = [])
    {
        if ($this->sent) {
            throw new Exception\AlreadySentException();
        }

        $headers = array_merge(
            [
                'Connection: close',
                'Content-Length: ' . strlen($reply),
            ],
            $headers
        );

        $hasContentType = false;
        foreach ($headers as $headerLine) {
            if (stripos($headerLine, 'Content-Type') !== false) {
                $hasContentType = true;
                break;
            }
        }

        if (!$hasContentType) {
            $headers[] = 'Content-Type: application/xml';
        }
        
        $this->sent = true;
        $this->printReply($reply, $headers);
    }

    /**
     * Sends the debug echo string.
     * 
     * @param string $echoString
     *
     * @throws Exception\AlreadySentException
     */
    public function sendEchoString ($echoString)
    {
        $this->sendReply($echoString, ['Content-Type: text/plain']);
    }

    /**
     * Sends a text reply.
     * 
     * @param Type\Text $message
     *
     * @throws Exception\AlreadySentException
     */
    public function sendText (Type\Text $message)
    {
        $this->sendReply(
            $this->buildMessage(
                [
                    'MsgType' => $message->getType(),
                    'Content' => $message->getContent(),
                ]
            )
        );
    }

    /**
     * Sends an image reply.
     * 
     * @param Type\Image $message
     *
     * @throws Exception\AlreadySentException
     */
    public function sendImage (Type\Image $message)
    {
        $this->sendReply(
            $this->buildMessage(
                [
                    'MsgType' => $message->getType(),
                    ucfirst($message->getType()) => [
                        'MediaId' => $message->getMediaID(),
                    ],
                ]
            )
        );
    }

    /**
     * Sends an audio reply.
     * 
     * @param Type\Audio $audioMessage
     *
     * @throws Exception\AlreadySentException
     */
    public function sendAudio (Type\Audio $audioMessage)
    {
        $this->sendReply(
            $this->buildMessage(
                [
                    'MsgType' => $audioMessage->getType(),
                    ucfirst($audioMessage->getType()) => [
                        'MediaId' => $audioMessage->getMediaID(),
                    ],
                ]
            )
        );
    }

    /**
     * Sends a video reply.
     * 
     * @param Type\Video $videoMessage
     *
     * @throws Exception\AlreadySentException
     */
    public function sendVideo (Type\Video $videoMessage)
    {
        $this->sendReply(
            $this->buildMessage(
                [
                    'MsgType' => $videoMessage->getType(),
                    ucfirst($videoMessage->getType()) => [
                        'MediaId' => $videoMessage->getMediaID(),
                        'ThumbMediaId' => $videoMessage->getThumbnailID(),
                    ],
                ]
            )
        );
    }

    /**
     * Sends a music reply.
     * 
     * @param Type\Music $musicMessage
     *
     * @throws Exception\AlreadySentException
     */
    public function sendMusic (Type\Music $musicMessage)
    {
        $this->sendReply(
            $this->buildMessage(
                [
                    'MsgType' => $musicMessage->getType(),
                    ucfirst($musicMessage->getType()) => [
                        'Title' => $musicMessage->getTitle(),
                        'Description' => $musicMessage->getDescription(),
                        'MusicUrl' => $musicMessage->getSourceURL(),
                        'HQMusicUrl' => $musicMessage->getHighQualitySourceURL(),
                        'ThumbMediaId' => $musicMessage->getThumbnailID(),
                    ]
                ]
            )
        );
    }

    /**
     * Sends a new message reply.
     * 
     * @param Type\News $newsMessage
     *
     * @throws Exception\AlreadySentException
     */
    public function sendNews (Type\News $newsMessage)
    {
        // Build up items.
        $items = [];
        foreach ($newsMessage->getItems() as $newsItem) {
            $items[] = [
                'Title' => $newsItem->getTitle(),
                'Description' => $newsItem->getDescription(),
                'PicUrl' => $newsItem->getImageURL(),
                'Url' => $newsItem->getURL(),
            ];
        }

        $this->sendReply(
            $this->buildMessage(
                [
                    'MsgType' => $newsMessage->getType(),
                    'ArticleCount' => count($items),
                    'Articles' => [
                        'item' => $items,
                    ],
                ]
            )
        );
    }
}
