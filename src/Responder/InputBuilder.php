<?php

namespace Garbetjie\WeChatClient\Responder;

use Garbetjie\WeChatClient\Responder\Exception\BadInputTypeException;
use ReflectionClass;
use SimpleXMLElement;

class InputBuilder
{

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Input
     * @throws Exception
     */
    public function build (SimpleXMLElement $xml)
    {
        // Start parsing the input.
        $function = preg_replace_callback('/(_)([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, strtolower($xml->MsgType));
        $callable = [$this, "parse{$function}Input"];

        if (is_callable($callable)) {
            $input = call_user_func($callable, $xml);
        } else {
            throw new Exception("unexpected input type `{$xml->MsgType}`");
        }
        
        return $input;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Text
     */
    private function parseTextInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Text::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [$xml->Content]
            )    
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    private function getCommonMessageProperties (SimpleXMLElement $xml)
    {
        return [
            (string)$xml->FromUserName,
            (string)$xml->ToUserName,
            (string)$xml->MsgId,
            (int)$xml->CreateTime
        ];
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Image
     */
    private function parseImageInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Image::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [
                    (string)$xml->MediaId,
                    (string)$xml->PicUrl,
                ]
            )
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Audio
     */
    private function parseVoiceInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Audio::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [
                    (string)$xml->MediaId,
                    (string)$xml->Format,
                    (string)$xml->Recognition
                ]
            )
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Video
     */
    private function parseShortvideoInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Video::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [
                    (string)$xml->MediaId,
                    (string)$xml->ThumbnailId,
                    true,
                ]
            )
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Video
     */
    private function parseVideoInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Video::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [
                    (string)$xml->MediaId,
                    (string)$xml->ThumbnailId,
                    false,
                ]
            )
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Location
     */
    private function parseLocationInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Location::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [
                    [(double)$xml->Location_X, (double)$xml->Location_Y],
                    (double)$xml->Scale,
                    (string)$xml->Label,
                ]
            )
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Input\Link
     */
    private function parseLinkInput (SimpleXMLElement $xml)
    {
        return (new ReflectionClass(Input\Link::class))->newInstanceArgs(
            array_merge(
                $this->getCommonMessageProperties($xml),
                [
                    (string)$xml->Url,
                    (string)$xml->Title,
                    (string)$xml->Description,
                ]
            )
        );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @todo Refactor creation to include different event types.
     * @return Input\Event
     */
    private function parseEventInput (SimpleXMLElement $xml)
    {
        return new Input\Event($xml);
    }
}
