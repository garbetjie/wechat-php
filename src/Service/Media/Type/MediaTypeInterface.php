<?php

namespace Garbetjie\WeChatClient\Service\Media\Type;

interface MediaTypeInterface
{
    /**
     * Returns the type of this media item for use in the WeChat API.
     *
     * @return string
     */
    public function type ();
}
