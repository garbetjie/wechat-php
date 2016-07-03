<?php

namespace Garbetjie\WeChatClient\Responder\Exception;

class AlreadySentException extends \Exception implements Exception
{
    public function __construct ()
    {
        parent::__construct('reply message has already been sent');
    }
}
