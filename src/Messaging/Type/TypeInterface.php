<?php

namespace Garbetjie\WeChat\Messaging\Type;

interface TypeInterface
{
    /**
     * Returns the message type, as used in the WeChat API.
     *
     * @return string
     */
    public function getType ();
}
