<?php

namespace Garbetjie\WeChatClient\Responder;

use InvalidArgumentException;
use League\Event\Emitter;
use League\Event\EventInterface;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Responder\Input\Audio;
use Garbetjie\WeChatClient\Responder\Input\Event;
use Garbetjie\WeChatClient\Responder\Input\InputInterface;

class Handler
{
    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var TypeInterface
     */
    protected $response;

    /**
     * @var array
     */
    private $eventEmitterMapping = [
        'subscribe'   => 'subscribe',
        'unsubscribe' => 'unsubscribe',
    ];

    /**
     * Handler constructor.
     */
    public function __construct ()
    {
        $this->emitter = new Emitter();
    }

    /**
     * Emit the specified event type.
     *
     * @param InputInterface $input
     * @param Response       $response
     */
    public function handle ( InputInterface $input, Response $response )
    {
        $emits = $input->emits();

        // Check if the event is a mapped event.
        if ( $input->emits() === 'event' && $input instanceof Event ) {
            $event = $input->event();
            if ( isset( $this->eventEmitterMapping[ $event ] ) ) {
                $emits = $this->eventEmitterMapping[ $event ];
            }
        }

        // Execute the handler.
        $this->emitter->emit( $emits, $input, $response );
    }

    /**
     * Returns a closure that wraps the given handler. This closure is what is executed when all the events
     * are emitted.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    protected function wrap ( callable $handler )
    {
        return function ( EventInterface $event, InputInterface $input, Response $response ) use ( $handler ) {
            call_user_func( $handler, $input, $response );

            if ( $response->stopped() ) {
                $event->stopPropagation();
            }
        };
    }

    /**
     * Handling incoming text messages.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function text ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle incoming images.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function image ( callable $handler, $priority = Emitter::P_NORMAL )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ), $priority );
    }

    /**
     * Handle incoming videos.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function video ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle incoming audio.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function audio ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle incoming events.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function event ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle user subscriptions to the OA.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function subscribe ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle users unsubscribing from the OA.
     *
     * @param callable $handler The handler that will handle the incoming request.
     */
    public function unsubscribe ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle incoming QR code scan events.
     *
     * @param callable $handler
     */
    public function scan ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }

    /**
     * Handle incoming location event.
     *
     * @param callable $handler
     */
    public function location ( callable $handler )
    {
        $this->emitter->addListener( __FUNCTION__, $this->wrap( $handler ) );
    }
}
