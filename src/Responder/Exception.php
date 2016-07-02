<?php

namespace Garbetjie\WeChatClient\Responder;

use Garbetjie\WeChatClient\Exception\WeChatClientException;

class Exception extends \Exception implements WeChatClientException
{
    const BAD_XML = 0;
}
