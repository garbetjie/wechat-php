<?php

namespace Garbetjie\WeChatClient\Responder;

use Garbetjie\WeChatClient\Responder\Exception\BadInputTypeException;
use Garbetjie\WeChatClient\Responder\Exception\ResponderException;
use Garbetjie\WeChatClient\Responder\Input\AudioInput;
use Garbetjie\WeChatClient\Responder\Input\EventInput;
use Garbetjie\WeChatClient\Responder\Input\ImageInput;
use Garbetjie\WeChatClient\Responder\Input\LinkInput;
use Garbetjie\WeChatClient\Responder\Input\LocationInput;
use Garbetjie\WeChatClient\Responder\Input\TextInput;
use Garbetjie\WeChatClient\Responder\Input\VideoInput;
use SimpleXMLElement;

class InputBuilder
{
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
            throw new BadInputTypeException("unexpected input type `{$xml->MsgType}`");
        }
        
        return $input;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return TextInput
     */
    private function parseTextInput (SimpleXMLElement $xml)
    {
        return new TextInput($xml);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return ImageInput
     */
    private function parseImageInput (SimpleXMLElement $xml)
    {
        return new ImageInput($xml);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return AudioInput
     */
    private function parseVoiceInput (SimpleXMLElement $xml)
    {
        return new AudioInput($xml);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return VideoInput
     */
    private function parseShortvideoInput (SimpleXMLElement $xml)
    {
        return new VideoInput($xml, true);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return VideoInput
     */
    private function parseVideoInput (SimpleXMLElement $xml)
    {
        return new VideoInput($xml, false);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return LocationInput
     */
    private function parseLocationInput (SimpleXMLElement $xml)
    {
        return new LocationInput($xml);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return LinkInput
     */
    private function parseLinkInput (SimpleXMLElement $xml)
    {
        return new LinkInput($xml);
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return EventInput
     */
    private function parseEventInput (SimpleXMLElement $xml)
    {
        return new EventInput($xml);
    }
}
