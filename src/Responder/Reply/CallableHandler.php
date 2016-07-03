<?php

namespace Garbetjie\WeChatClient\Responder\Reply;

class CallableHandler extends Handler
{
    /**
     * @var callable
     */
    private $handler;
    
    /**
     * CallableHandler constructor.
     *
     * @param callable $handler
     */
    public function __construct (callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @inheritdoc
     */
    protected function printReply ($reply, array $headers = [])
    {
        call_user_func($this->handler, $reply, $headers);
    }

}
