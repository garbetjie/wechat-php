<?php

namespace Garbetjie\WeChatClient\Responder;

use InvalidArgumentException;
use League\Event\Emitter;
use League\Event\EventInterface;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Responder\Input\Audio;
use Garbetjie\WeChatClient\Responder\Input\Event;
use Garbetjie\WeChatClient\Responder\Input\InputInterface;

class Dispatcher
{
    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var TypeInterface
     */
    protected $reply;

    /**
     * A mapping of events => emitters.
     *
     * @var array
     */
    private $eventEmitterMapping = [
        'subscribe'   => 'subscribe',
        'unsubscribe' => 'unsubscribe',
    ];

    /**
     * Dispatcher constructor.
     */
    public function __construct ()
    {
        $this->emitter = new Emitter();
    }

    /**
     * Handle the given input, and dispatch required event listeners.
     * 
     * If input type is an event, and the event is contained in `$this->eventEmitterMapping`, the event will be mapped to
     * its own event listener.
     * 
     * Returns the reply to be sent.
     *
     * @param InputInterface $input
     * 
     * @return TypeInterface
     */
    public function handle (InputInterface $input)
    {
        $emits = $input->emits();

        // Check if the event is a mapped event.
        if ($input->emits() === 'event' && $input instanceof Event) {
            $event = $input->event();
            if (isset($this->eventEmitterMapping[$event])) {
                $emits = $this->eventEmitterMapping[$event];
            }
        }

        // Execute the handler.
        $this->emitter->emit($emits, $input);

        return $this->reply;
    }

    /**
     * Returns a closure that wraps the given handler. This closure handles the stopping of the event, if required.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    protected function wrap (callable $handler)
    {
        $reply = &$this->reply;

        return function (EventInterface $event, InputInterface $input) use ($handler, &$reply) {
            $reply = call_user_func($handler, $input);
            $stop = false;

            if ($reply !== null) {
                if ($reply === false || $reply instanceof TypeInterface) {
                    $stop = true;
                } else {
                    throw new \RuntimeException('$reply must be an instance of ' . TypeInterface::class);
                }
            }

            if ($stop) {
                $event->stopPropagation();
            }
        };
    }

    /**
     * Handling incoming text messages.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function text (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle incoming images.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function image (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle incoming videos.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function video (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle incoming audio.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function audio (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle incoming events.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function event (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle user subscriptions to the OA.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function subscribe (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle users unsubscribing from the OA.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function unsubscribe (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle incoming QR code scan events.
     *
     * @param callable $handler
     */
    public function scan (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle incoming location event.
     *
     * @param callable $handler
     */
    public function location (callable $handler)
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler));
    }

    /**
     * Handle ANY incoming event.
     *
     * @param callable $handler
     */
    public function any (callable $handler)
    {
        $this->emitter->addListener('*', $this->wrap($handler));
    }
}
