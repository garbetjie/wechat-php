<?php

namespace Garbetjie\WeChatClient\Responder;

use Garbetjie\WeChatClient\Responder\Exception;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use League\Event\Emitter;
use League\Event\EmitterInterface;
use League\Event\EventInterface;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

class Responder
{
    /**
     * @var TypeInterface
     */
    private $defaultReply;

    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @var string|null
     */
    private $encodingKey;
    
    public function __construct ($encodingKey = null)
    {
        $this->encodingKey = $encodingKey;
        $this->emitter = new Emitter();
    }

    /**
     * Adds an event listener for the given input type.
     * 
     * @param          $type
     * @param callable $listener
     * @param array    ...$args
     */
    public function on ($type, callable $listener, ...$args)
    {
        $this->emitter->addListener(
            $type,
            function (EventInterface $event, Input\Input $input, Reply\HandlerInterface $replyHandler) use ($args, $listener) {
                call_user_func_array($listener, array_merge([$input, $replyHandler], $args));
            }
        );
    }
    
    public function run ($params, $input, Reply\HandlerInterface $replyHandler = null)
    {
        if (!$replyHandler) {
            $replyHandler = new Reply\DirectHandler();
        }
        
        // Handle the debug parameter.
        if (isset($params['echostr'])) {
            $replyHandler->sendEchoString($params['echostr']);
            return;
        }
        
        // @todo: implement request signing.
        
        // Extract input.
        try {
            $xml = new SimpleXMLElement($input);
        } catch (\Exception $e) {
            throw new Exception("bad xml input: {$e->getMessage()}");
        }
        
        // Build the input object.
        $input = (new InputBuilder())->build($xml);
        
        // Set the recipient and sender.
        $replyHandler = $replyHandler
            ->withSender($input->getRecipient())
            ->withRecipient($input->getSender());
        
        // Emit the event.
        $this->emitter->emit($input->getEmittedType(), $input, $replyHandler);
    }
}
