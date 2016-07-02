<?php

namespace Garbetjie\WeChatClient\Responder\Reply;

use Garbetjie\WeChatClient\Messaging\Type;

class DirectHandler extends Handler
{
    /**
     * @inheritDoc
     */
    protected function sendMessage (array $message)
    {
        $this->sendToOutput($this->buildMessage($message), ['Content-Type: text/plain']);
    }
    
    private function sendToOutput ($contents, $headers = [])
    {
        $headers = array_merge(
            [
                'Connection: close',
                'Content-Length: ' . strlen($contents),
            ],
            $headers
        );
        
        foreach ($headers as $headerLine) {
            header($headerLine);
        }

        // Disable output compression, so that the response can be sent immediately.
        ini_set('zlib.output_compression', false);
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', 1);
        }

        echo $contents;
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }

    public function sendEchoString ($echoString)
    {
        $this->sendToOutput($echoString, ['Content-Type: text/plain']);
    }

    public function sendText (Type\Text $message)
    {
        $this->sendMessage(
            [
                'MsgType' => $message->getType(),
                'Content' => $message->getContent(),
            ]
        );
    }
    
    public function sendImage (Type\Image $imageMessage)
    {
        $this->sendMessage(
            [
                'MsgType' => $imageMessage->getType(),
                ucfirst($imageMessage->getType()) => [
                    'MediaId' => $imageMessage->getMediaID(),
                ],
            ]
        );
    }

    public function sendAudio (Type\Audio $audioMessage)
    {
        $this->sendMessage(
            [
                'MsgType' => $audioMessage->getType(),
                ucfirst($audioMessage->getType()) => [
                    'MediaId' => $audioMessage->getMediaID(),
                ],
            ]
        );
    }

    public function sendVideo (Type\Video $videoMessage)
    {
        $this->sendMessage(
            [
                'MsgType' => $videoMessage->getType(),
                ucfirst($videoMessage->getType()) => [
                    'MediaId' => $videoMessage->getMediaID(),
                    'ThumbMediaId' => $videoMessage->getThumbnailID(),
                ],
            ]
        );
    }

    public function sendMusic (Type\Music $musicMessage)
    {
        $this->sendMessage(
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
        );
    }

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
        
        $this->sendMessage(
            [
                'MsgType' => $newsMessage->getType(),
                'ArticleCount' => count($items),
                'Articles' => [
                    'item' => $items,
                ],
            ]
        );
    }


}
