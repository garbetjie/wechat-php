<?php

namespace Garbetjie\WeChatClient\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class ApiErrorException extends \RuntimeException implements WeChatClientException
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * @inheritdoc
     */
    public function __construct ($message, $code, RequestInterface $request, ResponseInterface $response)
    {
        parent::__construct($message, $code);
        
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest ()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse ()
    {
        return $this->response;
    }
}
