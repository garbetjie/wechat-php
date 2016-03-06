<?php

namespace Garbetjie\WeChatClient\Responder;

use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Responder\Input\AbstractInput;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

class Responder
{
    /**
     * @var TypeInterface
     */
    protected $defaultReply;

    /**
     * @var Dispatcher
     */
    public $on;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string|null
     */
    protected $input;

    /**
     * Responder constructor.
     *
     * @param array  $params An array of query parameters (defaults to $_GET if not provided).
     * @param string $input  The body of the request. Defaults to the contents of `php://input` if not supplied.
     */
    public function __construct (array $params = [], $input = null)
    {
        $this->on = new Dispatcher();
        $this->params = (func_num_args() <= 0 && isset($_GET)) ? $_GET : $params;
        $this->input = $input;
    }

    /**
     * Run the responder, and respond to any incoming requests.
     *
     * If $forResponse is provided, the return value will be the modified version of this response. Otherwise, NULL is
     * returned.
     * 
     * @param ResponseInterface $forResponse
     *
     * @return ResponseInterface|null
     */
    public function run (ResponseInterface $forResponse = null)
    {
        // todo: verify signatures

        // Handle debugging.
        if (isset($this->params['echostr'])) {
            if ($forResponse !== null) {
                return $forResponse
                    ->withHeader('Content-Type', 'text/plain')
                    ->withBody(\GuzzleHttp\Psr7\stream_for($this->params['echostr']));
            } else {
                header('Content-Type: text/plain');
                echo $this->params['echostr'];

                return null;
            }
        }

        // Extract input, and create XML object from it.
        try {
            $input = $this->input === null ? file_get_contents('php://input') : $this->input;
            $xml = new SimpleXMLElement($input);
        } catch (\Exception $e) {
            throw new Exception("Unable to parse input as XML.", null, $e);
        }

        $input = AbstractInput::create($xml);
        $reply = $this->on->handle($input);
        $formatter = new ReplyFormatter();
        
        // Send a reply.
        if ($reply instanceof TypeInterface) {
            return $this->sendReply($formatter->format($input, $reply), $forResponse);
        }

        // Send default response, only if a response is allowed.
        if ($reply !== false && $this->defaultReply instanceof TypeInterface) {
            return $this->sendReply($formatter->format($input, $this->defaultReply), $forResponse);
        }

        // Return PSR-7 response if initially supplied.
        return $forResponse ?: null;
    }

    /**
     * Sends the supplied reply.
     * 
     * If `$forResponse` is supplied, it must be a PSR-7 response that will have the reply written into. The modified
     * response will be returned.
     * 
     * If no `$forResponse` is supplied, NULL will be returned, and the reply will be written directly to the output.
     * 
     * @param string                 $reply The reply to send.
     * @param ResponseInterface|null $forResponse If given, the PSR-7 response to write the reply into.
     *
     * @return null|\Psr\Http\Message\MessageInterface
     */
    private function sendReply ($reply, ResponseInterface $forResponse = null)
    {
        $headers = [
            'Content-Type' => 'text/xml',
            'Connection' => 'close',
            'Content-Length' => strlen($reply),
        ];
        
        // Sending response for PSR-7 response.
        if ($forResponse !== null) {
            foreach ($headers as $name => $value) {
                $forResponse = $forResponse->withHeader($name, $value);
            }
            
            return $forResponse->withBody(\GuzzleHttp\Psr7\stream_for($reply));
        }
        
        // Sending directly.
        
        // Disable GZIP compression.
        ini_set('zlib.output_compression', false);
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', 1);
        }

        // Output the response.
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        echo $reply;

        // Ensure it is sent.
        ob_flush();
        flush();
        
        return null;
    }

    /**
     * Set a default response to use if nothing was matched for the input.
     *
     * @param TypeInterface $message
     */
    public function defaultReply (TypeInterface $message)
    {
        $this->defaultReply = $message;
    }
}
