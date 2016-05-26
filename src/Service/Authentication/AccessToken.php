<?php

namespace Garbetjie\WeChatClient\Service\Authentication;

use DateTime;
use JsonSerializable;

class AccessToken implements JsonSerializable
{
    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var DateTime
     */
    protected $expires;

    /**
     * AccessToken constructor.
     *
     * @param string   $token
     * @param DateTime $expires
     */
    public function __construct ( $token, DateTime $expires )
    {
        $this->token = (string) $token;
        $this->expires = $expires;
    }

    /**
     * @return bool
     */
    public function valid ()
    {
        return $this->token && $this->expires->getTimestamp() > time();
    }

    /**
     * Returns the value of the access token.
     * 
     * @return string
     */
    public function value ()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function __toString ()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    public function jsonSerialize ()
    {
        return [ 'token' => $this->token, 'expires' => $this->expires->getTimestamp() ];
    }

    /**
     * @return DateTime
     */
    public function expires ()
    {
        return clone $this->expires;
    }
}
