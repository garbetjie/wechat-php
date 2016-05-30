<?php

namespace Garbetjie\WeChatClient\Users\Exception;

use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Users\Exception\UserException;

class BadUserResponseFormatException extends BadResponseFormatException implements UserException
{

}
