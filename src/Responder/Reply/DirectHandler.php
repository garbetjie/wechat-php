<?php

namespace Garbetjie\WeChatClient\Responder\Reply;

use Garbetjie\WeChatClient\Messaging\Type;
use Garbetjie\WeChatClient\Responder\Exception;

class DirectHandler extends Handler
{
    /**
     * @inheritdoc
     */
    protected function printReply ($reply, array $headers = [])
    {
        foreach ($headers as $headerLine) {
            header($headerLine);
        }

        // Disable output compression, so that the response can be sent immediately.
        ini_set('zlib.output_compression', false);
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', 1);
        }

        echo $reply;
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
}
