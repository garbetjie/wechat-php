<?php

namespace Garbetjie\WeChatClient\QR;

use DateTime;
use Garbetjie\WeChatClient\QR\CodeInterface;

class TemporaryCode implements CodeInterface
{
    /**
     * @var string
     */
    protected $ticket;

    /**
     * @var DateTime
     */
    protected $expires;

    /**
     * @var string
     */
    protected $url;

    /**
     * QRCode constructor.
     *
     * @param string   $ticket
     * @param DateTime $expires
     * @param string   $url
     */
    public function __construct ($ticket, $url, DateTime $expires)
    {
        $this->ticket = $ticket;
        $this->expires = $expires;
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
     * @return DateTime
     */
    public function getExpiryDate ()
    {
        return $this->expires;
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
