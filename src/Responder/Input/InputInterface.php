<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use DateTime;

interface InputInterface
{

    /**
     * @return string
     */
    public function sender ();

    /**
     * @return string
     */
    public function recipient ();

    /**
     * @return DateTime
     */
    public function created ();

    /**
     * @return string
     */
    public function id ();

    /**
     * @return string
     */
    public function unique ();

    /**
     * @return string
     */
    public function emits ();
}
