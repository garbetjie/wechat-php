<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

abstract class AbstractMediaType extends AbstractType
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
