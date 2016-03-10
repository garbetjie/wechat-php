<?php

namespace Garbetjie\WeChatClient\Media\Type;

interface TypeInterface
{
    /**
     * Returns the type of this media item for use in the WeChat API.
     *
     * @return string
     */
    public function type ();
}
