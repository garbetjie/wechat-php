<?php

namespace Garbetjie\WeChatClient\Service\QR;

use DateTime;
use Garbetjie\WeChatClient\Service\QR\CodeInterface;

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
    public function __construct ( $ticket, $url, DateTime $expires )
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
    public function ticket ()
    {
        return $this->ticket;
    }

    /**
     * @return DateTime
     */
    public function expires ()
    {
        return $this->expires;
    }

    /**
     * Gives the URL at which the QR code can be viewed.
     *
     * @return string
     */
    public function url ()
    {
        return $this->url;
    }
}
