<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

use Garbetjie\WeChatClient\Service\Messaging\Type\AbstractMessageType;

abstract class AbstractMediaMessageType extends AbstractMessageType
{
    /**
     * The ID of the media item to be sent (as given when uploading to WeChat).
     * 
     * @var string
     */
    public $id;

    /**
     * @param string $id
     */
    public function __construct ( $id )
    {
        $this->id = $id;
    }
}
