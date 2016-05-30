<?php

namespace Garbetjie\WeChatClient\Media\Exception;

use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Media\Exception\MediaException;

class BadMediaResponseFormatException extends BadResponseFormatException implements MediaException
{

}
