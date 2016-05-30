<?php

namespace Garbetjie\WeChatClient\Service\QR\Exception;

use GuzzleHttp\Exception\BadResponseException;

class BadQRCodeResponseFormatException extends BadResponseException implements QRCodeException
{

}
