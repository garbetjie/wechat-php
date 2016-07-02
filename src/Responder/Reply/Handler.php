<?php

namespace Garbetjie\WeChatClient\Responder\Reply;

use DOMCdataSection;
use DOMDocument;
use DOMElement;

abstract class Handler implements HandlerInterface
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
     * Performs the actual sending of the message. Receives the message that needs to be built.
     * 
     * @param array $message
     *
     * @return void
     */
    abstract protected function sendMessage (array $message);

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
        
        $message = array_merge(
            $message,
            ['FromUserName' => $this->recipient, 'ToUserName' => $this->sender, 'CreateTime' => time()]
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
}
