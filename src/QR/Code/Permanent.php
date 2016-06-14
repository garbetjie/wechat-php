<?php

namespace Garbetjie\WeChatClient\QR\Code;

class Permanent implements CodeInterface
{
    /**
     * @var string
     */
    protected $ticket;

    /**
     * @var string
     */
    protected $url;

    /**
     * QRCode constructor.
     *
     * @param string $ticket
     * @param string $url
     */
    public function __construct ($ticket, $url)
    {
        $this->ticket = $ticket;
        $this->url = $url;
    }

    /**
     * Returns the ticket value for the QR code.
     *
     * @return string
     */
    public function getTicket ()
    {
        return $this->ticket;
    }

    /**
     * Gives the URL at which the QR code can be viewed.
     *
     * @return string
     */
    public function getURL ()
    {
        return $this->url;
    }
}
