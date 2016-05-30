<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use DateTime;
use SimpleXMLElement;
use Garbetjie\WeChatClient\Responder\Input;

abstract class AbstractInput implements InputInterface
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
     * @var DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $id;

    /**
     * AbstractInput constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (\SimpleXMLElement $xml)
    {
        $this->sender = (string)$xml->FromUserName;
        $this->recipient = (string)$xml->ToUserName;
        $this->created = DateTime::createFromFormat('U', (int)$xml->CreateTime);
        $this->id = (int)$xml->MsgId;
    }

    /**
     * @return string
     */
    public function getSender ()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function getRecipient ()
    {
        return $this->recipient;
    }

    /**
     * @return DateTime
     */
    public function getCreatedDate ()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getID ()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUniqueHash ()
    {
        return hash('sha1', $this->getID() . $this->getCreatedDate()->format('U'));
    }
}
