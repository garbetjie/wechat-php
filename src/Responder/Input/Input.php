<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use DateTimeImmutable;
use DateTimeInterface;

abstract class Input
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
     * @var DateTimeImmutable
     */
    private $createdDate;

    /**
     * @var string
     */
    private $messageID;
    
    public function __construct ($sender, $recipient, $messageID, $createdDate)
    {
        $this->sender = (string)$sender;
        $this->recipient = (string)$recipient;
        $this->messageID = (string)$messageID;
        
        if ($createdDate instanceof DateTimeInterface) {
            $this->createdDate = new DateTimeImmutable("{$createdDate->getTimestamp()}");
        } else {
            $this->createdDate = new DateTimeImmutable("@{$createdDate}");
        }
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
     * @return DateTimeImmutable
     */
    public function getCreatedDate ()
    {
        return $this->createdDate;
    }

    /**
     * @return string
     */
    public function getMessageID ()
    {
        return $this->messageID;
    }

    /**
     * @return string
     */
    abstract public function getEmittedType ();
}
