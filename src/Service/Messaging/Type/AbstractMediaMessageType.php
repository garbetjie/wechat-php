<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

abstract class AbstractMediaMessageType extends AbstractMessageType
{
    /**
     * The ID of the media item to be sent (as given when uploading to WeChat).
     *
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct ($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getID ()
    {
        return $this->id;
    }
}
