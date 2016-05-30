<?php

namespace Garbetjie\WeChatClient\QR\Exception;

use Garbetjie\WeChatClient\QR\Exception\QRCodeException;
use GuzzleHttp\Exception\BadResponseException;

class BadQRCodeResponseFormatException extends BadResponseException implements QRCodeException
{

}
