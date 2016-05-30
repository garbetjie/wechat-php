<?php

namespace Garbetjie\WeChatClient\Messaging\Exception;

use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Messaging\Exception\MessagingException;

class BadMessagingResponseFormatException extends BadResponseFormatException implements MessagingException
{

}
