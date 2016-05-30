<?php

namespace Garbetjie\WeChatClient\Exception;

use Psr\Http\Message\ResponseInterface;

class BadResponseFormatException extends \RuntimeException implements WeChatClientException
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * BadResponseFormatException constructor.
     *
     * @param string            $message
     * @param ResponseInterface $response
     */
    public function __construct ($message, ResponseInterface $response)
    {
        parent::__construct($message);
        
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse ()
    {
        return $this->response;
    }
}
