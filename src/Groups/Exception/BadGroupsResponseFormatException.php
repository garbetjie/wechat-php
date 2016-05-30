<?php

namespace Garbetjie\WeChatClient\Groups\Exception;

use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Groups\Exception\GroupsException;

class BadGroupsResponseFormatException extends BadResponseFormatException implements GroupsException
{
    
}
