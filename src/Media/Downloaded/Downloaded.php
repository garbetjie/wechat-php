<?php

namespace Garbetjie\WeChatClient\Media\Downloaded;

use Garbetjie\WeChatClient\Media\Paginated\Paginated;

abstract class Downloaded extends Paginated
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * Downloaded constructor.
     *
     * @param string $mediaID
     * @param resource $stream
     */
    public function __construct ($mediaID, $stream)
    {
        parent::__construct($mediaID);
        
        $this->stream = $stream;
    }

    /**
     * @return resource
     */
    public function getStream ()
    {
        return $this->stream;
    }
}
