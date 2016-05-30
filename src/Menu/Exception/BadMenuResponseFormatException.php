<?php

namespace Garbetjie\WeChatClient\Menu\Exception;

use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Menu\Exception\MenuException;

class BadMenuResponseFormatException extends BadResponseFormatException implements MenuException
{

}
