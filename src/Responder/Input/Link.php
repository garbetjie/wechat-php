<?php

namespace Garbetjie\WeChatClient\Responder\Input;

class Link extends Input
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $url;

    public function __construct ($sender, $recipient, $messageID, $createdDate, $url, $title, $description)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Title given to the link.
     *
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * Custom description given to the link.
     *
     * @return string
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * The URL of the link.
     *
     * @return string
     */
    public function getURL ()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getEmittedType ()
    {
        return Type::LINK;
    }
}
