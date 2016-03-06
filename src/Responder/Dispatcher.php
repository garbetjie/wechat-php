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
     * @param array $args Additional arguments to pass to the handler.
     *
     * @return \Closure
     */
    protected function wrap (callable $handler, array $args = [])
    {
        $reply = &$this->reply;

        return function (EventInterface $event, InputInterface $input) use ($handler, &$reply, $args) {
            $reply = call_user_func_array($handler, array_merge([$input], $args));
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
     * @param array $args Additional arguments to be passed to the handler.                         
     */
    public function text (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle incoming images.
     *
     * @param callable $handler The handler that will handle the incoming request.
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function image (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle incoming videos.
     *
     * @param callable $handler The handler that will handle the incoming request.
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function video (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle incoming audio.
     *
     * @param callable $handler The handler that will handle the incoming request.
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function audio (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle incoming events.
     *
     * @param callable $handler The handler that will handle the incoming request.
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function event (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle user subscriptions to the OA.
     *
     * @param callable $handler The handler that will handle the incoming request.
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function subscribe (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle users unsubscribing from the OA.
     *
     * @param callable $handler The handler that will handle the incoming request.
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function unsubscribe (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle incoming QR code scan events.
     *
     * @param callable $handler
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function scan (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle incoming location event.
     *
     * @param callable $handler
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function location (callable $handler, array $args = [])
    {
        $this->emitter->addListener(__FUNCTION__, $this->wrap($handler, $args));
    }

    /**
     * Handle ANY incoming event.
     *
     * @param callable $handler
     * @param array $args Additional arguments to be passed to the handler.
     */
    public function any (callable $handler, array $args = [])
    {
        $this->emitter->addListener('*', $this->wrap($handler, $args));
    }
}
