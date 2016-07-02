<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Event extends Input
{
    /**
     * @var string
     */
    private $event;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Event constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        parent::__construct(
            (string)$xml->FromUserName,
            (string)$xml->ToUserName,
            (string)$xml->MsgId,
            (int)$xml->CreateTime
        );
        
        $this->event = strtolower($xml->Event);
        $method = sprintf('parseEvent_%s', ucfirst($this->event));

        if (method_exists($this, $method) && is_callable([$this, $method])) {
            call_user_func([$this, $method], $xml);
        }
    }

    /**
     * @return string
     */
    public function getEventName ()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getEventAttributes ()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getEmittedType ()
    {
        return Type::EVENT;
    }

    /**
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getEventAttribute ($param, $default = null)
    {
        return array_key_exists($param, $this->params) ? $this->params[$param] : $default;
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function parseEvent_Location (SimpleXMLElement $xml)
    {
        $this->params['latitude'] = (float)$xml->Latitide;
        $this->params['longitude'] = (float)$xml->Longitude;
        $this->params['precision'] = (float)$xml->Precision;
        $this->params['coordinates'] = [$this->params['latitude'], $this->params['longitude']];
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function parseEvent_Subscribe (SimpleXMLElement $xml)
    {
        $this->params['scanned'] = false;

        if (isset($xml->EventKey, $xml->Ticket)) {
            $this->params['scanned'] = true;
            $this->params['ticket'] = (string)$xml->Ticket;
            $this->params['code'] = (string)$xml->EventKey;
        }
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function parseEvent_Url (SimpleXMLElement $xml)
    {
        $this->params['url'] = (string)$xml->EventKey;
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function parseEvent_Scancode_push (SimpleXMLElement $xml)
    {
        $this->params['type'] = (string)$xml->ScanCodeInfo->ScanType;
        $this->params['value'] = (string)$xml->ScanCodeInfo->ScanResult;
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function parseEvent_Click (SimpleXMLElement $xml)
    {
        $this->params['text'] = (string)$xml->EventKey;
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function parseEvent_Scan (SimpleXMLElement $xml)
    {
        $this->params['ticket'] = (string)$xml->Ticket;
        $this->params['code'] = (string)$xml->EventKey;
    }
}
