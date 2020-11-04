<?php

namespace Garbetjie\WeChatClient\QR\Code;

use DateTimeImmutable;
use DateTimeInterface;

class Temporary implements CodeInterface
{
    /**
     * @var string
     */
    protected $ticket;

    /**
     * @var DateTimeImmutable
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
     * @param DateTimeInterface $expires
     * @param string   $url
     */
    public function __construct ($ticket, $url, DateTimeInterface $expires)
    {
        $this->ticket = $ticket;
        $this->expires = new DateTimeImmutable('@' . $expires->getTimestamp());
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
     * @return DateTimeImmutable
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
