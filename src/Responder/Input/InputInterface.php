<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use DateTime;

interface InputInterface
{

    /**
     * @return string
     */
    public function getSender ();

    /**
     * @return string
     */
    public function getRecipient ();

    /**
     * @return DateTime
     */
    public function getCreatedDate ();

    /**
     * @return string
     */
    public function getID ();

    /**
     * @return string
     */
    public function getUniqueHash ();

    /**
     * @return string
     */
    public function getEmittedEvent ();
}
