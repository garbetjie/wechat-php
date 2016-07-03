<?php

namespace Garbetjie\WeChatClient\Responder\Exception;

class UnknownInputTypeException extends \Exception implements Exception
{
    /**
     * @param string $type
     * 
     */
    public function __construct ($type)
    {
        parent::__construct("unknown input type `{$type}`");
    }
}
